<?php

namespace DominionEnterprises\Filter;

use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the \DominionEnterprises\Filter\DateTimeZone class.
 *
 * @coversDefaultClass \DominionEnterprises\Filter\DateTimeZone
 */
final class DateTimeZoneTest extends TestCase
{
    /**
     * Verify basic usage of filter().
     *
     * @test
     * @covers ::filter
     *
     * @return void
     */
    public function filter()
    {
        $value = 'Pacific/Honolulu';
        $timezone = DateTimeZone::filter($value);
        $this->assertSame($value, $timezone->getName());
        $this->assertSame(-36000, $timezone->getOffset(new \DateTime('now', $timezone)));
    }

    /**
     * Verify behavior of filter() when $allowNull is not true or false.
     *
     * @test
     * @covers ::filter
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage $allowNull was not a boolean value
     *
     * @return void
     */
    public function filterAllowNullNotBoolean()
    {
        DateTimeZone::filter('America/New_York', 5);
    }

    /**
     * Verify behavior of filter() when $allowNull is true and $value is null.
     *
     * @test
     * @covers ::filter
     *
     * @return void
     */
    public function filterNullAllowed()
    {
        $this->assertNull(DateTimeZone::filter(null, true));
    }

    /**
     * Verify behavior of filter() when $allowNull is false and $value is null.
     *
     * @test
     * @covers ::filter
     * @expectedException \DominionEnterprises\Filter\Exception
     * @expectedExceptionMessage $value not a non-empty string
     *
     * @return void
     */
    public function filterNullNotAllowed()
    {
        $this->assertNull(DateTimeZone::filter(null, false));
    }

    /**
     * Verify behavior of filter() when $value is a \DateTimeZone object.
     *
     * @test
     * @covers ::filter
     *
     * @return void
     */
    public function filterTimeZonePass()
    {
        $timezone = new \DateTimeZone('America/New_York');
        $this->assertSame($timezone, DateTimeZone::filter($timezone));
    }

    /**
     * Verify behavior of filter() when $value is not a valid timezone.
     *
     * @test
     * @covers ::filter
     * @expectedException \DominionEnterprises\Filter\Exception
     * @expectedExceptionMessage Unknown or bad timezone (INVALID)
     */
    public function filterInvalidName()
    {
        $timezone = DateTimeZone::filter('INVALID');
    }

    /**
     * Verify behavior of filter() $value is a string with only whitespace.
     *
     * @param mixed $value The value to be filtered.
     *
     * @test
     * @covers ::filter
     * @expectedException \DominionEnterprises\Filter\Exception
     * @expectedExceptionMessage $value not a non-empty string
     *
     * @return void
     */
    public function filterEmptyValue()
    {
        DateTimeZone::filter("\n\t");
    }

    /**
     * Verify behavior of filter() when $value is not a string.
     *
     * @test
     * @covers ::filter
     * @expectedException \DominionEnterprises\Filter\Exception
     * @expectedExceptionMessage $value not a non-empty string
     *
     * @return void
     */
    public function filterNonStringArgument()
    {
        $timezone = DateTimeZone::filter(42);
    }
}
