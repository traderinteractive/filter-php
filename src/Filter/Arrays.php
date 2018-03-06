<?php

namespace TraderInteractive\Filter;

use TraderInteractive\Filterer;

/**
 * A collection of filters for arrays.
 */
final class Arrays
{
    /**
     * Filter an array by throwing if not an array or count not in the min/max range.
     *
     * The return value is the $value, as expected by the \TraderInteractive\Filterer class.
     *
     * @param mixed $value the value to filter
     * @param int $minCount the minimum allowed count in the array
     * @param int $maxCount the maximum allowed count in the array
     *
     * @return mixed The passed in value
     *
     * @throws \InvalidArgumentException if $minCount was not an int
     * @throws \InvalidArgumentException if $maxCount was not an int
     * @throws Exception if $value is not an array
     * @throws Exception if $value count is less than $minCount
     * @throws Exception if $value count is greater than $maxCount
     */
    public static function filter($value, int $minCount = 1, int $maxCount = PHP_INT_MAX)
    {
        if (!is_int($minCount)) {
            throw new \InvalidArgumentException('$minCount was not an int');
        }

        if (!is_int($maxCount)) {
            throw new \InvalidArgumentException('$maxCount was not an int');
        }

        if (!is_array($value)) {
            throw new Exception("Value '" . trim(var_export($value, true), "'") . "' is not an array");
        }

        //optimization for default case
        if ($minCount === 1 && empty($value)) {
            throw new Exception('$value count of 0 is less than 1');
        }

        $count = count($value);

        if ($count < $minCount) {
            throw new Exception("\$value count of {$count} is less than {$minCount}");
        }

        if ($count > $maxCount) {
            throw new Exception("\$value count of {$count} is greater than {$maxCount}");
        }

        return $value;
    }

    /**
     * Filter an array by throwing if $value is not in $haystack adhering to $strict.
     *
     * The return value is the $value, as expected by the \TraderInteractive\Filterer class.
     *
     * @param mixed $value value to search for
     * @param array $haystack array to search in
     * @param bool $strict to compare strictly or not. @see in_array()
     *
     * @return mixed The passed in value
     *
     * @see in_array()
     *
     * @throws Exception if $value is not in array $haystack
     */
    public static function in($value, array $haystack, bool $strict = true)
    {
        if (!in_array($value, $haystack, $strict)) {
            throw new Exception(
                "Value '" . trim(var_export($value, true), "'") . "' is not in array " . var_export($haystack, true)
            );
        }

        return $value;
    }

    /**
     * Given a multi-dimensional array, flatten the array to a single level.
     *
     * The order of the values will be maintained, but the keys will not.
     *
     * For example, given the array [[1, 2], [3, [4, 5]]], this would result in the array [1, 2, 3, 4, 5].
     *
     * @param array $value The array to flatten.
     *
     * @return array The single-dimension array.
     */
    public static function flatten(array $value) : array
    {
        $result = [];

        array_walk_recursive(
            $value,
            function ($item) use (&$result) {
                $result[] = $item;
            }
        );

        return $result;
    }
}
