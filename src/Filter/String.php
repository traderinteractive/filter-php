<?php
/**
 * Defines the DominionEnterprises\Filter\String class.
 */

namespace DominionEnterprises\Filter;

/**
 * A collection of filters for strings.
 */
final class String
{
    /**
     * Filter a string.
     *
     * Verify that the passed in value  is a string.  By default, nulls are not allowed, and the length is restricted between 1 and PHP_INT_MAX.
     * These parameters can be overwritten for custom behavior.
     *
     * The return value is the string, as expected by the \DominionEnterprises\Filterer class.
     *
     * @param mixed $value The value to filter.
     * @param bool $allowNull True to allow nulls through, and false (default) if nulls should not be allowed.
     * @param int $minLength Minimum length to allow for $value.
     * @param int $maxLength Maximum length to allow for $value.
     * @return string|null The passed in $value.
     *
     * @throws \Exception if the value did not pass validation.
     * @throws \InvalidArgumentException if one of the parameters was not correctly typed.
     */
    public static function filter($value, $allowNull = false, $minLength = 1, $maxLength = PHP_INT_MAX)
    {
        if ($allowNull !== false && $allowNull !== true) {
            throw new \InvalidArgumentException('$allowNull was not a boolean value');
        }

        if (!is_int($minLength) || $minLength < 0) {
            throw new \InvalidArgumentException('$minLength was not a positive integer value');
        }

        if (!is_int($maxLength) || $maxLength < 0) {
            throw new \InvalidArgumentException('$maxLength was not a positive integer value');
        }

        if ($allowNull === true && $value === null) {
            return null;
        }

        if (!is_string($value)) {
            throw new \Exception("Value '" . var_export($value, true) . "' is not a string");
        }

        $valueLength = strlen($value);

        if ($valueLength < $minLength || $valueLength > $maxLength) {
            throw new \Exception("Value '{$value}' with length '{$valueLength}' is less than '{$minLength}' or greater than '{$maxLength}'");
        }

        return $value;
    }

    /**
     * Explodes a string into an array using the given delimiter.
     *
     * For example, given the string 'foo,bar,baz', this would return the array ['foo', 'bar', 'baz'].
     *
     * @param string $value The string to explode.
     * @param string $delimiter The non-empty delimiter to explode on.
     * @return array The exploded values.
     *
     * @throws \Exception if the value is not a string.
     * @throws \InvalidArgumentException if the delimiter does not pass validation.
     */
    public static function explode($value, $delimiter = ',')
    {
        if (!is_string($value)) {
            throw new \Exception("Value '" . var_export($value, true) . "' is not a string");
        }

        if (!is_string($delimiter) || empty($delimiter)) {
            throw new \InvalidArgumentException("Delimiter '" . var_export($delimiter, true) . "' is not a non-empty string");
        }

        return explode($delimiter, $value);
    }

    /**
     * Method to concat the given prefix and suffix to the given string value.
     *
     * @param mixed   $value     The starting value. This value must be castable as a string.
     * @param boolean $allowNull True to allow nulls through, and false (default) if nulls should not be allowed.
     * @param string  $prefix    The value to prepend to $value.
     * @param string  $suffix    The value to append to $value.
     *
     * @return string
     *
     * @throws \InvalidArgumentException Thrown if $prefix is not a string.
     * @throws \InvalidArgumentException Thrown if $suffix is not a string.
     * @throws \Exception Thrown if $value fails validation.
     */
    public static function concat($value, $allowNull = false, $prefix = '', $suffix = '')
    {
        if ($allowNull !== false && $allowNull !== true) {
            throw new \InvalidArgumentException('$allowNull was not a boolean value');
        }

        if (!is_string($prefix)) {
            throw new \InvalidArgumentException('$prefix was not a string');
        }

        if (!is_string($suffix)) {
            throw new \InvalidArgumentException('$suffix was not a string');
        }

        if (is_scalar($value) || (is_object($value) && method_exists($value, '__toString')) || ($allowNull && $value === null)) {
            return "{$prefix}{$value}{$suffix}";
        }

        throw new \Exception('$value was not filterable as a string');
    }

    /**
     * Method to filter empty or whitespace strings to null.
     *
     * @param string $value The starting value.
     *
     * @return null|string
     *
     * @throws \Exception Thrown if $value cannot be filtered.
     */
    public static function nullify($value)
    {
        if ($value === null) {
            return null;
        }

        if (!is_string($value)) {
            throw new \Exception('$value was not filterable as a string');
        }

        return (trim($value) == '') ? null : $value;
    }

    /**
     * Filters a given string by the given regular expression.
     *
     * @param string $value The starting value
     * @param string $pattern The regex pattern
     *
     * @return string
     *
     * @throws \Exception Thrown if $value cannot be filtered.
     * @throws \InvalidArgumentException Throw if $pattern is not a string.
     * @throws \InvalidArgumentException Throw if $pattern is not a valid regular expression.
     */
    public static function regex($value, $pattern)
    {
        if (!is_string($value)) {
            throw new \Exception('$value was not filterable as a string');
        }

        if (!is_string($pattern)) {
            throw new \InvalidArgumentException('$pattern must be a string');
        }

        $matched = @preg_match($pattern, $value);
        if ($matched === false) {
            throw new \InvalidArgumentException('$pattern is not a valid regular expression');
        }

        if ($matched === 0) {
            throw new \Exception("Value '{$value}' is not match the given regular expression");
        }

        return $value;
    }
}
