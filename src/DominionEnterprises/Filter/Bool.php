<?php
/**
 * Defines the DominionEnterprises\Filter\Bool class.
 */

namespace DominionEnterprises\Filter;

/**
 * A collection of filters for booleans.
 */
final class Bool
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
     *
     * @return bool the filtered $value
     *
     * @throws \InvalidArgumentException if $allowNull is not a boolean
     * @throws \Exception if $value is not a string
     * @throws \Exception if $value is not 'true' or 'false' disregarding case and whitespace
     */
    public static function filter($value, $allowNull = false)
    {
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
            throw new \Exception('"' . var_export($value, true) . '" $value is not a string');
        }

        $value = trim($value);

        $value = strtolower($value);

        if ($value === 'true') {
            return true;
        } elseif ($value === 'false') {
            return false;
        }

        throw new \Exception("{$value} is not 'true' or 'false' disregarding case and whitespace");
    }
}
