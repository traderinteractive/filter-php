<?php
/**
 * Defines the DominionEnterprises\Filterer class.
 */

namespace DominionEnterprises;

/**
 * Class to filter an array of input.
 */
final class Filterer
{
    private static $filterAliases = [
        'in' => '\DominionEnterprises\Filter\Arrays::in',
        'array' => '\DominionEnterprises\Filter\Arrays::filter',
        'bool' => '\DominionEnterprises\Filter\Booleans::filter',
        'float' => '\DominionEnterprises\Filter\Floats::filter',
        'int' => '\DominionEnterprises\Filter\Ints::filter',
        'uint' => '\DominionEnterprises\Filter\UnsignedInt::filter',
        'string' => '\DominionEnterprises\Filter\Strings::filter',
        'ofScalars' => '\DominionEnterprises\Filter\Arrays::ofScalars',
        'ofArrays' => '\DominionEnterprises\Filter\Arrays::ofArrays',
        'ofArray' => '\DominionEnterprises\Filter\Arrays::ofArray',
        'url' => '\DominionEnterprises\Filter\Url::filter',
        'email' => '\DominionEnterprises\Filter\Email::filter',
        'explode' => '\DominionEnterprises\Filter\Strings::explode',
        'flatten' => '\DominionEnterprises\Filter\Arrays::flatten',
        'date' => '\DominionEnterprises\Filter\DateTime::filter',
        'timezone' => '\DominionEnterprises\Filter\DateTimeZone::filter',
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
     * list($status, $result, $error, $unknowns) = DominionEnterprises\Filterer::filter(
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
     * @throws \Exception
     * @throws \InvalidArgumentException if 'allowUnknowns' option was not a bool
     * @throws \InvalidArgumentException if 'defaultRequired' option was not a bool
     * @throws \InvalidArgumentException if filters for a field was not a array
     * @throws \InvalidArgumentException if a filter for a field was not a array
     * @throws \InvalidArgumentException if 'required' for a field was not a bool
     */
    public static function filter(array $spec, array $input, array $options = [])
    {
        $options += ['allowUnknowns' => false, 'defaultRequired' => false];

        $allowUnknowns = $options['allowUnknowns'];
        $defaultRequired = $options['defaultRequired'];

        if ($allowUnknowns !== false && $allowUnknowns !== true) {
            throw new \InvalidArgumentException("'allowUnknowns' option was not a bool");
        }

        if ($defaultRequired !== false && $defaultRequired !== true) {
            throw new \InvalidArgumentException("'defaultRequired' option was not a bool");
        }

        $inputToFilter = array_intersect_key($input, $spec);
        $leftOverSpec = array_diff_key($spec, $input);
        $leftOverInput = array_diff_key($input, $spec);

        $errors = [];
        foreach ($inputToFilter as $field => $value) {
            $filters = $spec[$field];

            if (!is_array($filters)) {
                throw new \InvalidArgumentException("filters for field '{$field}' was not a array");
            }

            $customError = null;
            if (array_key_exists('error', $filters)) {
                $customError = $filters['error'];
                if (!is_string($customError) || trim($customError) === '') {
                    throw new \InvalidArgumentException("error for field '{$field}' was not a non-empty string");
                }

                unset($filters['error']);//unset so its not used as a filter
            }

            unset($filters['required']);//doesnt matter if required since we have this one
            unset($filters['default']);//doesnt matter if there is a default since we have a value
            foreach ($filters as $filter) {
                if (!is_array($filter)) {
                    throw new \InvalidArgumentException("filter for field '{$field}' was not a array");
                }

                if (empty($filter)) {
                    continue;
                }

                $function = array_shift($filter);
                if ((is_string($function) || is_int($function)) && array_key_exists($function, self::$filterAliases)) {
                    $function = self::$filterAliases[$function];
                }

                if (!is_callable($function)) {
                    throw new \Exception(
                        "Function '" . trim(var_export($function, true), "'") . "' for field '{$field}' is not callable"
                    );
                }

                array_unshift($filter, $value);
                try {
                    $value = call_user_func_array($function, $filter);
                } catch (\Exception $e) {
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
                    continue 2;//next field
                }
            }

            $inputToFilter[$field] = $value;
        }

        foreach ($leftOverSpec as $field => $filters) {
            if (!is_array($filters)) {
                throw new \InvalidArgumentException("filters for field '{$field}' was not a array");
            }

            $required = isset($filters['required']) ? $filters['required'] : $defaultRequired;

            if ($required !== false && $required !== true) {
                throw new \InvalidArgumentException("'required' for field '{$field}' was not a bool");
            }

            if (array_key_exists('default', $filters)) {
                $inputToFilter[$field] = $filters['default'];
                continue;
            }

            if ($required) {
                $errors[] = "Field '{$field}' was required and not present";
            }
        }

        if (!$allowUnknowns) {
            foreach ($leftOverInput as $field => $value) {
                $errors[] = "Field '{$field}' with value '" . trim(var_export($value, true), "'") . "' is unknown";
            }
        }

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
    public static function getFilterAliases()
    {
        return self::$filterAliases;
    }

    /**
     * Set the filter aliases.
     *
     * @param array $aliases array where keys are aliases and values pass is_callable().
     * @return void
     *
     * @throws \Exception Thrown if any of the given $aliases is not valid. @see registerAlias()
     */
    public static function setFilterAliases(array $aliases)
    {
        $originalAliases = self::$filterAliases;
        self::$filterAliases = [];
        try {
            foreach ($aliases as $alias => $callback) {
                self::registerAlias($alias, $callback);
            }
        } catch (\Exception $e) {
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
     * @throws \InvalidArgumentException if $overwrite was not a bool
     * @throws \Exception if $overwrite is false and $alias exists
     */
    public static function registerAlias($alias, callable $filter, $overwrite = false)
    {
        if (!is_string($alias) && !is_int($alias)) {
            throw new \InvalidArgumentException('$alias was not a string or int');
        }

        if ($overwrite !== false && $overwrite !== true) {
            throw new \InvalidArgumentException('$overwrite was not a bool');
        }

        if (array_key_exists($alias, self::$filterAliases) && !$overwrite) {
            throw new \Exception("Alias '{$alias}' exists");
        }

        self::$filterAliases[$alias] = $filter;
    }
}
