<?php
/**
 * Defines the DominionEnterprises\Filter\UnsignedInt class.
 */

namespace DominionEnterprises\Filter;

use DominionEnterprises\Filter\Int;

/**
 * A collection of filters for unsigned integers.
 */
final class UnsignedInt
{
    /**
     * Filters $value to an unsigned integer strictly.
     *
     * @see \DominionEnterprises\Filter\Int::filter()
     *
     * @throws \InvalidArgumentException if $minValue was not greater or equal to zero
     */
    public static function filter($value, $allowNull = false, $minValue = null, $maxValue = PHP_INT_MAX)
    {
        if ($minValue === null) {
            $minValue = 0;
        } elseif (is_int($minValue) && $minValue < 0) {
            throw new \InvalidArgumentException("{$minValue} was not greater or equal to zero");
        }

        return Int::filter($value, $allowNull, $minValue, $maxValue);
    }
}
