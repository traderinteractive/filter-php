<?php
namespace DominionEnterprises\Filter;
use DominionEnterprises\Filter\Arrays as A;

final class ArraysTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @covers \DominionEnterprises\Filter\Arrays::filter
     */
    public function filter_basicPass()
    {
        $this->assertSame(array('boo'), A::filter(array('boo')));
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filter\Arrays::filter
     * @expectedException \Exception
     * @expectedExceptionMessage Value '1' is not an array
     */
    public function filter_failNotArray()
    {
        A::filter(1);
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filter\Arrays::filter
     * @expectedException \Exception
     * @expectedExceptionMessage $value count of 0 is less than 1
     */
    public function filter_failEmpty()
    {
        A::filter(array());
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filter\Arrays::filter
     * @expectedException \Exception
     * @expectedExceptionMessage $value count of 1 is less than 2
     */
    public function filter_countLessThanMin()
    {
        A::filter(array(0), 2);
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filter\Arrays::filter
     * @expectedException \Exception
     * @expectedExceptionMessage $value count of 2 is greater than 1
     */
    public function filter_countGreaterThanMax()
    {
        A::filter(array(0, 1), 1, 1);
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filter\Arrays::filter
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage $minCount was not an int
     */
    public function filter_minCountNotInt()
    {
        A::filter(array(), true);
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filter\Arrays::filter
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage $maxCount was not an int
     */
    public function filter_maxCountNotInt()
    {
        A::filter(array(), 0, true);
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filter\Arrays::in
     */
    public function in_passStrict()
    {
        $this->assertSame('boo', A::in('boo', array('boo')));
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filter\Arrays::in
     * @expectedException \Exception
     * @expectedExceptionMessage Value '0' is not in array array (
     *   0 => 0
     * )
     */
    public function in_failStrict()
    {
        A::in('0', array(0));
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filter\Arrays::in
     * @expectedException \Exception
     * @expectedExceptionMessage Value 'boo' is not in array array (
     *   0 => 'foo'
     * )
     */
    public function in_failNotStrict()
    {
        A::in('boo', array('foo'), false);
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filter\Arrays::in
     */
    public function in_passNotStrict()
    {
        $this->assertSame('0', A::in('0', array(0), false));
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filter\Arrays::in
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage $strict was not a bool
     */
    public function in_strictNotBool()
    {
        A::in('boo', array(), 1);
    }

    /**
     * Validate that of works with a simple test
     *
     * @test
     * @covers \DominionEnterprises\Filter\Arrays::of
     */
    public function of()
    {
        $data = array(array('foo' => 'bar'), array('foo' => 'baz'));
        $this->assertSame($data, A::of($data, array('foo' => array(array('string')))));
    }

    /**
     * Validate that of throws an error it each of the items is not an array
     *
     * @test
     * @covers \DominionEnterprises\Filter\Arrays::of
     * @expectedException \Exception
     * @expectedExceptionMessage Item 'bar' was not an array
     */
    public function of_nonArrayItems()
    {
        $data = array('bar');
        $this->assertSame($data, A::of($data, array()));
    }

    /**
     * Validate that of throws an error if one of the items fails validation
     *
     * @test
     * @covers \DominionEnterprises\Filter\Arrays::of
     * @expectedException \Exception
     * @expectedExceptionMessage Field 'foo' with value '5.2' failed filtering
     */
    public function of_failingItem()
    {
        $data = array(array('foo' => 'bar'), array('foo' => 5.2));
        $this->assertSame($data, A::of($data, array('foo' => array(array('string')))));
    }
}
