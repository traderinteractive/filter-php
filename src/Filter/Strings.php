<?php
/**
 * Defines the DominionEnterprises\Filter\Strings class.
 */

namespace DominionEnterprises\Filter;

/**
 * A collection of filters for strings.
 */
final class Strings
{
    /**
     * Filter a string.
     *
     * Verify that the passed in value  is a string.  By default, nulls are not allowed, and the length is restricted
     * between 1 and PHP_INT_MAX.  These parameters can be overwritten for custom behavior.
     *
     * The return value is the string, as expected by the \DominionEnterprises\Filterer class.
     *
     * @param mixed $value The value to filter.
     * @param bool $allowNull True to allow nulls through, and false (default) if nulls should not be allowed.
     * @param int $minLength Minimum length to allow for $value.
     * @param int $maxLength Maximum length to allow for $value.
     * @return string|null The passed in $value.
     *
     * @throws Exception if the value did not pass validation.
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

        if (is_scalar($value)) {
            $value = "{$value}";
        }

        if (is_object($value) && method_exists($value, '__toString')) {
            $value = (string)$value;
        }

        if (!is_string($value)) {
            throw new Exception("Value '" . var_export($value, true) . "' is not a string");
        }

        $valueLength = strlen($value);

        if ($valueLength < $minLength || $valueLength > $maxLength) {
            throw new Exception(
                sprintf(
                    "Value '%s' with length '%d' is less than '%d' or greater than '%d'",
                    $value,
                    $valueLength,
                    $minLength,
                    $maxLength
                )
            );
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
     * @throws Exception if the value is not a string.
     * @throws \InvalidArgumentException if the delimiter does not pass validation.
     */
    public static function explode($value, $delimiter = ',')
    {
        if (!is_string($value)) {
            throw new Exception("Value '" . var_export($value, true) . "' is not a string");
        }

        if (!is_string($delimiter) || empty($delimiter)) {
            throw new \InvalidArgumentException(
                "Delimiter '" . var_export($delimiter, true) . "' is not a non-empty string"
            );
        }

        return explode($delimiter, $value);
    }
}
