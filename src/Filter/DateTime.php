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
     * @throws Exception if the value did not pass validation.
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

        if (is_int($value) || ctype_digit($value)) {
            $value = "@{$value}";
        }

        if (!is_string($value) || trim($value) == '') {
            throw new Exception('$value is not a non-empty string');
        }

        return new \DateTime($value, $timezone);
    }

    /**
     * Filters the give \DateTime object to a formatted string.
     *
     * @param \DateTimeInterface $dateTime The date to be formatted.
     * @param string             $format   The format of the outputted date string.
     *
     * @return string
     *
     * @throws \InvalidArgumentException Thrown if $format is not a string
     */
    public static function format(\DateTimeInterface $dateTime, $format = 'c')
    {
        if (!is_string($format) || trim($format) === '') {
            throw new \InvalidArgumentException('$format is not a non-empty string');
        }

        return $dateTime->format($format);
    }
}
