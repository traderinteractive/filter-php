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
}
