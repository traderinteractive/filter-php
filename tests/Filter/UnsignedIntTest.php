<?php

namespace TraderInteractive\Filter;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \TraderInteractive\Filter\UnsignedInt
 */
final class UnsignedIntTest extends TestCase
{
    /**
     * @test
     * @covers ::filter
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage -1 was not greater or equal to zero
     */
    public function filterMinValueNegative()
    {
        UnsignedInt::filter('1', false, -1);
    }

    /**
     * @test
     * @covers ::filter
     */
    public function filterMinValueNullSuccess()
    {
        $this->assertSame(1, UnsignedInt::filter('1', false, null));
    }

    /**
     * @test
     * @covers ::filter
     * @expectedException \TraderInteractive\Filter\Exception
     * @expectedExceptionMessage -1 is less than 0
     */
    public function filterMinValueNullFail()
    {
        UnsignedInt::filter('-1', false, null);
    }

    /**
     * @test
     * @covers ::filter
     */
    public function filterBasicUse()
    {
        $this->assertSame(123, UnsignedInt::filter('123'));
    }

    /**
     * @test
     * @covers ::filter
     */
    public function filterAllowNullSuccess()
    {
        $this->assertSame(null, UnsignedInt::filter(null, true));
    }

    /**
     * @test
     * @covers ::filter
     * @expectedException \TraderInteractive\Filter\Exception
     * @expectedExceptionMessage "NULL" $value is not a string
     */
    public function filterAllowNullFail()
    {
        UnsignedInt::filter(null, false);
    }

    /**
     * @test
     * @covers ::filter
     * @expectedException \TraderInteractive\Filter\Exception
     * @expectedExceptionMessage 0 is less than 1
     */
    public function filterMinValueFail()
    {
        $this->assertSame(1, UnsignedInt::filter('0', false, 1));
    }

    /**
     * @test
     * @covers ::filter
     * @expectedException \TraderInteractive\Filter\Exception
     * @expectedExceptionMessage 2 is greater than 1
     */
    public function filterMaxValueFail()
    {
        UnsignedInt::filter('2', false, 0, 1);
    }
}
