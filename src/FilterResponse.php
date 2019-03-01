<?php

namespace TraderInteractive;

use InvalidArgumentException;
use TraderInteractive\Exceptions\ReadOnlyViolationException;

/**
 * This object contains the various data returned by a filter action.
 *
 * @property bool        $success       TRUE if the filter was successful or FALSE if errors were encountered.
 * @property mixed       $filteredValue The input values after being filtered.
 * @property array       $errors        Any errors encountered during the filter process.
 * @property string|null $errorMessage  An error message generated from the errors. NULL if no errors.
 * @property mixed       $unknowns      The values that were unknown during filtering.
 */
final class FilterResponse
{
    /**
     * @var array
     */
    private $response;

    /**
     * @param array $filteredValue The input values after being filtered.
     * @param array $errors        Any errors encountered during the filter process.
     * @param array $unknowns      The values that were unknown during filtering.
     */
    public function __construct(
        array $filteredValue,
        array $errors = [],
        array $unknowns = []
    ) {
        $success = count($errors) === 0;
        $this->response = [
            'success' => $success,
            'filteredValue' => $filteredValue,
            'errors' => $errors,
            'errorMessage' => $success ? null : implode("\n", $errors),
            'unknowns' => $unknowns,
        ];
    }

    public function __get($name)
    {
        if (array_key_exists($name, $this->response)) {
            return $this->response[$name];
        }

        throw new InvalidArgumentException("Property '{$name}' does not exist");
    }

    public function __set($name, $value)
    {
        if (array_key_exists($name, $this->response)) {
            throw new ReadOnlyViolationException("Property '{$name}' is read-only");
        }

        throw new InvalidArgumentException("Property '{$name}' does not exist");
    }

    /**
     * Converts the response to an array.
     *
     * @return array
     */
    public function toArray() : array
    {
        $filteredValue = $this->success ? $this->filteredValue : null;

        return [
            $this->success,
            $filteredValue,
            $this->errorMessage,
            $this->unknowns
        ];
    }
}
