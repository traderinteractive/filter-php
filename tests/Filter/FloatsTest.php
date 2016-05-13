<?php
namespace DominionEnterprises\Filter;

use DominionEnterprises\Filter\Floats as F;

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
        F::filter('1', 1);
    }

    /**
     * @test
     * @covers ::filter
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage "'boo'" $minValue was not a float
     */
    public function filterMinValueNotFloat()
    {
        F::filter('1', false, 'boo');
    }

    /**
     * @test
     * @covers ::filter
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage "1" $maxValue was not a float
     */
    public function filterMaxValueNotFloat()
    {
        F::filter('1', false, 1.0, 1);
    }

    /**
     * @test
     * @covers ::filter
     */
    public function filterAllowNullIsTrueAndNullValue()
    {
        $this->assertNull(F::filter(null, true));
    }

    /**
     * @test
     * @covers ::filter
     */
    public function filterPositiveFloat()
    {
        $this->assertSame(123.0, F::filter(123.0));
    }

    /**
     * @test
     * @covers ::filter
     */
    public function filterNegativeFloat()
    {
        $this->assertSame(-123.0, F::filter(-123.0));
    }

    /**
     * @test
     * @covers ::filter
     */
    public function filterZeroFloat()
    {
        $positiveZero = + 0.0;
        $this->assertSame(0.0, F::filter($positiveZero));
        $this->assertSame(-0.0, F::filter(-0.0));
    }

    /**
     * @test
     * @covers ::filter
     */
    public function filterPositiveString()
    {
        $this->assertSame(123.0, F::filter('   123 '));
        $this->assertSame(123.0, F::filter('   +123 '));
        $this->assertSame(123.0, F::filter('   123.0 '));
        $this->assertSame(123.0, F::filter('   123E0 '));
        $this->assertSame(123.0, F::filter('   +123e0 '));
        $this->assertSame(123.0, F::filter('   +1230e-1 '));
        $this->assertSame(1230.0, F::filter('   +123e+1 '));
        $this->assertSame(0.0, F::filter('   +0 '));
        $this->assertSame(0.0, F::filter('   +0.0 '));
        $this->assertSame(0.0, F::filter('   0E0 '));
        $this->assertSame(0.0, F::filter('   00e-1 '));
        $this->assertSame(0.0, F::filter('   00e+1 '));
    }

    /**
     * @test
     * @covers ::filter
     */
    public function filterNegativeString()
    {
        $this->assertSame(-123.0, F::filter('   -123 '));
        $this->assertSame(-123.0, F::filter('   -123.0 '));
        $this->assertSame(-123.0, F::filter('   -123E0 '));
        $this->assertSame(-123.0, F::filter('   -1230E-1 '));
        $this->assertSame(-1230.0, F::filter('   -123e+1 '));
        $this->assertSame(-0.0, F::filter('   -0 '));
        $this->assertSame(-0.0, F::filter('   -0.0 '));
        $this->assertSame(-0.0, F::filter('   -0e0 '));
        $this->assertSame(-0.0, F::filter('   -00e-1 '));
        $this->assertSame(-0.0, F::filter('   -0e+1 '));
    }

    /**
     * @test
     * @covers ::filter
     * @expectedException \Exception
     * @expectedExceptionMessage "true" $value is not a string
     */
    public function filterNonStringOrFloat()
    {
        F::filter(true);
    }

    /**
     * @test
     * @covers ::filter
     * @expectedException \Exception
     * @exceptedExceptionMessage  does not pass is_numeric
     */
    public function filterEmptyString()
    {
        F::filter('');
    }

    /**
     * @test
     * @covers ::filter
     * @expectedException \Exception
     * @expectedExceptionMessage  does not pass is_numeric
     */
    public function filterWhitespaceString()
    {
        F::filter('   ');
    }

    /**
     * @test
     * @covers ::filter
     * @expectedException \Exception
     * @expectedExceptionMessage 123-4 does not pass is_numeric
     */
    public function filterNonDigitString()
    {
        F::filter('123-4');
    }

    /**
     * @test
     * @covers ::filter
     * @expectedException \Exception
     * @expectedExceptionMessage 0xff is hex format
     */
    public function filterHexString()
    {
        F::filter('0xFF');
    }

    /**
     * @test
     * @covers ::filter
     * @expectedException \Exception
     * @expectedExceptionMessage 1. 0 does not pass is_numeric
     */
    public function filterRogueSpaceStringAfterPeriod()
    {
        F::filter('1. 0');
    }

    /**
     * @test
     * @covers ::filter
     * @expectedException \Exception
     * @expectedExceptionMessage 1 0 does not pass is_numeric
     */
    public function filterRogueSpaceStringBetweenDigits()
    {
        F::filter('1 0');
    }

    /**
     * @test
     * @covers ::filter
     * @expectedException \Exception
     * @expectedExceptionMessage 1e999999999999 overflow
     */
    public function filterOverflow()
    {
        F::filter('1e999999999999');
    }

    /**
     * @test
     * @covers ::filter
     * @expectedException \Exception
     * @expectedExceptionMessage -1e999999999999 overflow
     */
    public function filterUnderflow()
    {
        F::filter('-1e999999999999');
    }

    /**
     * @test
     * @covers ::filter
     * @expectedException \Exception
     * @expectedExceptionMessage -1 is less than 0
     */
    public function filterLessThanMin()
    {
        F::filter(-1.0, false, 0.0);
    }

    /**
     * @test
     * @covers ::filter
     */
    public function filterEqualToMin()
    {
        $this->assertSame(0.0, F::filter(0.0, false, 0.0));
    }

    /**
     * @test
     * @covers ::filter
     * @expectedException \Exception
     * @expectedExceptionMessage 1 is greater than 0
     */
    public function filterGreaterThanMax()
    {
        F::filter(1.0, false, null, 0.0);
    }

    /**
     * @test
     * @covers ::filter
     */
    public function filterEqualToMax()
    {
        $this->assertSame(0.0, F::filter(0.0, false, null, 0.0));
    }

    /**
     * @test
     * @covers ::filter
     */
    public function filterCastInts()
    {
        $this->assertSame(1.0, F::filter(1, false, null, null, true));
    }

    /**
     * @test
     * @covers ::filter
     * @expectedException \Exception
     * @expectedExceptionMessage "1" $value is not a string
     */
    public function filterCastIntsIsFalse()
    {
        F::filter(1, false, null, null, false);
    }

    /**
     * @test
     * @covers ::filter
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage "1" $castInts was not a bool
     */
    public function filterCastIntsIsNotBool()
    {
        F::filter('1', false, null, null, 1);
    }
}
