<?php

namespace TraderInteractive\Filter;

use TraderInteractive\Exceptions\FilterException;

/**
 * A collection of filters for Closures.
 */
class Closures
{
    /**
     * Filters $value to a Closure strictly.
     * The return value is \Closure, as expected by the \TraderInteractive\Filterer class.
     *
     * @param mixed $value       the value to filter to a closure function
     * @param bool  $allowNull   Set to true if NULL values are allowed. The filtered result of a NULL value is NULL
     *
     * @return bool|null the filtered $value
     *
     * @throws FilterException
     */
    public static function filter($value, bool $allowNull = false)
    {
        if ($allowNull === true && $value === null) {
            return null;
        }

        if ($value instanceof \Closure) {
            return $value;
        }

        if (is_callable($value)) {
            return \Closure::fromCallable($value);
        }

        throw new FilterException('Value "' . var_export($value, true) . '" is not Closure or $allowNull is not set');
    }
}
