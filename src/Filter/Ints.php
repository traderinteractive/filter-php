<?php

namespace TraderInteractive\Filter;

/**
 * A collection of filters for integers.
 */
final class Ints
{
    /**
     * Filters $value to an integer strictly.
     *
     * $value must be an int or contain all digits, optionally prepended by a '+' or '-' and optionally surrounded by
     * whitespace to pass the filter.
     *
     * The return value is the int, as expected by the \TraderInteractive\Filterer class.
     *
     * @param string|int $value the value to make an integer
     * @param bool $allowNull Set to true if NULL values are allowed. The filtered result of a NULL value is NULL
     * @param int $minValue The minimum acceptable value
     * @param int $maxValue The maximum acceptable value
     *
     * @return int|null The filtered value
     *
     * @throws \InvalidArgumentException if $allowNull is not a boolean
     * @throws \InvalidArgumentException if $minValue is not null and not an int
     * @throws \InvalidArgumentException if $maxValue is not an int
     * @throws Exception if $value string length is zero
     * @throws Exception if $value does not contain all digits, optionally prepended by a '+' or '-' and optionally
     *                    surrounded by whitespace
     * @throws Exception if $value was greater than a max int of PHP_INT_MAX
     * @throws Exception if $value was less than a min int of ~PHP_INT_MAX
     * @throws Exception if $value is not a string
     * @throws Exception if $value is less than $minValue
     * @throws Exception if $value is greater than $maxValue
     */
    public static function filter($value, bool $allowNull = false, int $minValue = null, int $maxValue = PHP_INT_MAX)
    {
        if ($allowNull === true && $value === null) {
            return null;
        }

        $valueInt = null;
        if (is_int($value)) {
            $valueInt = $value;
        } elseif (is_string($value)) {
            $value = trim($value);

            if (strlen($value) === 0) {
                throw new Exception('$value string length is zero');
            }

            $stringToCheckDigits = $value;

            if ($value[0] === '-' || $value[0] === '+') {
                $stringToCheckDigits = substr($value, 1);
            }

            if (!ctype_digit($stringToCheckDigits)) {
                throw new Exception(
                    "{$value} does not contain all digits, optionally prepended by a '+' or '-' and optionally "
                    . "surrounded by whitespace"
                );
            }

            $phpIntMin = ~PHP_INT_MAX;

            $casted = (int)$value;

            if ($casted === PHP_INT_MAX && $value !== (string)PHP_INT_MAX) {
                throw new Exception("{$value} was greater than a max int of " . PHP_INT_MAX);
            }

            if ($casted === $phpIntMin && $value !== (string)$phpIntMin) {
                throw new Exception("{$value} was less than a min int of {$phpIntMin}");
            }

            $valueInt = $casted;
        } else {
            throw new Exception('"' . var_export($value, true) . '" $value is not a string');
        }

        if ($minValue !== null && $valueInt < $minValue) {
            throw new Exception("{$valueInt} is less than {$minValue}");
        }

        if ($valueInt > $maxValue) {
            throw new Exception("{$valueInt} is greater than {$maxValue}");
        }

        return $valueInt;
    }
}
