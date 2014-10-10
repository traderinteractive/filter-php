<?php
namespace DominionEnterprises\Filter;
use DominionEnterprises\Filter\DateTimeZone as TZ;

/**
 * Unit tests for the \DominionEnterprises\Filter\DateTimeZone class.
 *
 * @coversDefaultClass \DominionEnterprises\Filter\DateTimeZone
 */
final class DateTimeZoneTest extends \PHPUnit_Framework_TestCase
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
        $timezone = TZ::filter($value);
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
    public function filter_allowNullNotBoolean()
    {
        TZ::filter('America/New_York', 5);
    }

    /**
     * Verify behavior of filter() when $allowNull is true and $value is null.
     *
     * @test
     * @covers ::filter
     *
     * @return void
     */
    public function filter_nullAllowed()
    {
        $this->assertNull(TZ::filter(null, true));
    }

    /**
     * Verify behavior of filter() when $allowNull is false and $value is null.
     *
     * @test
     * @covers ::filter
     * @expectedException \Exception
     * @expectedExceptionMessage $value not a non-empty string
     *
     * @return void
     */
    public function filter_nullNotAllowed()
    {
        $this->assertNull(TZ::filter(null, false));
    }

    /**
     * Verify behavior of filter() when $value is a \DateTimeZone object.
     *
     * @test
     * @covers ::filter
     *
     * @return void
     */
    public function filter_timeZonePass()
    {
        $timezone = new \DateTimeZone('America/New_York');
        $this->assertSame($timezone, TZ::filter($timezone));
    }

    /**
     * Verify behavior of filter() when $value is not a valid timezone.
     *
     * @test
     * @covers ::filter
     * @expectedException \Exception
     * @expectedExceptionMessage Unknown or bad timezone (INVALID)
     */
    public function filter_invalidName()
    {
        $timezone = TZ::filter('INVALID');
    }

    /**
     * Verify behavior of filter() $value is a string with only whitespace.
     *
     * @param mixed $value The value to be filtered.
     *
     * @test
     * @covers ::filter
     * @expectedException \Exception
     * @expectedExceptionMessage $value not a non-empty string
     *
     * @return void
     */
    public function filter_emptyValue()
    {
        TZ::filter("\n\t");
    }

    /**
     * Verify behavior of filter() when $value is not a string.
     *
     * @test
     * @covers ::filter
     * @expectedException \Exception
     * @expectedExceptionMessage $value not a non-empty string
     *
     * @return void
     */
    public function filter_nonStringArgument()
    {
        $timezone = TZ::filter(42);
    }
}
