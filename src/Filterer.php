<?php

namespace TraderInteractive;

use Exception;
use Throwable;

/**
 * Class to filter an array of input.
 */
final class Filterer
{
    private static $filterAliases = [
        'in' => '\TraderInteractive\Filter\Arrays::in',
        'array' => '\TraderInteractive\Filter\Arrays::filter',
        'bool' => '\TraderInteractive\Filter\Booleans::filter',
        'float' => '\TraderInteractive\Filter\Floats::filter',
        'int' => '\TraderInteractive\Filter\Ints::filter',
        'bool-convert' => '\TraderInteractive\Filter\Booleans::convert',
        'uint' => '\TraderInteractive\Filter\UnsignedInt::filter',
        'string' => '\TraderInteractive\Filter\Strings::filter',
        'ofScalars' => '\TraderInteractive\Filter\Arrays::ofScalars',
        'ofArrays' => '\TraderInteractive\Filter\Arrays::ofArrays',
        'ofArray' => '\TraderInteractive\Filter\Arrays::ofArray',
        'url' => '\TraderInteractive\Filter\Url::filter',
        'email' => '\TraderInteractive\Filter\Email::filter',
        'explode' => '\TraderInteractive\Filter\Strings::explode',
        'flatten' => '\TraderInteractive\Filter\Arrays::flatten',
        'date' => '\TraderInteractive\Filter\DateTime::filter',
        'date-format' => '\TraderInteractive\Filter\DateTime::format',
        'timezone' => '\TraderInteractive\Filter\DateTimeZone::filter',
    ];

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
     *
     * @return array on success [true, $input filtered, null, array of unknown fields]
     *     on error [false, null, 'error message', array of unknown fields]
     *
     * @throws Exception
     * @throws \InvalidArgumentException if 'allowUnknowns' option was not a bool
     * @throws \InvalidArgumentException if 'defaultRequired' option was not a bool
     * @throws \InvalidArgumentException if filters for a field was not a array
     * @throws \InvalidArgumentException if a filter for a field was not a array
     * @throws \InvalidArgumentException if 'required' for a field was not a bool
     */
    public static function filter(array $spec, array $input, array $options = []) : array
    {
        $options += ['allowUnknowns' => false, 'defaultRequired' => false];

        $allowUnknowns = self::getAllowUnknowns($options);
        $defaultRequired = self::getDefaultRequired($options);

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

        if (empty($errors)) {
            return [true, $inputToFilter, null, $leftOverInput];
        }

        return [false, null, implode("\n", $errors), $leftOverInput];
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

    private static function assertIfStringOrInt($alias)
    {
        if (!is_string($alias) && !is_int($alias)) {
            throw new \InvalidArgumentException('$alias was not a string or int');
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
            throw new \InvalidArgumentException("'required' for field '{$field}' was not a bool");
        }

        return $required;
    }

    private static function assertFiltersIsAnArray($filters, string $field)
    {
        if (!is_array($filters)) {
            throw new \InvalidArgumentException("filters for field '{$field}' was not a array");
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
                "Field '%s' with value '%s' failed filtering, message '%s'",
                $field,
                trim(var_export($value, true), "'"),
                $e->getMessage()
            );
        }

        $errors[] = $error;
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
            throw new \InvalidArgumentException("filter for field '{$field}' was not a array");
        }
    }

    private static function validateCustomError(array $filters, string $field)
    {
        $customError = null;
        if (array_key_exists('error', $filters)) {
            $customError = $filters['error'];
            if (!is_string($customError) || trim($customError) === '') {
                throw new \InvalidArgumentException("error for field '{$field}' was not a non-empty string");
            }

            unset($filters['error']);//unset so its not used as a filter
        }

        return $customError;
    }

    private static function getAllowUnknowns(array $options) : bool
    {
        $allowUnknowns = $options['allowUnknowns'];
        if ($allowUnknowns !== false && $allowUnknowns !== true) {
            throw new \InvalidArgumentException("'allowUnknowns' option was not a bool");
        }

        return $allowUnknowns;
    }

    private static function getDefaultRequired(array $options) : bool
    {
        $defaultRequired = $options['defaultRequired'];
        if ($defaultRequired !== false && $defaultRequired !== true) {
            throw new \InvalidArgumentException("'defaultRequired' option was not a bool");
        }

        return $defaultRequired;
    }
}
