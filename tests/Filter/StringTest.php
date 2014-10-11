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

    /**
     * Verify basic behvaior of concat().
     *
     * @test
     * @covers ::concat
     *
     * @return void
     */
    public function concat()
    {
        $this->assertSame('prefixstringsuffix', S::concat('string', false, 'prefix', 'suffix'));
    }

    /**
     * Verify behavior of concat() when $allowNull is not a boolean.
     *
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage $allowNull was not a boolean value
     * @covers ::concat
     */
    public function concat_allowNullNotBoolean()
    {
        S::concat('n/a', 5, 'prefix', 'suffix');
    }

    /**
     * Verify behavior of concat() when null is given for $value and $allowNull is true.
     *
     * @test
     * @covers ::concat
     */
    public function concat_nullAllowed()
    {
        $this->assertSame('prefixsuffix', S::concat(null, true, 'prefix', 'suffix'));
    }

    /**
     * Verify behavior of concat() when null is given for $value and $allowNull is true.
     *
     * @test
     * @covers ::concat
     * @expectedException \Exception
     * @expectedExceptionMessage $value was not filterable as a string
     */
    public function concat_nullNotAllowed()
    {
        S::concat(null, false, 'prefix', 'suffix');
    }

    /**
     * Verify behavior of concat() when $prefix is not a string.
     *
     * @test
     * @covers ::concat
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage $prefix was not a string
     *
     * @return void
     */
    public function concat_nonStringPrefix()
    {
        S::concat('string', false, true, 'suffix');
    }

    /**
     * Verify behavior of concat() when $suffix is not a string.
     *
     * @test
     * @covers ::concat
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage $suffix was not a string
     *
     * @return void
     */
    public function concat_nonStringSuffix()
    {
        S::concat('string', false, 'prefix', 0.00);
    }

    /**
     * Verify behavior of concat() when $value is a non-string scalar.
     *
     * @test
     * @covers ::concat
     *
     * @return void
     */
    public function concat_scalarValue()
    {
        $this->assertSame('prefix123suffix', S::concat(123, false, 'prefix', 'suffix'));
    }

    /**
     * Verify behavior of concat() when $value is an object that implements __toString().
     *
     * @test
     * @covers ::concat
     *
     * @return void
     */
    public function concat_objectValue()
    {
        $this->assertSame('prefix' . __FILE__ . 'suffix', S::concat(new \SplFileInfo(__FILE__), false, 'prefix', 'suffix'));
    }
}
