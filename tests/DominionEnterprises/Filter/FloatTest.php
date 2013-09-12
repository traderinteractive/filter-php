<?php
namespace DominionEnterprises\Filter;
use DominionEnterprises\Filter\Float as F;

final class FloatTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage "1" $allowNull was not a bool
     */
    public function filter_allowNullIsNotBool()
    {
        F::filter('1', 1);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage "'boo'" $minValue was not a float
     */
    public function filter_minValueNotFloat()
    {
        F::filter('1', false, 'boo');
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage "1" $maxValue was not a float
     */
    public function filter_maxValueNotFloat()
    {
        F::filter('1', false, 1.0, 1);
    }

    /**
     * @test
     */
    public function filter_allowNullIsTrueAndNullValue()
    {
        $this->assertNull(F::filter(null, true));
    }

    /**
     * @test
     */
    public function filter_positiveFloat()
    {
        $this->assertSame(123.0, F::filter(123.0));
    }

    /**
     * @test
     */
    public function filter_negativeFloat()
    {
        $this->assertSame(-123.0, F::filter(-123.0));
    }

    /**
     * @test
     */
    public function filter_zeroFloat()
    {
        $positiveZero = + 0.0;
        $this->assertSame(0.0, F::filter($positiveZero));
        $this->assertSame(-0.0, F::filter(-0.0));
    }

    /**
     * @test
     */
    public function filter_positiveString()
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
     */
    public function filter_negativeString()
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
     * @expectedException \Exception
     * @expectedExceptionMessage "true" $value is not a string
     */
    public function filter_nonStringOrFloat()
    {
        F::filter(true);
    }

    /**
     * @test
     * @expectedException \Exception
     * @exceptedExceptionMessage  does not pass is_numeric
     */
    public function filter_emptyString()
    {
        F::filter('');
    }

    /**
     * @test
     * @expectedException \Exception
     * @expectedExceptionMessage  does not pass is_numeric
     */
    public function filter_whitespaceString()
    {
        F::filter('   ');
    }

    /**
     * @test
     * @expectedException \Exception
     * @expectedExceptionMessage 123-4 does not pass is_numeric
     */
    public function filter_nonDigitString()
    {
        F::filter('123-4');
    }

    /**
     * @test
     * @expectedException \Exception
     * @expectedExceptionMessage 0xff is hex format
     */
    public function filter_hexString()
    {
        F::filter('0xFF');
    }

    /**
     * @test
     * @expectedException \Exception
     * @expectedExceptionMessage 1. 0 does not pass is_numeric
     */
    public function filter_rogueSpaceStringAfterPeriod()
    {
        F::filter('1. 0');
    }

    /**
     * @test
     * @expectedException \Exception
     * @expectedExceptionMessage 1 0 does not pass is_numeric
     */
    public function filter_rogueSpaceStringBetweenDigits()
    {
        F::filter('1 0');
    }

    /**
     * @test
     * @expectedException \Exception
     * @expectedExceptionMessage 1e999999999999 overflow
     */
    public function filter_overflow()
    {
        F::filter('1e999999999999');
    }

    /**
     * @test
     * @expectedException \Exception
     * @expectedExceptionMessage -1e999999999999 overflow
     */
    public function filter_underflow()
    {
        F::filter('-1e999999999999');
    }

    /**
     * @test
     * @expectedException \Exception
     * @expectedExceptionMessage -1 is less than 0
     */
    public function filter_lessThanMin()
    {
        F::filter(-1.0, false, 0.0);
    }

    /**
     * @test
     */
    public function filter_equalToMin()
    {
        $this->assertSame(0.0, F::filter(0.0, false, 0.0));
    }

    /**
     * @test
     * @expectedException \Exception
     * @expectedExceptionMessage 1 is greater than 0
     */
    public function filter_greaterThanMax()
    {
        F::filter(1.0, false, null, 0.0);
    }

    /**
     * @test
     */
    public function filter_equalToMax()
    {
        $this->assertSame(0.0, F::filter(0.0, false, null, 0.0));
    }
}
