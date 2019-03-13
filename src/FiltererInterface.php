<?php

namespace TraderInteractive;

interface FiltererInterface
{
    /**
     * @param mixed $input The input to filter.
     *
     * @return FilterResponse
     */
    public function execute(array $input) : FilterResponse;

    /**
     * Returns the filter aliases of the filterer.
     *
     * An array where the keys are shorthand aliases like 'flatten' and the values are anything that can pass
     * is_callable() and accept a value to filter as its first argument.
     *
     * @return array
     */
    public function getAliases() : array;

    /**
     * Returns the specification of the filterer.
     *
     * An array where each key is a known input field and each value is an array of filters. Each filter should be an
     * array with the first member being a filter alias or anything that can pass is_callable() and accept a value to
     * filter as its first argument.
     *
     * The rest of the members are extra arguments to the callable. The result of one filter will be the
     * first argument to the next filter.
     *
     * In addition to the filters, the specification values may contain a 'required' key that, if set to true, will
     * cause an error to be thrown if the value is absent.
     *
     * A 'default' specification value may be used to substitute in a default to the input when the key is not present
     * (whether 'required' is specified or not).
     *
     * @return array
     */
    public function getSpecification() : array;

    /**
     * Returns an identical filterer with the filter aliases set to the new value.
     *
     * @param array $filterAliases
     *
     * @return FiltererInterface
     */
    public function withAliases(array $filterAliases) : FiltererInterface;

    /**
     * Returns an identical filterer with the specification set to the new value.
     *
     * @param array $specification
     *
     * @return FiltererInterface
     */
    public function withSpecification(array $specification) : FiltererInterface;
}
