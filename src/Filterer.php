<?php

namespace TraderInteractive;

use Exception;
use InvalidArgumentException;
use Throwable;
use TraderInteractive\Exceptions\FilterException;
use TraderInteractive\Filter\Arrays;
use TraderInteractive\Filter\Json;
use TraderInteractive\Filter\PhoneFilter;
use TraderInteractive\Filter\TimeOfDayFilter;
use TraderInteractive\Filter\UuidFilter;
use TraderInteractive\Filter\XmlFilter;

/**
 * Class to filter an array of input.
 */
final class Filterer implements FiltererInterface
{
    /**
     * @var array
     */
    const DEFAULT_FILTER_ALIASES = [
        'array' => '\\TraderInteractive\\Filter\\Arrays::filter',
        'array-copy' => Arrays::class . '::copy',
        'array-copy-each' => Arrays::class . '::copyEach',
        'array-pad' => Arrays::class . '::pad',
        'arrayize' => '\\TraderInteractive\\Filter\\Arrays::arrayize',
        'bool' => '\\TraderInteractive\\Filter\\Booleans::filter',
        'bool-convert' => '\\TraderInteractive\\Filter\\Booleans::convert',
        'closure' => Filter\Closures::class . '::filter',
        'compress-string' => '\\TraderInteractive\\Filter\\Strings::compress',
        'concat' => '\\TraderInteractive\\Filter\\Strings::concat',
        'date' => '\\TraderInteractive\\Filter\\DateTime::filter',
        'date-format' => '\\TraderInteractive\\Filter\\DateTime::format',
        'email' => '\\TraderInteractive\\Filter\\Email::filter',
        'explode' => '\\TraderInteractive\\Filter\\Strings::explode',
        'flatten' => '\\TraderInteractive\\Filter\\Arrays::flatten',
        'float' => '\\TraderInteractive\\Filter\\Floats::filter',
        'implode' => Arrays::class . '::implode',
        'in' => '\\TraderInteractive\\Filter\\Arrays::in',
        'int' => '\\TraderInteractive\\Filter\\Ints::filter',
        'json' => Json::class . '::validate',
        'json-decode' => Json::class . '::parse',
        'ofArray' => '\\TraderInteractive\\Filterer::ofArray',
        'ofArrays' => '\\TraderInteractive\\Filterer::ofArrays',
        'ofScalars' => '\\TraderInteractive\\Filterer::ofScalars',
        'phone' => PhoneFilter::class . '::filter',
        'redact' => '\\TraderInteractive\\Filter\\Strings::redact',
        'string' => '\\TraderInteractive\\Filter\\Strings::filter',
        'strip-tags' => '\\TraderInteractive\\Filter\\Strings::stripTags',
        'time-of-day' => TimeOfDayFilter::class . '::filter',
        'timezone' => '\\TraderInteractive\\Filter\\DateTimeZone::filter',
        'translate' => '\\TraderInteractive\\Filter\\Strings::translate',
        'uint' => '\\TraderInteractive\\Filter\\UnsignedInt::filter',
        'url' => '\\TraderInteractive\\Filter\\Url::filter',
        'uuid' => UuidFilter::class . '::filter',
        'xml' => XmlFilter::class . '::filter',
        'xml-extract' => XmlFilter::class . '::extract',
        'xml-validate' => XmlFilter::class . '::validate',
    ];

    /**
     * @var array
     */
    const DEFAULT_OPTIONS = [
        FiltererOptions::ALLOW_UNKNOWNS => false,
        FiltererOptions::DEFAULT_REQUIRED => false,
        FiltererOptions::RESPONSE_TYPE => self::RESPONSE_TYPE_ARRAY,
    ];

    /**
     * @var string
     */
    const RESPONSE_TYPE_ARRAY = 'array';

    /**
     * @var string
     */
    const RESPONSE_TYPE_FILTER = FilterResponse::class;

    /**
     * @var string
     */
    const INVALID_BOOLEAN_FILTER_OPTION = "%s for field '%s' was not a boolean value";

    /**
     * @var array
     */
    private static $registeredFilterAliases = self::DEFAULT_FILTER_ALIASES;

    /**
     * @var array|null
     */
    private $filterAliases;

    /**
     * @var array
     */
    private $specification;

    /**
     * @var bool
     */
    private $allowUnknowns;

    /**
     * @var bool
     */
    private $defaultRequired;

    /**
     * @param array      $specification The specification to apply to the value.
     * @param array      $options       The options apply during filtering.
     *                                  'allowUnknowns' (default false) true to allow or false to treat as error.
     *                                  'defaultRequired' (default false) true to make fields required by default.
     * @param array|null $filterAliases The filter aliases to accept.
     *
     * @throws InvalidArgumentException if 'allowUnknowns' option was not a bool
     * @throws InvalidArgumentException if 'defaultRequired' option was not a bool
     */
    public function __construct(array $specification, array $options = [], array $filterAliases = null)
    {
        $options += self::DEFAULT_OPTIONS;

        $this->specification = $specification;
        $this->filterAliases = $filterAliases;
        $this->allowUnknowns = self::getAllowUnknowns($options);
        $this->defaultRequired = self::getDefaultRequired($options);
    }

    /**
     * @param mixed $input The input to filter.
     *
     * @return FilterResponse
     *
     * @throws InvalidArgumentException Thrown if the filters for a field were not an array.
     * @throws InvalidArgumentException Thrown if any one filter for a field was not an array.
     * @throws InvalidArgumentException Thrown if the 'required' value for a field was not a bool.
     */
    public function execute(array $input) : FilterResponse
    {
        $filterAliases = $this->getAliases();
        $inputToFilter = [];
        $leftOverSpec = [];

        foreach ($this->specification as $field => $specification) {
            if (array_key_exists($field, $input)) {
                $inputToFilter[$field] = $input[$field];
                continue;
            }

            $leftOverSpec[$field] = $specification;
        }

        $leftOverInput = array_diff_key($input, $inputToFilter);

        $filteredInput = [];
        $errors = [];
        $conflicts = [];
        foreach ($inputToFilter as $field => $input) {
            $filters = $this->specification[$field];
            self::assertFiltersIsAnArray($filters, $field);
            $customError = self::validateCustomError($filters, $field);
            $throwOnError = self::validateThrowOnError($filters, $field);
            $returnOnNull = self::validateReturnOnNull($filters, $field);
            unset($filters[FilterOptions::IS_REQUIRED]);//doesn't matter if required since we have this one
            unset($filters[FilterOptions::DEFAULT_VALUE]);//doesn't matter if there is a default since we have a value
            $conflicts = self::extractConflicts($filters, $field, $conflicts);

            foreach ($filters as $filter) {
                self::assertFilterIsArray($filter, $field);

                if (empty($filter)) {
                    continue;
                }

                $uses = self::extractUses($filter);

                $function = array_shift($filter);
                $function = self::handleFilterAliases($function, $filterAliases);

                self::assertFunctionIsCallable($function, $field);

                array_unshift($filter, $input);
                try {
                    $this->addUsedInputToFilter($uses, $filteredInput, $field, $filter);
                    $input = call_user_func_array($function, $filter);
                    if ($input === null && $returnOnNull) {
                        break;
                    }
                } catch (Exception $exception) {
                    if ($throwOnError) {
                        throw $exception;
                    }

                    $errors = self::handleCustomError($field, $input, $exception, $errors, $customError);
                    continue 2;//next field
                }
            }

            $filteredInput[$field] = $input;
        }

        foreach ($leftOverSpec as $field => $filters) {
            self::assertFiltersIsAnArray($filters, $field);
            $required = self::getRequired($filters, $this->defaultRequired, $field);
            if (array_key_exists(FilterOptions::DEFAULT_VALUE, $filters)) {
                $filteredInput[$field] = $filters[FilterOptions::DEFAULT_VALUE];
                continue;
            }

            $errors = self::handleRequiredFields($required, $field, $errors);
        }

        $errors = self::handleAllowUnknowns($this->allowUnknowns, $leftOverInput, $errors);
        $errors = self::handleConflicts($filteredInput, $conflicts, $errors);

        return new FilterResponse($filteredInput, $errors, $leftOverInput);
    }

    /**
     * @return array
     *
     * @see FiltererInterface::getAliases
     */
    public function getAliases() : array
    {
        return $this->filterAliases ?? self::$registeredFilterAliases;
    }

    private static function extractConflicts(array &$filters, string $field, array $conflicts) : array
    {
        $conflictsWith = $filters[FilterOptions::CONFLICTS_WITH] ?? null;
        unset($filters[FilterOptions::CONFLICTS_WITH]);
        if ($conflictsWith === null) {
            return $conflicts;
        }

        if (!is_array($conflictsWith)) {
            $conflictsWith = [$conflictsWith];
        }

        $conflicts[$field] = $conflictsWith;

        return $conflicts;
    }

    private static function handleConflicts(array $inputToFilter, array $conflicts, array $errors)
    {
        foreach (array_keys($inputToFilter) as $field) {
            if (!array_key_exists($field, $conflicts)) {
                continue;
            }

            foreach ($conflicts[$field] as $conflictsWith) {
                if (array_key_exists($conflictsWith, $inputToFilter)) {
                    $errors[] = "Field '{$field}' cannot be given if field '{$conflictsWith}' is present.";
                }
            }
        }

        return $errors;
    }

    private static function extractUses(&$filters)
    {
        $uses = $filters[FilterOptions::USES] ?? [];
        unset($filters[FilterOptions::USES]);
        return is_array($uses) ? $uses : [$uses];
    }

    /**
     * @return array
     *
     * @see FiltererInterface::getSpecification
     */
    public function getSpecification() : array
    {
        return $this->specification;
    }

    /**
     * @param array $filterAliases
     *
     * @return FiltererInterface
     *
     * @see FiltererInterface::withAliases
     */
    public function withAliases(array $filterAliases) : FiltererInterface
    {
        return new Filterer($this->specification, $this->getOptions(), $filterAliases);
    }

    /**
     * @param array $specification
     *
     * @return FiltererInterface
     *
     * @see FiltererInterface::withSpecification
     */
    public function withSpecification(array $specification) : FiltererInterface
    {
        return new Filterer($specification, $this->getOptions(), $this->filterAliases);
    }

    /**
     * @return array
     */
    private function getOptions() : array
    {
        return [
            FiltererOptions::DEFAULT_REQUIRED => $this->defaultRequired,
            FiltererOptions::ALLOW_UNKNOWNS => $this->allowUnknowns,
        ];
    }

    /**
     * Example:
     * <pre>
     * <?php
     * class AppendFilter
     * {
     *     public function filter($value, $extraArg)
     *     {
     *         return $value . $extraArg;
     *     }
     * }
     * $appendFilter = new AppendFilter();
     *
     * $trimFunc = function($val) { return trim($val); };
     *
     * list($status, $result, $error, $unknowns) = TraderInteractive\Filterer::filter(
     *     [
     *         'field one' => [[$trimFunc], ['substr', 0, 3], [[$appendFilter, 'filter'], 'boo']],
     *         'field two' => ['required' => true, ['floatval']],
     *         'field three' => ['required' => false, ['float']],
     *         'field four' => ['required' => true, 'default' => 1, ['uint']],
     *     ],
     *     ['field one' => ' abcd', 'field two' => '3.14']
     * );
     *
     * var_dump($status);
     * var_dump($result);
     * var_dump($error);
     * var_dump($unknowns);
     * </pre>
     * prints:
     * <pre>
     * bool(true)
     * array(3) {
     *   'field one' =>
     *   string(6) "abcboo"
     *   'field two' =>
     *   double(3.14)
     *   'field four' =>
     *   int(1)
     * }
     * NULL
     * array(0) {
     * }
     * </pre>
     *
     * @param array $specification The specification to apply to the input.
     * @param array $input          The input the apply the specification to.
     * @param array $options        The options apply during filtering.
     *                              'allowUnknowns' (default false) true to allow or false to treat as error.
     *                              'defaultRequired' (default false) true to make fields required by default.
     *                              'responseType' (default RESPONSE_TYPE_ARRAY)
     *                                  Determines the return type, as described in the return section.
     *
     * @return array|FilterResponse If 'responseType' option is RESPONSE_TYPE_ARRAY:
     *                                  On success: [true, $input filtered, null, array of unknown fields]
     *                                  On error: [false, null, 'error message', array of unknown fields]
     *                              If 'responseType' option is RESPONSE_TYPE_FILTER: a FilterResponse instance
     *
     * @throws Exception
     * @throws InvalidArgumentException Thrown if the 'allowUnknowns' option was not a bool
     * @throws InvalidArgumentException Thrown if the 'defaultRequired' option was not a bool
     * @throws InvalidArgumentException Thrown if the 'responseType' option was not a recognized type.
     * @throws InvalidArgumentException Thrown if the filters for a field were not an array.
     * @throws InvalidArgumentException Thrown if any one filter for a field was not an array.
     * @throws InvalidArgumentException Thrown if the 'required' value for a field was not a bool.
     *
     * @see FiltererInterface::getSpecification For more information on specifications.
     */
    public static function filter(array $specification, array $input, array $options = [])
    {
        $options += self::DEFAULT_OPTIONS;
        $responseType = $options[FiltererOptions::RESPONSE_TYPE];

        $filterer = new Filterer($specification, $options);
        $filterResponse = $filterer->execute($input);

        return self::generateFilterResponse($responseType, $filterResponse);
    }

    /**
     * Return the filter aliases.
     *
     * @return array array where keys are aliases and values pass is_callable().
     */
    public static function getFilterAliases() : array
    {
        return self::$registeredFilterAliases;
    }

    /**
     * Set the filter aliases.
     *
     * @param array $aliases array where keys are aliases and values pass is_callable().
     * @return void
     *
     * @throws Exception Thrown if any of the given $aliases is not valid. @see registerAlias()
     */
    public static function setFilterAliases(array $aliases)
    {
        $originalAliases = self::$registeredFilterAliases;
        self::$registeredFilterAliases = [];
        try {
            foreach ($aliases as $alias => $callback) {
                self::registerAlias($alias, $callback);
            }
        } catch (Throwable $throwable) {
            self::$registeredFilterAliases = $originalAliases;
            throw $throwable;
        }
    }

    /**
     * Register a new alias with the Filterer
     *
     * @param string|int $alias the alias to register
     * @param callable $filter the aliased callable filter
     * @param bool $overwrite Flag to overwrite existing alias if it exists
     *
     * @return void
     *
     * @throws \InvalidArgumentException if $alias was not a string or int
     * @throws Exception if $overwrite is false and $alias exists
     */
    public static function registerAlias($alias, callable $filter, bool $overwrite = false)
    {
        self::assertIfStringOrInt($alias);
        self::assertIfAliasExists($alias, $overwrite);
        self::$registeredFilterAliases[$alias] = $filter;
    }

    /**
     * Filter an array by applying filters to each member
     *
     * @param array $values an array to be filtered. Use the Arrays::filter() before this method to ensure counts when
     *                      you pass into Filterer
     * @param array $filters filters with each specified the same as in @see self::filter.
     *                       Eg [['string', false, 2], ['uint']]
     *
     * @return array the filtered $values
     *
     * @throws FilterException if any member of $values fails filtering
     */
    public static function ofScalars(array $values, array $filters) : array
    {
        $wrappedFilters = [];
        foreach ($values as $key => $item) {
            $wrappedFilters[$key] = $filters;
        }

        list($status, $result, $error) = self::filter($wrappedFilters, $values);
        if (!$status) {
            throw new FilterException($error);
        }

        return $result;
    }

    /**
     * Filter an array by applying filters to each member
     *
     * @param array $values as array to be filtered. Use the Arrays::filter() before this method to ensure counts when
     *                      you pass into Filterer
     * @param array $spec spec to apply to each $values member, specified the same as in @see self::filter.
     *     Eg ['key' => ['required' => true, ['string', false], ['unit']], 'key2' => ...]
     *
     * @return array the filtered $values
     *
     * @throws Exception if any member of $values fails filtering
     */
    public static function ofArrays(array $values, array $spec) : array
    {
        $results = [];
        $errors = [];
        foreach ($values as $key => $item) {
            if (!is_array($item)) {
                $errors[] = "Value at position '{$key}' was not an array";
                continue;
            }

            list($status, $result, $error) = self::filter($spec, $item);
            if (!$status) {
                $errors[] = $error;
                continue;
            }

            $results[$key] = $result;
        }

        if (!empty($errors)) {
            throw new FilterException(implode("\n", $errors));
        }

        return $results;
    }

    /**
     * Filter $value by using a Filterer $spec and Filterer's default options.
     *
     * @param array $value array to be filtered. Use the Arrays::filter() before this method to ensure counts when you
     *                     pass into Filterer
     * @param array $spec spec to apply to $value, specified the same as in @see self::filter.
     *     Eg ['key' => ['required' => true, ['string', false], ['unit']], 'key2' => ...]
     *
     * @return array the filtered $value
     *
     * @throws FilterException if $value fails filtering
     */
    public static function ofArray(array $value, array $spec) : array
    {
        list($status, $result, $error) = self::filter($spec, $value);
        if (!$status) {
            throw new FilterException($error);
        }

        return $result;
    }

    private static function assertIfStringOrInt($alias)
    {
        if (!is_string($alias) && !is_int($alias)) {
            throw new InvalidArgumentException('$alias was not a string or int');
        }
    }

    private static function assertIfAliasExists($alias, bool $overwrite)
    {
        if (array_key_exists($alias, self::$registeredFilterAliases) && !$overwrite) {
            throw new Exception("Alias '{$alias}' exists");
        }
    }

    private static function checkForUnknowns(array $leftOverInput, array $errors) : array
    {
        foreach ($leftOverInput as $field => $value) {
            $errors[$field] = "Field '{$field}' with value '" . trim(var_export($value, true), "'") . "' is unknown";
        }

        return $errors;
    }

    private static function handleAllowUnknowns(bool $allowUnknowns, array $leftOverInput, array $errors) : array
    {
        if (!$allowUnknowns) {
            $errors = self::checkForUnknowns($leftOverInput, $errors);
        }

        return $errors;
    }

    private static function handleRequiredFields(bool $required, string $field, array $errors) : array
    {
        if ($required) {
            $errors[$field] = "Field '{$field}' was required and not present";
        }
        return $errors;
    }

    private static function getRequired($filters, $defaultRequired, $field) : bool
    {
        $required = $filters[FilterOptions::IS_REQUIRED] ?? $defaultRequired;
        if ($required !== false && $required !== true) {
            throw new InvalidArgumentException(
                sprintf("'%s' for field '%s' was not a bool", FilterOptions::IS_REQUIRED, $field)
            );
        }

        return $required;
    }

    private static function assertFiltersIsAnArray($filters, string $field)
    {
        if (!is_array($filters)) {
            throw new InvalidArgumentException("filters for field '{$field}' was not a array");
        }
    }

    private static function handleCustomError(
        string $field,
        $value,
        Throwable $e,
        array $errors,
        string $customError = null
    ) : array {
        $error = $customError;
        if ($error === null) {
            $errorFormat = "Field '%s' with value '{value}' failed filtering, message '%s'";
            $error = sprintf($errorFormat, $field, $e->getMessage());
        }

        $errors[$field] = str_replace('{value}', trim(var_export($value, true), "'"), $error);
        return $errors;
    }

    private static function assertFunctionIsCallable($function, string $field)
    {
        if (!is_callable($function)) {
            throw new Exception(
                "Function '" . trim(var_export($function, true), "'") . "' for field '{$field}' is not callable"
            );
        }
    }

    private static function handleFilterAliases($function, $filterAliases)
    {
        if ((is_string($function) || is_int($function)) && array_key_exists($function, $filterAliases)) {
            $function = $filterAliases[$function];
        }

        return $function;
    }

    private static function assertFilterIsArray($filter, string $field)
    {
        if (!is_array($filter)) {
            throw new InvalidArgumentException("filter for field '{$field}' was not a array");
        }
    }

    private static function validateThrowOnError(array &$filters, string $field) : bool
    {
        if (!array_key_exists(FilterOptions::THROW_ON_ERROR, $filters)) {
            return false;
        }

        $throwOnError = $filters[FilterOptions::THROW_ON_ERROR];
        if ($throwOnError !== true && $throwOnError !== false) {
            throw new InvalidArgumentException(
                sprintf(self::INVALID_BOOLEAN_FILTER_OPTION, FilterOptions::THROW_ON_ERROR, $field)
            );
        }

        unset($filters[FilterOptions::THROW_ON_ERROR]);

        return $throwOnError;
    }

    private static function validateReturnOnNull(array &$filters, string $field) : bool
    {
        if (!array_key_exists(FilterOptions::RETURN_ON_NULL, $filters)) {
            return false;
        }

        $returnOnNull = $filters[FilterOptions::RETURN_ON_NULL];
        if ($returnOnNull !== true && $returnOnNull !== false) {
            throw new InvalidArgumentException(
                sprintf(self::INVALID_BOOLEAN_FILTER_OPTION, FilterOptions::RETURN_ON_NULL, $field)
            );
        }

        unset($filters[FilterOptions::RETURN_ON_NULL]);

        return $returnOnNull;
    }

    private static function validateCustomError(array &$filters, string $field)
    {
        $customError = null;
        if (array_key_exists(FilterOptions::CUSTOM_ERROR, $filters)) {
            $customError = $filters[FilterOptions::CUSTOM_ERROR];
            if (!is_string($customError) || trim($customError) === '') {
                throw new InvalidArgumentException(
                    sprintf("%s for field '%s' was not a non-empty string", FilterOptions::CUSTOM_ERROR, $field)
                );
            }

            unset($filters[FilterOptions::CUSTOM_ERROR]);//unset so its not used as a filter
        }

        return $customError;
    }

    private static function getAllowUnknowns(array $options) : bool
    {
        $allowUnknowns = $options[FiltererOptions::ALLOW_UNKNOWNS];
        if ($allowUnknowns !== false && $allowUnknowns !== true) {
            throw new InvalidArgumentException(sprintf("'%s' option was not a bool", FiltererOptions::ALLOW_UNKNOWNS));
        }

        return $allowUnknowns;
    }

    private static function getDefaultRequired(array $options) : bool
    {
        $defaultRequired = $options[FiltererOptions::DEFAULT_REQUIRED];
        if ($defaultRequired !== false && $defaultRequired !== true) {
            throw new InvalidArgumentException(
                sprintf("'%s' option was not a bool", FiltererOptions::DEFAULT_REQUIRED)
            );
        }

        return $defaultRequired;
    }

    /**
     * @param string         $responseType   The type of object that should be returned.
     * @param FilterResponse $filterResponse The filter response to generate the typed response from.
     *
     * @return array|FilterResponse
     *
     * @see filter For more information on how responseType is handled and returns are structured.
     */
    private static function generateFilterResponse(string $responseType, FilterResponse $filterResponse)
    {
        if ($responseType === self::RESPONSE_TYPE_FILTER) {
            return $filterResponse;
        }

        if ($responseType === self::RESPONSE_TYPE_ARRAY) {
            return [
                $filterResponse->success,
                $filterResponse->success ? $filterResponse->filteredValue : null,
                $filterResponse->errorMessage,
                $filterResponse->unknowns
            ];
        }

        throw new InvalidArgumentException(sprintf("'%s' was not a recognized value", FiltererOptions::RESPONSE_TYPE));
    }

    private function addUsedInputToFilter(array $uses, array $filteredInput, string $field, array &$filter)
    {
        foreach ($uses as $usedField) {
            if (array_key_exists($usedField, $filteredInput)) {
                array_push($filter, $filteredInput[$usedField]);
                continue;
            }

            throw new FilterException(
                "{$field} uses {$usedField} but {$usedField} was not given."
            );
        }
    }
}
