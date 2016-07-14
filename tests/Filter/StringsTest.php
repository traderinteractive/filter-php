<?php
namespace DominionEnterprises\Filter;

/**
 * @coversDefaultClass \DominionEnterprises\Filter\Strings
 */
final class StringsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @expectedException Exception
     * @expectedExceptionMessage Value '1' is not a string
     * @covers ::filter
     */
    public function filterNotString()
    {
        Strings::filter(1);
    }

    /**
     * @test
     * @covers ::filter
     */
    public function filterNullPass()
    {
        $this->assertSame(null, Strings::filter(null, true));
    }

    /**
     * @test
     * @expectedException Exception
     * @expectedExceptionMessage Value 'NULL' is not a string
     * @covers ::filter
     */
    public function filterNullFail()
    {
        Strings::filter(null);
    }

    /**
     * @test
     * @covers ::filter
     */
    public function filterMinLengthPass()
    {
        $this->assertSame('a', Strings::filter('a'));
    }

    /**
     * @test
     * @expectedException Exception
     * @covers ::filter
     */
    public function filterMinLengthFail()
    {
        Strings::filter('');
    }

    /**
     * @test
     * @covers ::filter
     */
    public function filterMaxLengthPass()
    {
        $this->assertSame('a', Strings::filter('a', false, 0, 1));
    }

    /**
     * @test
     * @expectedException Exception
     * @expectedExceptionMessage Value 'a' with length '1' is less than '0' or greater than '0'
     * @covers ::filter
     */
    public function filterMaxLengthFail()
    {
        Strings::filter('a', false, 0, 0);
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage $allowNull was not a boolean value
     * @covers ::filter
     */
    public function filterAllowNullNotBoolean()
    {
        Strings::filter('a', 5);
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage $minLength was not a positive integer value
     * @covers ::filter
     */
    public function filterMinLengthNotInteger()
    {
        Strings::filter('a', false, 5.2);
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage $maxLength was not a positive integer value
     * @covers ::filter
     */
    public function filterMaxLengthNotInteger()
    {
        Strings::filter('a', false, 1, 5.2);
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage $minLength was not a positive integer value
     * @covers ::filter
     */
    public function filterMinLengthNegative()
    {
        Strings::filter('a', false, -1);
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage $maxLength was not a positive integer value
     * @covers ::filter
     */
    public function filterMaxLengthNegative()
    {
        Strings::filter('a', false, 1, -1);
    }

    /**
     * Verifies basic explode functionality.
     *
     * @test
     * @covers ::explode
     */
    public function explode()
    {
        $this->assertSame(['a', 'bcd', 'e'], Strings::explode('a,bcd,e'));
    }

    /**
     * Verifies explode with a custom delimiter.
     *
     * @test
     * @covers ::explode
     */
    public function explodeCustomDelimiter()
    {
        $this->assertSame(['a', 'b', 'c', 'd,e'], Strings::explode('a b c d,e', ' '));
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
        Strings::explode(true);
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
        Strings::explode('test', 4);
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
        Strings::explode('test', '');
    }
}
