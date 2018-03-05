<?php

namespace TraderInteractive\Filter;

use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the \TraderInteractive\Filter\DateTime class.
 *
 * @coversDefaultClass \TraderInteractive\Filter\DateTime
 */
final class DateTimeTest extends TestCase
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
        $string = '2014-02-04T11:55:00-0500';
        $dateTime = DateTime::filter($string);

        $this->assertSame(strtotime($string), $dateTime->getTimestamp());
    }

    /**
     * Verify behavior of filter() when $value is an integer.
     *
     * @test
     * @covers ::filter
     *
     * @return void
     */
    public function filterTimestamp()
    {
        $now = time();
        $dateTime = DateTime::filter("@{$now}");

        $this->assertSame($now, $dateTime->getTimestamp());
    }

    /**
     * Verify behavior of filter() when $value is a string with only whitespace.
     *
     * @test
     * @covers ::filter
     * @expectedException \TraderInteractive\Filter\Exception
     * @expectedExceptionMessage $value is not a non-empty string
     *
     * @return void
     */
    public function filterEmptyValue()
    {
        DateTime::filter("\t \n");
    }

    /**
     * Verify behavior of filter() when $value is not a string or integer.
     *
     * @test
     * @covers ::filter
     * @expectedException \TraderInteractive\Filter\Exception
     * @expectedExceptionMessage $value is not a non-empty string
     *
     * @return void
     */
    public function filterInvalidValue()
    {
        DateTime::filter(true);
    }

    /**
     * Verify behavior of filter() when null is given for $value and $allowNull is true.
     *
     * @test
     * @covers ::filter
     */
    public function filterNullAllowed()
    {
        $this->assertNull(DateTime::filter(null, true));
    }

    /**
     * Verify behavior of filter() when null is given for $value and $allowNull is true.
     *
     * @test
     * @covers ::filter
     * @expectedException \TraderInteractive\Filter\Exception
     * @expectedExceptionMessage $value is not a non-empty string
     */
    public function filterNullNotAllowed()
    {
        DateTime::filter(null, false);
    }

    /**
     * Verify behavior of filter() when $value is a \DateTime object.
     *
     * @test
     * @covers ::filter
     */
    public function filterDateTimePass()
    {
        $dateTime = new \DateTime('now');
        $this->assertSame($dateTime, DateTime::filter($dateTime));
    }

    /**
     * Verify behavior of filter() when $timezone is given.
     *
     * @test
     * @covers ::filter
     */
    public function filterWithTimeZone()
    {
        $timezone = new \DateTimeZone('Pacific/Honolulu');
        $dateTime = DateTime::filter('now', false, $timezone);
        $this->assertSame($timezone->getName(), $dateTime->getTimeZone()->getName());
        $this->assertSame(-36000, $dateTime->getOffset());
    }

    /**
     * Verify behavior of filter() when $value is an integer.
     *
     * @test
     * @covers ::filter
     */
    public function filterWithIntegerValue()
    {
        $now = time();
        $dateTime = DateTime::filter($now);
        $this->assertSame($now, $dateTime->getTimestamp());
    }

    /**
     * Verify basic behavior of format().
     *
     * @test
     * @covers ::format
     */
    public function format()
    {
        $now = new \DateTime();
        $this->assertSame($now->format('Y-m-d H:i:s'), DateTime::format($now, 'Y-m-d H:i:s'));
    }
}
