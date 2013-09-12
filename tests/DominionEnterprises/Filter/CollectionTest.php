<?php
namespace DominionEnterprises\Filter;
use DominionEnterprises\Filter\Collection as C;

final class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @covers \DominionEnterprises\Filter\Collection::notEmpty
     */
    public function notEmpty_pass()
    {
        $this->assertSame(array('boo'), C::notEmpty(array('boo')));
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filter\Collection::notEmpty
     * @expectedException \Exception
     * @expectedExceptionMessage Value '1' is not an array
     */
    public function notEmpty_failNotArray()
    {
        C::notEmpty(1);
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filter\Collection::notEmpty
     * @expectedException \Exception
     * @expectedExceptionMessage Array is empty
     */
    public function notEmpty_failEmpty()
    {
        C::notEmpty(array());
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filter\Collection::in
     */
    public function in_passStrict()
    {
        $this->assertSame('boo', C::in('boo', array('boo')));
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filter\Collection::in
     * @expectedException \Exception
     * @expectedExceptionMessage Value '0' is not in array array (
     *   0 => 0
     * )
     */
    public function in_failStrict()
    {
        C::in('0', array(0));
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filter\Collection::in
     * @expectedException \Exception
     * @expectedExceptionMessage Value 'boo' is not in array array (
     *   0 => 'foo'
     * )
     */
    public function in_failNotStrict()
    {
        C::in('boo', array('foo'), false);
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filter\Collection::in
     */
    public function in_passNotStrict()
    {
        $this->assertSame('0', C::in('0', array(0), false));
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filter\Collection::in
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage $strict was not a bool
     */
    public function in_strictNotBool()
    {
        C::in('boo', array(), 1);
    }
}
