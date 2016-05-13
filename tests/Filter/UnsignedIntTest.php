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
    public function filterMinValueNegative()
    {
        I::filter('1', false, -1);
    }

    /**
     * @test
     * @covers ::filter
     */
    public function filterMinValueNullSuccess()
    {
        $this->assertSame(1, I::filter('1', false, null));
    }

    /**
     * @test
     * @covers ::filter
     * @expectedException \Exception
     * @expectedExceptionMessage -1 is less than 0
     */
    public function filterMinValueNullFail()
    {
        I::filter('-1', false, null);
    }

    /**
     * @test
     * @covers ::filter
     */
    public function filterBasicUse()
    {
        $this->assertSame(123, I::filter('123'));
    }

    /**
     * @test
     * @covers ::filter
     */
    public function filterAllowNullSuccess()
    {
        $this->assertSame(null, I::filter(null, true));
    }

    /**
     * @test
     * @covers ::filter
     * @expectedException \Exception
     * @expectedExceptionMessage "NULL" $value is not a string
     */
    public function filterAllowNullFail()
    {
        I::filter(null, false);
    }

    /**
     * @test
     * @covers ::filter
     * @expectedException \Exception
     * @expectedExceptionMessage 0 is less than 1
     */
    public function filterMinValueFail()
    {
        $this->assertSame(1, I::filter('0', false, 1));
    }

    /**
     * @test
     * @covers ::filter
     * @expectedException \Exception
     * @expectedExceptionMessage 2 is greater than 1
     */
    public function filterMaxValueFail()
    {
        I::filter('2', false, 0, 1);
    }
}
