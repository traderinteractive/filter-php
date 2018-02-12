<?php
namespace DominionEnterprises\Filter;

/**
 * @coversDefaultClass \DominionEnterprises\Filter\Floats
 */
final class FloatsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @covers ::filter
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage "1" $allowNull was not a bool
     */
    public function filterAllowNullIsNotBool()
    {
        Floats::filter('1', 1);
    }

    /**
     * @test
     * @covers ::filter
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage "'boo'" $minValue was not a float
     */
    public function filterMinValueNotFloat()
    {
        Floats::filter('1', false, 'boo');
    }

    /**
     * @test
     * @covers ::filter
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage "1" $maxValue was not a float
     */
    public function filterMaxValueNotFloat()
    {
        Floats::filter('1', false, 1.0, 1);
    }

    /**
     * @test
     * @covers ::filter
     */
    public function filterAllowNullIsTrueAndNullValue()
    {
        $this->assertNull(Floats::filter(null, true));
    }

    /**
     * @test
     * @covers ::filter
     */
    public function filterPositiveFloat()
    {
        $this->assertSame(123.0, Floats::filter(123.0));
    }

    /**
     * @test
     * @covers ::filter
     */
    public function filterNegativeFloat()
    {
        $this->assertSame(-123.0, Floats::filter(-123.0));
    }

    /**
     * @test
     * @covers ::filter
     */
    public function filterZeroFloat()
    {
        $positiveZero = + 0.0;
        $this->assertSame(0.0, Floats::filter($positiveZero));
        $this->assertSame(-0.0, Floats::filter(-0.0));
    }

    /**
     * @test
     * @covers ::filter
     */
    public function filterPositiveString()
    {
        $this->assertSame(123.0, Floats::filter('   123 '));
        $this->assertSame(123.0, Floats::filter('   +123 '));
        $this->assertSame(123.0, Floats::filter('   123.0 '));
        $this->assertSame(123.0, Floats::filter('   123E0 '));
        $this->assertSame(123.0, Floats::filter('   +123e0 '));
        $this->assertSame(123.0, Floats::filter('   +1230e-1 '));
        $this->assertSame(1230.0, Floats::filter('   +123e+1 '));
        $this->assertSame(0.0, Floats::filter('   +0 '));
        $this->assertSame(0.0, Floats::filter('   +0.0 '));
        $this->assertSame(0.0, Floats::filter('   0E0 '));
        $this->assertSame(0.0, Floats::filter('   00e-1 '));
        $this->assertSame(0.0, Floats::filter('   00e+1 '));
    }

    /**
     * @test
     * @covers ::filter
     */
    public function filterNegativeString()
    {
        $this->assertSame(-123.0, Floats::filter('   -123 '));
        $this->assertSame(-123.0, Floats::filter('   -123.0 '));
        $this->assertSame(-123.0, Floats::filter('   -123E0 '));
        $this->assertSame(-123.0, Floats::filter('   -1230E-1 '));
        $this->assertSame(-1230.0, Floats::filter('   -123e+1 '));
        $this->assertSame(-0.0, Floats::filter('   -0 '));
        $this->assertSame(-0.0, Floats::filter('   -0.0 '));
        $this->assertSame(-0.0, Floats::filter('   -0e0 '));
        $this->assertSame(-0.0, Floats::filter('   -00e-1 '));
        $this->assertSame(-0.0, Floats::filter('   -0e+1 '));
    }

    /**
     * @test
     * @covers ::filter
     * @expectedException \DominionEnterprises\Filter\Exception
     * @expectedExceptionMessage "true" $value is not a string
     */
    public function filterNonStringOrFloat()
    {
        Floats::filter(true);
    }

    /**
     * @test
     * @covers ::filter
     * @expectedException \DominionEnterprises\Filter\Exception
     * @exceptedExceptionMessage  does not pass is_numeric
     */
    public function filterEmptyString()
    {
        Floats::filter('');
    }

    /**
     * @test
     * @covers ::filter
     * @expectedException \DominionEnterprises\Filter\Exception
     * @expectedExceptionMessage  does not pass is_numeric
     */
    public function filterWhitespaceString()
    {
        Floats::filter('   ');
    }

    /**
     * @test
     * @covers ::filter
     * @expectedException \DominionEnterprises\Filter\Exception
     * @expectedExceptionMessage 123-4 does not pass is_numeric
     */
    public function filterNonDigitString()
    {
        Floats::filter('123-4');
    }

    /**
     * @test
     * @covers ::filter
     * @expectedException \DominionEnterprises\Filter\Exception
     */
    public function filterHexString()
    {
        Floats::filter('0xFF');
    }

    /**
     * @test
     * @covers ::filter
     * @expectedException \DominionEnterprises\Filter\Exception
     * @expectedExceptionMessage 1. 0 does not pass is_numeric
     */
    public function filterRogueSpaceStringAfterPeriod()
    {
        Floats::filter('1. 0');
    }

    /**
     * @test
     * @covers ::filter
     * @expectedException \DominionEnterprises\Filter\Exception
     * @expectedExceptionMessage 1 0 does not pass is_numeric
     */
    public function filterRogueSpaceStringBetweenDigits()
    {
        Floats::filter('1 0');
    }

    /**
     * @test
     * @covers ::filter
     * @expectedException \DominionEnterprises\Filter\Exception
     * @expectedExceptionMessage 1e999999999999 overflow
     */
    public function filterOverflow()
    {
        Floats::filter('1e999999999999');
    }

    /**
     * @test
     * @covers ::filter
     * @expectedException \DominionEnterprises\Filter\Exception
     * @expectedExceptionMessage -1e999999999999 overflow
     */
    public function filterUnderflow()
    {
        Floats::filter('-1e999999999999');
    }

    /**
     * @test
     * @covers ::filter
     * @expectedException \DominionEnterprises\Filter\Exception
     * @expectedExceptionMessage -1 is less than 0
     */
    public function filterLessThanMin()
    {
        Floats::filter(-1.0, false, 0.0);
    }

    /**
     * @test
     * @covers ::filter
     */
    public function filterEqualToMin()
    {
        $this->assertSame(0.0, Floats::filter(0.0, false, 0.0));
    }

    /**
     * @test
     * @covers ::filter
     * @expectedException \DominionEnterprises\Filter\Exception
     * @expectedExceptionMessage 1 is greater than 0
     */
    public function filterGreaterThanMax()
    {
        Floats::filter(1.0, false, null, 0.0);
    }

    /**
     * @test
     * @covers ::filter
     */
    public function filterEqualToMax()
    {
        $this->assertSame(0.0, Floats::filter(0.0, false, null, 0.0));
    }

    /**
     * @test
     * @covers ::filter
     */
    public function filterCastInts()
    {
        $this->assertSame(1.0, Floats::filter(1, false, null, null, true));
    }

    /**
     * @test
     * @covers ::filter
     * @expectedException \DominionEnterprises\Filter\Exception
     * @expectedExceptionMessage "1" $value is not a string
     */
    public function filterCastIntsIsFalse()
    {
        Floats::filter(1, false, null, null, false);
    }

    /**
     * @test
     * @covers ::filter
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage "1" $castInts was not a bool
     */
    public function filterCastIntsIsNotBool()
    {
        Floats::filter('1', false, null, null, 1);
    }
}
