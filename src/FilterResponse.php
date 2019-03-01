<?php

namespace TraderInteractive;

/**
 * This object contains the various data returned by a filter action.
 *
 * @property-read bool        $success       TRUE if the filter was successful or FALSE if errors were encountered.
 * @property-read mixed       $filteredValue The input values after being filtered.
 * @property-read array       $errors        Any errors encountered during the filter process.
 * @property-read string|null $errorMessage  An error message generated from the errors. NULL if no errors.
 * @property-read mixed       $unknowns      The values that were unknown during filtering.
 */
final class FilterResponse
{
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
        $this->success = count($errors) === 0;
        $this->filteredValue = $filteredValue;
        $this->errors = $errors;
        $this->errorMessage = $this->success ? null : implode("\n", $errors);
        $this->unknowns = $unknowns;
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
