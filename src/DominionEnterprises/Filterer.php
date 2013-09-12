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
     * list($status, $result, $unknowns, $error) = DominionEnterprises\Filterer::filter(
     *     [
     *         'field one' => [[$trimFunc], ['substr', 0, 3], [[$appendFilter, 'filter'], 'boo']],
     *         'field two' => ['required' => true, ['floatval']],
     *         'field three' => ['required' => false, ['floatval']],
     *     ],
     *     ['field one' => ' abcd', 'field two' => '3.14']
     * );
     *
     * var_dump($status);
     * var_dump($result);
     * var_dump($unknowns);
     * var_dump($error);
     * </pre>
     * prints:
     * <pre>
     * bool(true)
     * array(2) {
     *   'field one' =>
     *   string(6) "abcboo"
     *   'field two' =>
     *   double(3.14)
     * }
     * array(0) {
     * }
     * NULL
     * </pre>
     *
     * @param array $spec the specification to apply to the $input. An array where each key is a known input field and each value is an array
     *     of filters. Each filter should be an array with the first member being anything that can pass is_callable() as well as accepting the
     *     value to filter as its first argument. Two examples would be the string 'trim' or an object function specified like [$obj, 'filter'],
     *     see is_callable() documentation. The rest of the members are extra arguments to the callable. The result of one filter will be the
     *     first argument to the next filter. In addition to the filters, the specification values may contain a 'required' key (default false)
     *     that controls the same behavior as the 'defaultRequired' option below but on a per field basis.
     * @param array $input the input the apply the $spec on.
     * @param array $options 'allowUnknowns' (default false) true to allow unknowns or false to treat as error, 'defaultRequired'
     *     (default false) true to make fields required by default and treat as error on absence and false to allow their absence by default,
     *     and 'prepends' as an array of strings to try appending to the filters if filter isnt callable first try
     *
     * @return array on success array('status' => true, 'result' => $input filtered, 'unknowns' => array of unknown fields, 'error' => null)
     *     on error array('status' => false, 'result' => null, 'unknowns' => array of unknown fields, 'error' => 'error message')
     *
     * @throws \Exception
     * @throws \InvalidArgumentException if 'allowUnknowns' option was not a bool
     * @throws \InvalidArgumentException if 'defaultRequired' option was not a bool
     * @throws \InvalidArgumentException if filters for a field was not a array
     * @throws \InvalidArgumentException if a filter for a field was not a array
     * @throws \InvalidArgumentException if 'required' for a field was not a bool
     */
    public static function filter(array $spec, array $input, array $options = array())
    {
        $options += array('allowUnknowns' => false, 'defaultRequired' => false, 'prepends' => array());

        $allowUnknowns = $options['allowUnknowns'];
        $defaultRequired = $options['defaultRequired'];
        $prepends = $options['prepends'];

        if (!is_array($prepends)) {
            throw new \InvalidArgumentException("'prepends' option was not an array");
        }

        foreach ($prepends as $prepend) {
            if (!is_string($prepend)) {
                throw new \InvalidArgumentException('"' . var_export($prepend, true) . '" a given prepend was not a string');
            }
        }

        if ($allowUnknowns !== false && $allowUnknowns !== true) {
            throw new \InvalidArgumentException("'allowUnknowns' option was not a bool");
        }

        if ($defaultRequired !== false && $defaultRequired !== true) {
            throw new \InvalidArgumentException("'defaultRequired' option was not a bool");
        }

        $inputToFilter = array_intersect_key($input, $spec);
        $leftOverSpec = array_diff_key($spec, $input);
        $leftOverInput = array_diff_key($input, $spec);

        $errors = array();
        foreach ($inputToFilter as $field => $value) {
            $filters = $spec[$field];

            if (!is_array($filters)) {
                throw new \InvalidArgumentException("filters for field '{$field}' was not a array");
            }

            unset($filters['required']);//doesnt matter if required since we have this one
            foreach ($filters as $filter) {
                if (!is_array($filter)) {
                    throw new \InvalidArgumentException("filter for field '{$field}' was not a array");
                }

                if (empty($filter)) {
                    continue;
                }

                $function = array_shift($filter);
                if (!is_callable($function)) {
                    $foundCallable = false;

                    if (is_string($function)) {
                        foreach ($prepends as $prepend) {
                            if (is_callable($prepend . $function)) {
                                $function = $prepend . $function;
                                $foundCallable = true;
                                break;
                            }
                        }
                    }

                    if (!$foundCallable) {
                        throw new \Exception("Function '" . trim(var_export($function, true), "'") . "' for field '{$field}' is not callable");
                    }
                }

                array_unshift($filter, $value);
                try {
                    $value = call_user_func_array($function, $filter);
                } catch (\Exception $e) {
                    $error = "Field '{$field}' with value '" . trim(var_export($value, true), "'");
                    $error .= "' failed filtering, message '{$e->getMessage()}'";
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

            $required = array_key_exists('required', $filters) ? $filters['required'] : $defaultRequired;

            if ($required !== false && $required !== true) {
                throw new \InvalidArgumentException("'required' for field '{$field}' was not a bool");
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
            return array('status' => true, 'result' => $inputToFilter, 'unknowns' => $leftOverInput, 'error' => null);
        }

        return array('status' => false, 'result' => null, 'unknowns' => $leftOverInput, 'error' => implode("\n", $errors));
    }
}
