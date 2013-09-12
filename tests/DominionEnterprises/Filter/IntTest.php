<?php
namespace DominionEnterprises\Filter;
use DominionEnterprises\Filter\Int as S;

final class IntTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @covers \DominionEnterprises\Filter\Int::filter
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage "1" $allowNull was not a bool
     */
    public function filter_allowNullIsNotBool()
    {
        S::filter('1', 1);
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filter\Int::filter
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage "'boo'" $minValue was not an int
     */
    public function filter_minValueNotInt()
    {
        S::filter('1', false, 'boo');
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filter\Int::filter
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage "1.1" $maxValue was not an int
     */
    public function filter_maxValueNotInt()
    {
        S::filter('1', false, 1, 1.1);
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filter\Int::filter
     */
    public function filter_allowNullIsTrueAndNullValue()
    {
        $result = S::filter(null, true);
        $this->assertSame(null, $result);
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filter\Int::filter
     */
    public function filter_positiveInt()
    {
        $this->assertSame(123, S::filter(123));
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filter\Int::filter
     */
    public function filter_negativeInt()
    {
        $this->assertSame(-123, S::filter(-123));
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filter\Int::filter
     */
    public function filter_zeroInt()
    {
        $positiveZero = + 0;
        $this->assertSame(0, S::filter($positiveZero));
        $this->assertSame(0, S::filter(-0));
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filter\Int::filter
     */
    public function filter_positiveString()
    {
        $this->assertSame(123, S::filter('   123 '));
        $this->assertSame(123, S::filter('   +123 '));
        $this->assertSame(0, S::filter('   +0 '));
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filter\Int::filter
     */
    public function filter_negativeString()
    {
        $this->assertSame(-123, S::filter('   -123 '));
        $this->assertSame(0, S::filter('   -0 '));
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filter\Int::filter
     * @expectedException \Exception
     * @expectedExceptionMessage "true" $value is not a string
     */
    public function filter_nonStringOrInt()
    {
        S::filter(true);
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filter\Int::filter
     * @expectedException \Exception
     * @expectedExceptionMessage $value string length is zero
     */
    public function filter_emptyString()
    {
        S::filter('');
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filter\Int::filter
     * @expectedException \Exception
     * @expectedExceptionMessage $value string length is zero
     */
    public function filter_whitespaceString()
    {
        S::filter('   ');
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filter\Int::filter
     * @expectedException \Exception
     * @expectedExceptionMessage 123.4 does not contain all digits, optionally prepended by a '+' or '-' and optionally surrounded by whitespace
     */
    public function nonDigitString()
    {
        S::filter('123.4');
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filter\Int::filter
     * @expectedException \Exception
     */
    public function filter_greaterThanPhpIntMax()
    {
        //32, 64 and 128 bit and their +1 's
        $maxes = array(
            '2147483647' => '2147483648',
            '9223372036854775807' => '9223372036854775808',
            '170141183460469231731687303715884105727' => '170141183460469231731687303715884105728',
        );
        $oneOverMax = $maxes[(string)PHP_INT_MAX];
        S::filter($oneOverMax);
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filter\Int::filter
     * @expectedException \Exception
     */
    public function filter_lessThanPhpIntMin()
    {
        //32, 64 and 128 bit and their -1 's
        $mins = array(
            '-2147483648' => '-2147483649',
            '-9223372036854775808' => '-9223372036854775809',
            '-170141183460469231731687303715884105728' => '-170141183460469231731687303715884105729',
        );
        $oneUnderMin = $mins[(string)~PHP_INT_MAX];
        S::filter($oneUnderMin);
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filter\Int::filter
     * @expectedException \Exception
     * @expectedExceptionMessage -1 is less than 0
     */
    public function filter_lessThanMin()
    {
        S::filter(-1, false, 0);
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filter\Int::filter
     */
    public function filter_equalToMin()
    {
        $this->assertSame(0, S::filter(0, false, 0));
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filter\Int::filter
     * @expectedException \Exception
     * @expectedExceptionMessage 1 is greater than 0
     */
    public function filter_greaterThanMax()
    {
        S::filter(1, false, null, 0);
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filter\Int::filter
     */
    public function filter_equalToMax()
    {
        $this->assertSame(0, S::filter(0, false, null, 0));
    }
}
