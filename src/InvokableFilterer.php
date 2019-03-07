<?php

namespace TraderInteractive;

use TraderInteractive\Exceptions\FilterException;

final class InvokableFilterer implements FiltererInterface
{
    /**
     * @var Filterer
     */
    private $filterer;

    /**
     * @param FiltererInterface $filterer The base filterer.
     */
    public function __construct(FiltererInterface $filterer)
    {
        $this->filterer = $filterer;
    }

    /**
     * Executes and returns the filtered value.
     *
     * @param array $value The value to filter.
     *
     * @return array The filtered value.
     *
     * @throws FilterException Thrown if an error is encountered during filtering.
     */
    public function __invoke(array $value) : array
    {
        $filterResponse = $this->filterer->execute($value);
        if ($filterResponse->success === false) {
            throw new FilterException($filterResponse->errorMessage);
        }

        return $filterResponse->filteredValue;
    }

    /**
     * @param mixed $input The input to filter.
     *
     * @return FilterResponse
     *
     * @see FiltererInterface::execute
     */
    public function execute(array $input) : FilterResponse
    {
        return $this->filterer->execute($input);
    }

    /**
     * @return array
     *
     * @see FiltererInterface::getAliases
     */
    public function getAliases() : array
    {
        return $this->filterer->getAliases();
    }

    /**
     * @return array
     *
     * @see FiltererInterface::getSpecification
     */
    public function getSpecification() : array
    {
        return $this->filterer->getSpecification();
    }

    /**
     * @param array $filterAliases
     *
     * @return FiltererInterface
     *
     * @see FiltererInterface::withAliases
     */
    public function withAliases(array $filterAliases) : FiltererInterface
    {
        return $this->filterer->withAliases($filterAliases);
    }

    /**
     * @param array $specification
     *
     * @return FiltererInterface
     *
     * @see FiltererInterface::withSpecification
     */
    public function withSpecification(array $specification) : FiltererInterface
    {
        return $this->filterer->withSpecification($specification);
    }
}
