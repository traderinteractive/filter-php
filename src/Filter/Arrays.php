<?php
/**
 * Defines the DominionEnterprises\Filter\Arrays class.
 */

namespace DominionEnterprises\Filter;

use DominionEnterprises\Filterer;

/**
 * A collection of filters for arrays.
 */
final class Arrays
{
    /**
     * Filter an array by throwing if not an array or count not in the min/max range.
     *
     * The return value is the $value, as expected by the \DominionEnterprises\Filterer class.
     *
     * @param mixed $value the value to filter
     * @param int $minCount the minimum allowed count in the array
     * @param int $maxCount the maximum allowed count in the array
     *
     * @return the passed in value
     *
     * @throws \InvalidArgumentException if $minCount was not an int
     * @throws \InvalidArgumentException if $maxCount was not an int
     * @throws \Exception if $value is not an array
     * @throws \Exception if $value count is less than $minCount
     * @throws \Exception if $value count is greater than $maxCount
     */
    public static function filter($value, $minCount = 1, $maxCount = PHP_INT_MAX)
    {
        if (!is_int($minCount)) {
            throw new \InvalidArgumentException('$minCount was not an int');
        }

        if (!is_int($maxCount)) {
            throw new \InvalidArgumentException('$maxCount was not an int');
        }

        if (!is_array($value)) {
            throw new \Exception("Value '" . trim(var_export($value, true), "'") . "' is not an array");
        }

        //optimization for default case
        if ($minCount === 1 && empty($value)) {
            throw new \Exception('$value count of 0 is less than 1');
        }

        $count = count($value);

        if ($count < $minCount) {
            throw new \Exception("\$value count of {$count} is less than {$minCount}");
        }

        if ($count > $maxCount) {
            throw new \Exception("\$value count of {$count} is greater than {$maxCount}");
        }

        return $value;
    }

    /**
     * Filter an array by throwing if $value is not in $haystack adhering to $strict.
     *
     * The return value is the $value, as expected by the \DominionEnterprises\Filterer class.
     *
     * @param mixed $value value to search for
     * @param array|callable $haystack array to search in or a callable to retrieve the haystack.
     * @param bool $strict to compare strictly or not. @see in_array()
     *
     * @return the passed in value
     *
     * @see in_array()
     * @throws \InvalidArgumentException if $strict was not a bool
     * @throws \Exception if $value is not in array $haystack
     */
    public static function in($value, $haystack, $strict = true)
    {
        if ($strict !== true && $strict !== false) {
            throw new \InvalidArgumentException('$strict was not a bool');
        }

        if (is_callable($haystack)) {
            $haystack = call_user_func($haystack);
        }

        if (!is_array($haystack)) {
            throw new \InvalidArgumentException('Given $haystack was not an array or result from callable was not an array');
        }

        if (!in_array($value, $haystack, $strict)) {
            throw new \Exception(
                "Value '" . trim(var_export($value, true), "'") . "' is not in array " . var_export($haystack, true)
            );
        }

        return $value;
    }

    /**
     * Filter an array by applying filters to each member
     *
     * @param array $values an array to be filtered. Use the Arrays::filter() before this method to ensure counts when
     *                      you pass into Filterer
     * @param array $filters filters with each specified the same as in @see Filterer::filter.
     *                       Eg [['string', false, 2], ['uint']]
     *
     * @return array the filtered $values
     *
     * @throws \Exception if any member of $values fails filtering
     */
    public static function ofScalars(array $values, array $filters)
    {
        $wrappedFilters = [];
        foreach ($values as $key => $item) {
            $wrappedFilters[$key] = $filters;
        }

        list($status, $result, $error) = Filterer::filter($wrappedFilters, $values);
        if (!$status) {
            throw new \Exception($error);
        }

        return $result;
    }

    /**
     * Filter an array by applying filters to each member
     *
     * @param array $values as array to be filtered. Use the Arrays::filter() before this method to ensure counts when
     *                      you pass into Filterer
     * @param array $spec spec to apply to each $values member, specified the same as in @see Filterer::filter.
     *     Eg ['key' => ['required' => true, ['string', false], ['unit']], 'key2' => ...]
     *
     * @return array the filtered $values
     *
     * @throws \Exception if any member of $values fails filtering
     */
    public static function ofArrays(array $values, array $spec)
    {
        $results = [];
        $errors = [];
        foreach ($values as $key => $item) {
            if (!is_array($item)) {
                $errors[] = "Value at position '{$key}' was not an array";
                continue;
            }

            list($status, $result, $error) = Filterer::filter($spec, $item);
            if (!$status) {
                $errors[] = $error;
                continue;
            }

            $results[$key] = $result;
        }

        if (!empty($errors)) {
            throw new \Exception(implode("\n", $errors));
        }

        return $results;
    }

    /**
     * Filter $value by using a Filterer $spec and Filterer's default options.
     *
     * @param array $value array to be filtered. Use the Arrays::filter() before this method to ensure counts when you
     *                     pass into Filterer
     * @param array $spec spec to apply to $value, specified the same as in @see Filterer::filter.
     *     Eg ['key' => ['required' => true, ['string', false], ['unit']], 'key2' => ...]
     *
     * @return array the filtered $value
     *
     * @throws \Exception if $value fails filtering
     */
    public static function ofArray(array $value, array $spec)
    {
        list($status, $result, $error) = Filterer::filter($spec, $value);
        if (!$status) {
            throw new \Exception($error);
        }

        return $result;
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
    public static function flatten(array $value)
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
