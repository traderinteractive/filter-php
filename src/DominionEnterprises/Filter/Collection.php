<?php
/**
 * Defines the DominionEnterprises\Filter\Collection class.
 */

namespace DominionEnterprises\Filter;

/**
 * A collection of filters for collections.
 */
final class Collection
{
    /**
     * Filter an array by throwing if not an array or an empty array.
     *
     * The return value is the $value, as expected by the \DominionEnterprises\Filterer class.
     *
     * @param mixed $value the value to filter
     *
     * @return the passed in value
     *
     * @throws \Exception if $value is not an array
     * @throws \Exception if $value is an empty array
     */
    public static function notEmpty($value)
    {
        if (!is_array($value)) {
            throw new \Exception("Value '" . trim(var_export($value, true), "'") . "' is not an array");
        }

        if (empty($value)) {
            throw new \Exception('Array is empty');
        }

        return $value;
    }

    /**
     * Filter an array by throwing if $value is not in $haystack adhering to $strict.
     *
     * The return value is the $value, as expected by the \DominionEnterprises\Filterer class.
     *
     * @param mixed $value value to search for
     * @param array $haystack array to search in
     * @param bool $strict to compare strictly or not. @see in_array()
     *
     * @return the passed in value
     *
     * @see in_array()
     * @throws \InvalidArgumentException if $strict was not a bool
     * @throws \Exception if $value is not in array $haystack
     */
    public static function in($value, array $haystack, $strict = true)
    {
        if ($strict !== true && $strict !== false) {
            throw new \InvalidArgumentException('$strict was not a bool');
        }

        if (!in_array($value, $haystack, $strict)) {
            throw new \Exception("Value '" . trim(var_export($value, true), "'") . "' is not in array " . var_export($haystack, true) . '"');
        }

        return $value;
    }
}
