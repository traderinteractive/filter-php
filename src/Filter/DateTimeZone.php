<?php

namespace TraderInteractive\Filter;

/**
 * A collection of filters for filtering strings into \DateTimeZone objects.
 */
class DateTimeZone
{
    /**
     * Filters the given value into a \DateTimeZone object.
     *
     * @param mixed   $value     The value to be filtered.
     * @param boolean $allowNull True to allow nulls through, and false (default) if nulls should not be allowed.
     *
     * @return \DateTimeZone
     *
     * @throws \InvalidArgumentException Thrown if $allowNull was not a boolean value.
     * @throws Exception if the value did not pass validation.
     */
    public static function filter($value, $allowNull = false)
    {
        if ($allowNull !== false && $allowNull !== true) {
            throw new \InvalidArgumentException('$allowNull was not a boolean value');
        }

        if ($value === null && $allowNull) {
            return null;
        }

        if ($value instanceof \DateTimeZone) {
            return $value;
        }

        if (!is_string($value) || trim($value) == '') {
            throw new Exception('$value not a non-empty string');
        }

        try {
            return new \DateTimeZone($value);
        } catch (\Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode(), $e);
        }
    }
}
