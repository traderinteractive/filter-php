<?php
/**
 * Defines the DominionEnterprises\Filter\Url class.
 */

namespace DominionEnterprises\Filter;

/**
 * A collection of filters for urls.
 */
final class Url
{
    /**
     * Filter an url
     *
     * Filters value as URL (according to » http://www.faqs.org/rfcs/rfc2396)
     *
     * The return value is the url, as expected by the \DominionEnterprises\Filterer class.
     * By default, nulls are not allowed.
     *
     * @param mixed $value The value to filter.
     * @param bool $allowNull True to allow nulls through, and false (default) if nulls should not be allowed.
     *
     * @return string The passed in $value.
     *
     * @throws \Exception if the value did not pass validation.
     * @throws \InvalidArgumentException if one of the parameters was not correctly typed.
     */
    public static function filter($value, $allowNull = false)
    {
        if ($allowNull !== false && $allowNull !== true) {
            throw new \InvalidArgumentException('$allowNull was not a boolean value');
        }

        if ($allowNull === true && $value === null) {
            return null;
        }

        if (!is_string($value)) {
            throw new \Exception("Value '" . var_export($value, true) . "' is not a string");
        }

        $filteredUrl = filter_var($value, FILTER_VALIDATE_URL);
        if ($filteredUrl === false) {
            throw new \Exception("Value '{$value}' is not a valid url");
        }

        return $filteredUrl;
    }
}
