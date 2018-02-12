<?php
/**
 * Defines the DominionEnterprises\Filter\Booleans class.
 */

namespace DominionEnterprises\Filter;

/**
 * A collection of filters for booleans.
 */
final class Booleans
{
    /**
     * Filters $value to a boolean strictly.
     *
     * $value must be a bool or 'true' or 'false' disregarding case and whitespace.
     *
     * The return value is the bool, as expected by the \DominionEnterprises\Filterer class.
     *
     * @param string|bool $value the value to filter to a boolean
     * @param bool $allowNull Set to true if NULL values are allowed. The filtered result of a NULL value is NULL
     * @param array $trueValues Array of values which represent the boolean true value. Values should be lowercased
     * @param array $falseValues Array of values which represent the boolean false value. Values should be lowercased
     *
     * @return bool|null the filtered $value
     *
     * @throws \InvalidArgumentException if $allowNull is not a boolean
     * @throws Exception if $value is not a string
     * @throws Exception if $value is not 'true' or 'false' disregarding case and whitespace
     */
    public static function filter(
        $value,
        $allowNull = false,
        array $trueValues = ['true'],
        array $falseValues = ['false']
    ) {
        if ($allowNull !== false && $allowNull !== true) {
            throw new \InvalidArgumentException('$allowNull was not a bool');
        }

        if ($allowNull === true && $value === null) {
            return null;
        }

        if (is_bool($value)) {
            return $value;
        }

        if (!is_string($value)) {
            throw new Exception('"' . var_export($value, true) . '" $value is not a string');
        }

        $value = trim($value);

        $value = strtolower($value);

        if (in_array($value, $trueValues, true)) {
            return true;
        }

        if (in_array($value, $falseValues, true)) {
            return false;
        }

        throw new Exception(
            sprintf(
                "%s is not '%s' disregarding case and whitespace",
                $value,
                implode("' or '", array_merge($trueValues, $falseValues))
            )
        );
    }

    /**
     * Filters the boolean $value to the given $true and $false cases
     *
     * @param boolean $value The boolean value to convert.
     * @param mixed   $true  The value to return on the true case.
     * @param mixed   $false The value to return on the false case.
     *
     * @return mixed
     *
     * @throws Exception Thrown if $value is not a boolean
     */
    public static function convert($value, $true = 'true', $false = 'false')
    {
        if ($value !== false && $value !== true) {
            throw new Exception('$value was not a bool');
        }

        return $value ? $true : $false;
    }
}
