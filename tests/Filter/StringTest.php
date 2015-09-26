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
    public function filterNotString()
    {
        S::filter(1);
    }

    /**
     * @test
     * @covers ::filter
     */
    public function filterNullPass()
    {
        $this->assertSame(null, S::filter(null, true));
    }

    /**
     * @test
     * @expectedException Exception
     * @expectedExceptionMessage Value 'NULL' is not a string
     * @covers ::filter
     */
    public function filterNullFail()
    {
        S::filter(null);
    }

    /**
     * @test
     * @covers ::filter
     */
    public function filterMinLengthPass()
    {
        $this->assertSame('a', S::filter('a'));
    }

    /**
     * @test
     * @expectedException Exception
     * @covers ::filter
     */
    public function filterMinLengthFail()
    {
        S::filter('');
    }

    /**
     * @test
     * @covers ::filter
     */
    public function filterMaxLengthPass()
    {
        $this->assertSame('a', S::filter('a', false, 0, 1));
    }

    /**
     * @test
     * @expectedException Exception
     * @expectedExceptionMessage Value 'a' with length '1' is less than '0' or greater than '0'
     * @covers ::filter
     */
    public function filterMaxLengthFail()
    {
        S::filter('a', false, 0, 0);
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage $allowNull was not a boolean value
     * @covers ::filter
     */
    public function filterAllowNullNotBoolean()
    {
        S::filter('a', 5);
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage $minLength was not a positive integer value
     * @covers ::filter
     */
    public function filterMinLengthNotInteger()
    {
        S::filter('a', false, 5.2);
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage $maxLength was not a positive integer value
     * @covers ::filter
     */
    public function filterMaxLengthNotInteger()
    {
        S::filter('a', false, 1, 5.2);
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage $minLength was not a positive integer value
     * @covers ::filter
     */
    public function filterMinLengthNegative()
    {
        S::filter('a', false, -1);
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage $maxLength was not a positive integer value
     * @covers ::filter
     */
    public function filterMaxLengthNegative()
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
    public function explodeCustomDelimiter()
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
    public function explodeNonStringValue()
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
    public function explodeNonStringDelimiter()
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
    public function explodeEmptyDelimiter()
    {
        S::explode('test', '');
    }
}
