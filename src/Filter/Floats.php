<?php

namespace TraderInteractive\Filter;

/**
 * A collection of filters for floats.
 */
final class Floats
{
    /**
     * Filters $value to a float strictly.
     *
     * The return value is the float, as expected by the \TraderInteractive\Filterer class.
     *
     * @param string|float $value the value to filter to a float
     * @param bool $allowNull Set to true if NULL values are allowed. The filtered result of a NULL value is NULL
     * @param int $minValue The minimum acceptable value
     * @param int $maxValue The maximum acceptable value
     * @param bool $castInts Flag to cast $value to float if it is an integer
     *
     * @return float|null The filtered value
     *
     * @see is_numeric
     * @throws \InvalidArgumentException if $allowNull is not a boolean
     * @throws \InvalidArgumentException if $minValue is not null and not a float
     * @throws \InvalidArgumentException if $maxValue is not null and not a float
     * @throws \InvalidArgumentException if $castInts is not a boolean
     * @throws Exception if $value does not pass is_numeric
     * @throws Exception if $value is hex format
     * @throws Exception if $value is not a string or float
     * @throws Exception if $value overflow or underflows
     * @throws Exception if $value is less than $minValue
     * @throws Exception if $value is greater than $maxValue
     */
    public static function filter($value, $allowNull = false, $minValue = null, $maxValue = null, $castInts = false)
    {
        if ($allowNull !== false && $allowNull !== true) {
            throw new \InvalidArgumentException('"' . var_export($allowNull, true) . '" $allowNull was not a bool');
        }

        if ($minValue !== null && !is_float($minValue)) {
            throw new \InvalidArgumentException('"' . var_export($minValue, true) . '" $minValue was not a float');
        }

        if ($maxValue !== null && !is_float($maxValue)) {
            throw new \InvalidArgumentException('"' . var_export($maxValue, true) . '" $maxValue was not a float');
        }

        if ($castInts !== false && $castInts !== true) {
            throw new \InvalidArgumentException('"' . var_export($castInts, true) . '" $castInts was not a bool');
        }

        if ($allowNull === true && $value === null) {
            return null;
        }

        $valueFloat = null;
        if (is_float($value)) {
            $valueFloat = $value;
        } elseif (is_int($value) && $castInts) {
            $valueFloat = (float)$value;
        } elseif (is_string($value)) {
            $value = trim($value);

            if (!is_numeric($value)) {
                throw new Exception("{$value} does not pass is_numeric");
            }

            $value = strtolower($value);

            //This is the only case (that we know of) where is_numeric does not return correctly castable float
            if (strpos($value, 'x') !== false) {
                throw new Exception("{$value} is hex format");
            }

            $valueFloat = (float)$value;
        } else {
            throw new Exception('"' . var_export($value, true) . '" $value is not a string');
        }

        if (is_infinite($valueFloat)) {
            throw new Exception("{$value} overflow");
        }

        if ($minValue !== null && $valueFloat < $minValue) {
            throw new Exception("{$valueFloat} is less than {$minValue}");
        }

        if ($maxValue !== null && $valueFloat > $maxValue) {
            throw new Exception("{$valueFloat} is greater than {$maxValue}");
        }

        return $valueFloat;
    }
}
