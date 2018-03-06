<?php

namespace TraderInteractive\Filter;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \TraderInteractive\Filter\Arrays
 */
final class ArraysTest extends TestCase
{
    /**
     * @test
     * @covers ::filter
     */
    public function filterBasicPass()
    {
        $this->assertSame(['boo'], Arrays::filter(['boo']));
    }

    /**
     * @test
     * @covers ::filter
     * @expectedException \TraderInteractive\Filter\Exception
     * @expectedExceptionMessage Value '1' is not an array
     */
    public function filterFailNotArray()
    {
        Arrays::filter(1);
    }

    /**
     * @test
     * @covers ::filter
     * @expectedException \TraderInteractive\Filter\Exception
     * @expectedExceptionMessage $value count of 0 is less than 1
     */
    public function filterFailEmpty()
    {
        Arrays::filter([]);
    }

    /**
     * @test
     * @covers ::filter
     * @expectedException \TraderInteractive\Filter\Exception
     * @expectedExceptionMessage $value count of 1 is less than 2
     */
    public function filterCountLessThanMin()
    {
        Arrays::filter([0], 2);
    }

    /**
     * @test
     * @covers ::filter
     * @expectedException \TraderInteractive\Filter\Exception
     * @expectedExceptionMessage $value count of 2 is greater than 1
     */
    public function filterCountGreaterThanMax()
    {
        Arrays::filter([0, 1], 1, 1);
    }

    /**
     * @test
     * @covers ::in
     */
    public function inPassStrict()
    {
        $this->assertSame('boo', Arrays::in('boo', ['boo']));
    }

    /**
     * @test
     * @covers ::in
     */
    public function inFailStrict()
    {
        try {
            Arrays::in('0', [0]);
            $this->fail();
        } catch (Exception $e) {
            $this->assertSame("Value '0' is not in array array (\n  0 => 0,\n)", $e->getMessage());
        }
    }

    /**
     * @test
     * @covers ::in
     */
    public function inFailNotStrict()
    {
        try {
            Arrays::in('boo', ['foo'], false);
            $this->fail();
        } catch (Exception $e) {
            $this->assertSame("Value 'boo' is not in array array (\n  0 => 'foo',\n)", $e->getMessage());
        }
    }

    /**
     * @test
     * @covers ::in
     */
    public function inPassNotStrict()
    {
        $this->assertSame('0', Arrays::in('0', [0], false));
    }

    /**
     * Verifies the basic behavior of the flatten filter.
     *
     * @test
     * @covers ::flatten
     */
    public function flatten()
    {
        $this->assertSame([1, 2, 3, 4, 5], Arrays::flatten([[1, 2], [[3, [4, 5]]]]));
    }
}
