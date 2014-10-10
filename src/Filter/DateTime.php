<?php
namespace DominionEnterprises\Filter;

/**
 * A collection of filters for filtering strings into \DateTime objects.
 */
class DateTime
{
    /**
     * Filters the given value into a \DateTime object.
     *
     * @param mixed         $value     The value to be filtered.
     * @param boolean       $allowNull True to allow nulls through, and false (default) if nulls should not be allowed.
     * @param \DateTimeZone $timezone  A \DateTimeZone object representing the timezone of $value.
     *                                 If $timezone is omitted, the current timezone will be used.
     *
     * @return \DateTime
     *
     * @throws \InvalidArgumentException Thrown if $allowNull was not a boolean value.
     * @throws \Exception if the value did not pass validation.
     */
    public static function filter($value, $allowNull = false, \DateTimeZone $timezone = null)
    {
        if ($allowNull !== false && $allowNull !== true) {
            throw new \InvalidArgumentException('$allowNull was not a boolean value');
        }

        if ($value === null && $allowNull) {
            return null;
        }

        if ($value instanceof \DateTime) {
            return $value;
        }

        if (!is_string($value) && !is_int($value)) {
            throw new \Exception('$value is not a string or integer');
        }

        if (ctype_digit($value)) {
            $value = "@{$value}";
        }

        if (trim($value) == '') {
            throw new \Exception('$value is not a string or integer');
        }

        return new \DateTime($value, $timezone);
    }
}
