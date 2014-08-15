<?php
namespace DominionEnterprises\Filter;
use DominionEnterprises\Filter\String as S;

/**
 * @coversDefaultClass \DominionEnterprises\Filter\String
 */
final class StringUtilTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @expectedException Exception
     * @expectedExceptionMessage Value '1' is not a string
     * @covers ::filter
     */
    public function filter_notString()
    {
        S::filter(1);
    }

    /**
     * @test
     * @covers ::filter
     */
    public function filter_nullPass()
    {
        $this->assertSame(null, S::filter(null, true));
    }

    /**
     * @test
     * @expectedException Exception
     * @expectedExceptionMessage Value 'NULL' is not a string
     * @covers ::filter
     */
    public function filter_nullFail()
    {
        S::filter(null);
    }

    /**
     * @test
     * @covers ::filter
     */
    public function filter_minLengthPass()
    {
        $this->assertSame('a', S::filter('a'));
    }

    /**
     * @test
     * @expectedException Exception
     * @covers ::filter
     */
    public function filter_minLengthFail()
    {
        S::filter('');
    }

    /**
     * @test
     * @covers ::filter
     */
    public function filter_maxLengthPass()
    {
        $this->assertSame('a', S::filter('a', false, 0, 1));
    }

    /**
     * @test
     * @expectedException Exception
     * @expectedExceptionMessage Value 'a' with length '1' is less than '0' or greater than '0'
     * @covers ::filter
     */
    public function filter_maxLengthFail()
    {
        S::filter('a', false, 0, 0);
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage $allowNull was not a boolean value
     * @covers ::filter
     */
    public function filter_allowNullNotBoolean()
    {
        S::filter('a', 5);
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage $minLength was not a positive integer value
     * @covers ::filter
     */
    public function filter_minLengthNotInteger()
    {
        S::filter('a', false, 5.2);
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage $maxLength was not a positive integer value
     * @covers ::filter
     */
    public function filter_maxLengthNotInteger()
    {
        S::filter('a', false, 1, 5.2);
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage $minLength was not a positive integer value
     * @covers ::filter
     */
    public function filter_minLengthNegative()
    {
        S::filter('a', false, -1);
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage $maxLength was not a positive integer value
     * @covers ::filter
     */
    public function filter_maxLengthNegative()
    {
        S::filter('a', false, 1, -1);
    }

    /**
     * Verifies basic explode functionality.
     *
     * @test
     * @covers ::explode
     */
    public function explode()
    {
        $this->assertSame(['a', 'bcd', 'e'], S::explode('a,bcd,e'));
    }

    /**
     * Verifies explode with a custom delimiter.
     *
     * @test
     * @covers ::explode
     */
    public function explode_customDelimiter()
    {
        $this->assertSame(['a', 'b', 'c', 'd,e'], S::explode('a b c d,e', ' '));
    }

    /**
     * Verifies explode filter with a non-string value.
     *
     * @test
     * @expectedException Exception
     * @expectedExceptionMessage Value 'true' is not a string
     * @covers ::explode
     */
    public function explode_nonStringValue()
    {
        S::explode(true);
    }

    /**
     * Verifies explode filter with a non-string delimiter.
     *
     * @test
     * @expectedException Exception
     * @expectedExceptionMessage Delimiter '4' is not a non-empty string
     * @covers ::explode
     */
    public function explode_nonStringDelimiter()
    {
        S::explode('test', 4);
    }

    /**
     * Verifies explode filter with an empty delimiter.
     *
     * @test
     * @expectedException Exception
     * @expectedExceptionMessage Delimiter '''' is not a non-empty string
     * @covers ::explode
     */
    public function explode_emptyDelimiter()
    {
        S::explode('test', '');
    }
}
