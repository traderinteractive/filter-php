<?php
namespace DominionEnterprises\Filter;

use DominionEnterprises\Filter\DateTime as D;

/**
 * Unit tests for the \DominionEnterprises\Filter\DateTime class.
 *
 * @coversDefaultClass \DominionEnterprises\Filter\DateTime
 */
final class DateTimeTest extends \PHPUnit_Framework_TestCase
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
        $dateTime = D::filter($string);

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
        $dateTime = D::filter("@{$now}");

        $this->assertSame($now, $dateTime->getTimestamp());
    }

    /**
     * Verify behavior of filter() when $value is a string with only whitespace.
     *
     * @param mixed $value The value to be filtered.
     *
     * @test
     * @covers ::filter
     * @expectedException \Exception
     * @expectedExceptionMessage $value is not a non-empty string
     *
     * @return void
     */
    public function filterEmptyValue()
    {
        D::filter("\t \n");
    }

    /**
     * Verify behavior of filter() when $value is not a string or integer.
     *
     * @param mixed $value The value to be filtered.
     *
     * @test
     * @covers ::filter
     * @expectedException \Exception
     * @expectedExceptionMessage $value is not a non-empty string
     *
     * @return void
     */
    public function filterInvalidValue()
    {
        D::filter(true);
    }

    /**
     * Verify behavior of filter() when $allowNull is not a boolean.
     *
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage $allowNull was not a boolean value
     * @covers ::filter
     */
    public function filterAllowNullNotBoolean()
    {
        D::filter('n/a', 5);
    }

    /**
     * Verify behavior of filter() when null is given for $value and $allowNull is true.
     *
     * @test
     * @covers ::filter
     */
    public function filterNullAllowed()
    {
        $this->assertNull(D::filter(null, true));
    }

    /**
     * Verify behavior of filter() when null is given for $value and $allowNull is true.
     *
     * @test
     * @covers ::filter
     * @expectedException \Exception
     * @expectedExceptionMessage $value is not a non-empty string
     */
    public function filterNullNotAllowed()
    {
        D::filter(null, false);
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
        $this->assertSame($dateTime, D::filter($dateTime));
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
        $dateTime = D::filter('now', false, $timezone);
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
        $dateTime = D::filter($now);
        $this->assertSame($now, $dateTime->getTimestamp());
    }
}
