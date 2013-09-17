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
     * @test
     * @covers \DominionEnterprises\Filter\Arrays::ofStrings
     */
    public function ofStrings_basicPass()
    {
        $this->assertSame(array('a', 'b', 'c'), A::ofStrings(array('a', 'b', 'c')));
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filter\Arrays::ofStrings
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage $allowNull was not a bool
     */
    public function ofStrings_allowNullNotBool()
    {
        A::ofStrings(array('a'), 1);
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filter\Arrays::ofStrings
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage $allowEmpty was not a bool
     */
    public function ofStrings_allowEmptyNotBool()
    {
        A::ofStrings(array('a'), true, 1);
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filter\Arrays::ofStrings
     * @expectedException \Exception
     * @expectedExceptionMessage Value at position '1' was not a string
     */
    public function ofStrings_containsNullFail()
    {
        A::ofStrings(array('a', null, 'c'));
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filter\Arrays::ofStrings
     */
    public function ofStrings_containsNullPass()
    {
        $this->assertSame(array('a', null, 'c'), A::ofStrings(array('a', null, 'c'), true));
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filter\Arrays::ofStrings
     * @expectedException \Exception
     * @expectedExceptionMessage Value at position '2' was not a string
     */
    public function ofStrings_containsNonString()
    {
        A::ofStrings(array('a', 'b', 1));
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filter\Arrays::ofStrings
     * @expectedException \Exception
     * @expectedExceptionMessage Value at position '2' was empty or whitespace
     */
    public function ofStrings_containsEmptyStringFail()
    {
        A::ofStrings(array('a', 'b', ''));
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filter\Arrays::ofStrings
     * @expectedException \Exception
     * @expectedExceptionMessage Value at position '0' was empty or whitespace
     */
    public function ofStrings_containsWhitespaceFail()
    {
        A::ofStrings(array("\n\t", 'b', 'c'));
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filter\Arrays::ofStrings
     */
    public function ofStrings_containsEmptyStringPass()
    {
        $this->assertSame(array('a', 'b', ''), A::ofStrings(array('a', 'b', ''), false, true));
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filter\Arrays::ofStrings
     */
    public function ofStrings_containsWhitespacePass()
    {
        $this->assertSame(array("\n\t", 'b', 'c'), A::ofStrings(array("\n\t", 'b', 'c'), false, true));
    }
}
