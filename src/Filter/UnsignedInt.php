<?php

namespace TraderInteractive\Filter;

/**
 * A collection of filters for unsigned integers.
 */
final class UnsignedInt
{
    /**
     * Filters $value to an unsigned integer strictly.
     *
     * @see \TraderInteractive\Filter\Ints::filter()
     *
     * @param mixed $value     The value to be checked.
     * @param bool  $allowNull Indicates if the value can be null.
     * @param int   $minValue  Indicates the minimum acceptable value.
     * @param int   $maxValue  Indicates the maximum acceptable value.
     *
     * @return int|null
     *
     * @throws Exception
     */
    public static function filter($value, bool $allowNull = false, int $minValue = null, int $maxValue = PHP_INT_MAX)
    {
        if ($minValue === null) {
            $minValue = 0;
        } elseif (is_int($minValue) && $minValue < 0) {
            throw new \InvalidArgumentException("{$minValue} was not greater or equal to zero");
        }

        return Ints::filter($value, $allowNull, $minValue, $maxValue);
    }
}
