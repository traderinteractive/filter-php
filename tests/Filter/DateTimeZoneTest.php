<?php

namespace TraderInteractive\Filter;

use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the \TraderInteractive\Filter\DateTimeZone class.
 *
 * @coversDefaultClass \TraderInteractive\Filter\DateTimeZone
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
     * @expectedException \TraderInteractive\Filter\Exception
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
     * @expectedException \TraderInteractive\Filter\Exception
     * @expectedExceptionMessage Unknown or bad timezone (INVALID)
     */
    public function filterInvalidName()
    {
        DateTimeZone::filter('INVALID');
    }

    /**
     * Verify behavior of filter() $value is a string with only whitespace.
     *
     * @test
     * @covers ::filter
     * @expectedException \TraderInteractive\Filter\Exception
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
     * @expectedException \TraderInteractive\Filter\Exception
     * @expectedExceptionMessage $value not a non-empty string
     *
     * @return void
     */
    public function filterNonStringArgument()
    {
        DateTimeZone::filter(42);
    }
}
