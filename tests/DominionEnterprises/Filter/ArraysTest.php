<?php
namespace DominionEnterprises\Filter;
use DominionEnterprises\Filter\Arrays as A;

final class ArraysTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @covers \DominionEnterprises\Filter\Arrays::notEmpty
     */
    public function notEmpty_pass()
    {
        $this->assertSame(array('boo'), A::notEmpty(array('boo')));
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filter\Arrays::notEmpty
     * @expectedException \Exception
     * @expectedExceptionMessage Value '1' is not an array
     */
    public function notEmpty_failNotArray()
    {
        A::notEmpty(1);
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filter\Arrays::notEmpty
     * @expectedException \Exception
     * @expectedExceptionMessage Array is empty
     */
    public function notEmpty_failEmpty()
    {
        A::notEmpty(array());
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
}
