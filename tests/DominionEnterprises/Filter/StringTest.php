<?php
namespace DominionEnterprises\Filter;
use DominionEnterprises\Filter\String as S;

final class StringUtilTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @covers \DominionEnterprises\Filter\String::filter
     */
    public function filter_castScalars()
    {
        $this->assertSame('1', S::filter(1, false, 1, PHP_INT_MAX, true));
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage $castScalars was not a boolean value
     * @covers \DominionEnterprises\Filter\String::filter
     */
    public function filter_castScalarsNotBoolean()
    {
        S::filter('a', false, 1, PHP_INT_MAX, 'string');
    }

    /**
     * @test
     * @expectedException Exception
     * @expectedExceptionMessage Value '1' is not a string
     * @covers \DominionEnterprises\Filter\String::filter
     */
    public function filter_notString()
    {
        S::filter(1);
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filter\String::filter
     */
    public function filter_nullPass()
    {
        $this->assertSame(null, S::filter(null, true));
    }

    /**
     * @test
     * @expectedException Exception
     * @expectedExceptionMessage Value 'NULL' is not a string
     * @covers \DominionEnterprises\Filter\String::filter
     */
    public function filter_nullFail()
    {
        S::filter(null);
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filter\String::filter
     */
    public function filter_minLengthPass()
    {
        $this->assertSame('a', S::filter('a'));
    }

    /**
     * @test
     * @expectedException Exception
     * @covers \DominionEnterprises\Filter\String::filter
     */
    public function filter_minLengthFail()
    {
        S::filter('');
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filter\String::filter
     */
    public function filter_maxLengthPass()
    {
        $this->assertSame('a', S::filter('a', false, 0, 1));
    }

    /**
     * @test
     * @expectedException Exception
     * @expectedExceptionMessage Value 'a' with length '1' is less than '0' or greater than '0'
     * @covers \DominionEnterprises\Filter\String::filter
     */
    public function filter_maxLengthFail()
    {
        S::filter('a', false, 0, 0);
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage $allowNull was not a boolean value
     * @covers \DominionEnterprises\Filter\String::filter
     */
    public function filter_allowNullNotBoolean()
    {
        S::filter('a', 5);
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage $minLength was not a positive integer value
     * @covers \DominionEnterprises\Filter\String::filter
     */
    public function filter_minLengthNotInteger()
    {
        S::filter('a', false, 5.2);
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage $maxLength was not a positive integer value
     * @covers \DominionEnterprises\Filter\String::filter
     */
    public function filter_maxLengthNotInteger()
    {
        S::filter('a', false, 1, 5.2);
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage $minLength was not a positive integer value
     * @covers \DominionEnterprises\Filter\String::filter
     */
    public function filter_minLengthNegative()
    {
        S::filter('a', false, -1);
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage $maxLength was not a positive integer value
     * @covers \DominionEnterprises\Filter\String::filter
     */
    public function filter_maxLengthNegative()
    {
        S::filter('a', false, 1, -1);
    }
}
