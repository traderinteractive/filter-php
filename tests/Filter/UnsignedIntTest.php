<?php
namespace DominionEnterprises\Filter;
use DominionEnterprises\Filter\UnsignedInt as I;

/**
 * @coversDefaultClass \DominionEnterprises\Filter\UnsignedInt
 */
final class UnsignedIntTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @covers ::filter
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage -1 was not greater or equal to zero
     */
    public function filter_minValueNegative()
    {
        I::filter('1', false, -1);
    }

    /**
     * @test
     * @covers ::filter
     */
    public function filter_minValueNullSuccess()
    {
        $this->assertSame(1, I::filter('1', false, null));
    }

    /**
     * @test
     * @covers ::filter
     * @expectedException \Exception
     * @expectedExceptionMessage -1 is less than 0
     */
    public function filter_minValueNullFail()
    {
        I::filter('-1', false, null);
    }

    /**
     * @test
     * @covers ::filter
     */
    public function filter_basicUse()
    {
        $this->assertSame(123, I::filter('123'));
    }

    /**
     * @test
     * @covers ::filter
     */
    public function filter_allowNullSuccess()
    {
        $this->assertSame(null, I::filter(null, true));
    }

    /**
     * @test
     * @covers ::filter
     * @expectedException \Exception
     * @expectedExceptionMessage "NULL" $value is not a string
     */
    public function filter_allowNullFail()
    {
        I::filter(null, false);
    }

    /**
     * @test
     * @covers ::filter
     * @expectedException \Exception
     * @expectedExceptionMessage 0 is less than 1
     */
    public function filter_minValueFail()
    {
        $this->assertSame(1, I::filter('0', false, 1));
    }

    /**
     * @test
     * @covers ::filter
     * @expectedException \Exception
     * @expectedExceptionMessage 2 is greater than 1
     */
    public function filter_maxValueFail()
    {
        I::filter('2', false, 0, 1);
    }
}
