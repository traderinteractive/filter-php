<?php

namespace TraderInteractive;

use Exception;
use InvalidArgumentException;
use Throwable;
use TraderInteractive\Exceptions\FilterException;

/**
 * Class to filter an array of input.
 */
final class Filterer
{
    /**
     * @var array
     */
    const DEFAULT_FILTER_ALIASES = [
        'array' => '\\TraderInteractive\\Filter\\Arrays::filter',
        'arrayize' => '\\TraderInteractive\\Filter\\Arrays::arrayize',
        'bool' => '\\TraderInteractive\\Filter\\Booleans::filter',
        'bool-convert' => '\\TraderInteractive\\Filter\\Booleans::convert',
        'concat' => '\\TraderInteractive\\Filter\\Strings::concat',
        'date' => '\\TraderInteractive\\Filter\\DateTime::filter',
        'date-format' => '\\TraderInteractive\\Filter\\DateTime::format',
        'email' => '\\TraderInteractive\\Filter\\Email::filter',
        'explode' => '\\TraderInteractive\\Filter\\Strings::explode',
        'flatten' => '\\TraderInteractive\\Filter\\Arrays::flatten',
        'float' => '\\TraderInteractive\\Filter\\Floats::filter',
        'in' => '\\TraderInteractive\\Filter\\Arrays::in',
        'int' => '\\TraderInteractive\\Filter\\Ints::filter',
        'ofArray' => '\\TraderInteractive\\Filterer::ofArray',
        'ofArrays' => '\\TraderInteractive\\Filterer::ofArrays',
        'ofScalars' => '\\TraderInteractive\\Filterer::ofScalars',
        'redact' => '\\TraderInteractive\\Filter\\Strings::redact',
        'string' => '\\TraderInteractive\\Filter\\Strings::filter',
        'strip-tags' => '\\TraderInteractive\\Filter\\Strings::stripTags',
        'timezone' => '\\TraderInteractive\\Filter\\DateTimeZone::filter',
        'translate' => '\\TraderInteractive\\Filter\\Strings::translate',
        'uint' => '\\TraderInteractive\\Filter\\UnsignedInt::filter',
        'url' => '\\TraderInteractive\\Filter\\Url::filter',
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
     * @var array
     */
    private static $filterAliases = self::DEFAULT_FILTER_ALIASES;

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
     * @param array $spec the specification to apply to the $input. An array where each key is a known input field and
     *                    each value is an array of filters. Each filter should be an array with the first member being
     *                    anything that can pass is_callable() as well as accepting the value to filter as its first
     *                    argument. Two examples would be the string 'trim' or an object function specified like [$obj,
     *                    'filter'], see is_callable() documentation. The rest of the members are extra arguments to the
     *                    callable. The result of one filter will be the first argument to the next filter. In addition
     *                    to the filters, the specification values may contain a 'required' key (default false) that
     *                    controls the same behavior as the 'defaultRequired' option below but on a per field basis. A
     *                    'default' specification value may be used to substitute in a default to the $input when the
     *                    key is not present (whether 'required' is specified or not).
     * @param array $input the input the apply the $spec on.
     * @param array $options 'allowUnknowns' (default false) true to allow unknowns or false to treat as error,
     *                       'defaultRequired' (default false) true to make fields required by default and treat as
     *                       error on absence and false to allow their absence by default
     *                       'responseType' (default RESPONSE_TYPE_ARRAY) Determines the return type, as described
     *                       in the return section.
     *
     * @return array|FilterResponse If 'responseType' option is RESPONSE_TYPE_ARRAY:
     *                                  on success [true, $input filtered, null, array of unknown fields]
     *                                  on error [false, null, 'error message', array of unknown fields]
     *                              If 'responseType' option is RESPONSE_TYPE_FILTER: a FilterResponse instance.
     *
     * @throws Exception
     * @throws InvalidArgumentException if 'allowUnknowns' option was not a bool
     * @throws InvalidArgumentException if 'defaultRequired' option was not a bool
     * @throws InvalidArgumentException if 'responseType' option was not a recognized type
     * @throws InvalidArgumentException if filters for a field was not an array
     * @throws InvalidArgumentException if a filter for a field was not an array
     * @throws InvalidArgumentException if 'required' for a field was not a bool
     */
    public static function filter(array $spec, array $input, array $options = [])
    {
        $options += ['allowUnknowns' => false, 'defaultRequired' => false, 'responseType' => self::RESPONSE_TYPE_ARRAY];

        $allowUnknowns = self::getAllowUnknowns($options);
        $defaultRequired = self::getDefaultRequired($options);
        $responseType = $options['responseType'];

        $inputToFilter = array_intersect_key($input, $spec);
        $leftOverSpec = array_diff_key($spec, $input);
        $leftOverInput = array_diff_key($input, $spec);

        $errors = [];
        foreach ($inputToFilter as $field => $value) {
            $filters = $spec[$field];
            self::assertFiltersIsAnArray($filters, $field);
            $customError = self::validateCustomError($filters, $field);
            unset($filters['required']);//doesn't matter if required since we have this one
            unset($filters['default']);//doesn't matter if there is a default since we have a value
            foreach ($filters as $filter) {
                self::assertFilterIsNotArray($filter, $field);

                if (empty($filter)) {
                    continue;
                }

                $function = array_shift($filter);
                $function = self::handleFilterAliases($function);

                self::assertFunctionIsCallable($function, $field);

                array_unshift($filter, $value);
                try {
                    $value = call_user_func_array($function, $filter);
                } catch (Exception $e) {
                    $errors = self::handleCustomError($field, $value, $e, $errors, $customError);
                    continue 2;//next field
                }
            }

            $inputToFilter[$field] = $value;
        }

        foreach ($leftOverSpec as $field => $filters) {
            self::assertFiltersIsAnArray($filters, $field);
            $required = self::getRequired($filters, $defaultRequired, $field);
            if (array_key_exists('default', $filters)) {
                $inputToFilter[$field] = $filters['default'];
                continue;
            }

            $errors = self::handleRequiredFields($required, $field, $errors);
        }

        $errors = self::handleAllowUnknowns($allowUnknowns, $leftOverInput, $errors);

        return self::generateFilterResponse($responseType, $inputToFilter, $errors, $leftOverInput);
    }

    /**
     * Return the filter aliases.
     *
     * @return array array where keys are aliases and values pass is_callable().
     */
    public static function getFilterAliases() : array
    {
        return self::$filterAliases;
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
        $originalAliases = self::$filterAliases;
        self::$filterAliases = [];
        try {
            foreach ($aliases as $alias => $callback) {
                self::registerAlias($alias, $callback);
            }
        } catch (Exception $e) {
            self::$filterAliases = $originalAliases;
            throw $e;
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
        self::$filterAliases[$alias] = $filter;
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
        if (array_key_exists($alias, self::$filterAliases) && !$overwrite) {
            throw new Exception("Alias '{$alias}' exists");
        }
    }

    private static function checkForUnknowns(array $leftOverInput, array $errors) : array
    {
        foreach ($leftOverInput as $field => $value) {
            $errors[] = "Field '{$field}' with value '" . trim(var_export($value, true), "'") . "' is unknown";
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
            $errors[] = "Field '{$field}' was required and not present";
        }
        return $errors;
    }

    private static function getRequired($filters, $defaultRequired, $field) : bool
    {
        $required = isset($filters['required']) ? $filters['required'] : $defaultRequired;
        if ($required !== false && $required !== true) {
            throw new InvalidArgumentException("'required' for field '{$field}' was not a bool");
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
            $error = sprintf(
                "Field '%s' with value '{value}' failed filtering, message '%s'",
                $field,
                $e->getMessage()
            );
        }

        $errors[] = str_replace('{value}', trim(var_export($value, true), "'"), $error);
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

    private static function handleFilterAliases($function)
    {
        if ((is_string($function) || is_int($function)) && array_key_exists($function, self::$filterAliases)) {
            $function = self::$filterAliases[$function];
        }

        return $function;
    }

    private static function assertFilterIsNotArray($filter, string $field)
    {
        if (!is_array($filter)) {
            throw new InvalidArgumentException("filter for field '{$field}' was not a array");
        }
    }

    private static function validateCustomError(array &$filters, string $field)
    {
        $customError = null;
        if (array_key_exists('error', $filters)) {
            $customError = $filters['error'];
            if (!is_string($customError) || trim($customError) === '') {
                throw new InvalidArgumentException("error for field '{$field}' was not a non-empty string");
            }

            unset($filters['error']);//unset so its not used as a filter
        }

        return $customError;
    }

    private static function getAllowUnknowns(array $options) : bool
    {
        $allowUnknowns = $options['allowUnknowns'];
        if ($allowUnknowns !== false && $allowUnknowns !== true) {
            throw new InvalidArgumentException("'allowUnknowns' option was not a bool");
        }

        return $allowUnknowns;
    }

    private static function getDefaultRequired(array $options) : bool
    {
        $defaultRequired = $options['defaultRequired'];
        if ($defaultRequired !== false && $defaultRequired !== true) {
            throw new InvalidArgumentException("'defaultRequired' option was not a bool");
        }

        return $defaultRequired;
    }

    /**
     * @param string $responseType  The type of object that should be returned.
     * @param array  $filteredValue The filtered input to return.
     * @param array  $errors        The errors to return.
     * @param array  $unknowns      The unknowns to return.
     *
     * @return array|FilterResponse
     *
     * @see filter For more information on how responseType is handled and returns are structured.
     */
    private static function generateFilterResponse(
        string $responseType,
        array $filteredValue,
        array $errors,
        array $unknowns
    ) {
        $filterResponse = new FilterResponse($filteredValue, $errors, $unknowns);

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

        throw new InvalidArgumentException("'responseType' was not a recognized value");
    }
}
