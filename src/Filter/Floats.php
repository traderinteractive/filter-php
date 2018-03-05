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
     * @param string|float $value     the value to filter to a float
     * @param bool         $allowNull Set to true if NULL values are allowed. The filtered result of a NULL value is
     *                                NULL
     * @param float        $minValue  The minimum acceptable value
     * @param float        $maxValue  The maximum acceptable value
     * @param bool         $castInts  Flag to cast $value to float if it is an integer
     *
     * @return float|null The filtered value
     *
     * @throws Exception if $value is greater than $maxValue
     * @see is_numeric
     */
    public static function filter(
        $value,
        bool $allowNull = false,
        float $minValue = null,
        float $maxValue = null,
        bool $castInts = false
    ) {
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

            //This is the only case (that we know of) where is_numeric does not return correctly cast-able float
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
