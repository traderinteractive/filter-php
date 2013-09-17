<?php
/**
 * Defines the DominionEnterprises\Filter\Arrays class.
 */

namespace DominionEnterprises\Filter;

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

    /**
     * Filter an array by throwing if any element in the array $value is not a string
     *
     * The return value is the $value, as expected by the \DominionEnterprises\Filterer class.
     *
     * @param mixed $value the value to filter
     * @param bool $allowNull flag to allow elements to be null.
     * @param bool $allowEmpty flag to allow elements to be empty/whitespace strings
     *
     * @return the passed in value
     * @throws \InvalidArgumentException if $allowNull was not a bool
     * @throws \InvalidArgumentException if $allowEmpty was not a bool
     * @throws \Exception if any element not a string or null and $allowNull is false
     * @throws \Exception if any element in the array empty or contains only whitespace and $allowEmpty false
     */
    public static function ofStrings($value, $allowNull = false, $allowEmpty = false)
    {
        if ($allowNull !== true && $allowNull !== false) {
            throw new \InvalidArgumentException('$allowNull was not a bool');
        }

        if ($allowEmpty !== true && $allowEmpty !== false) {
            throw new \InvalidArgumentException('$allowEmpty was not a bool');
        }

        self::filter($value);

        foreach ($value as $key => $element) {
            if ($allowNull && $element === null) {
                continue;
            }

            if (!is_string($element)) {
                throw new \Exception("Value at position '{$key}' was not a string");
            }

            if (!$allowEmpty && trim($element) === '') {
                throw new \Exception("Value at position '{$key}' was empty or whitespace");
            }
        }

        return $value;
    }
}
