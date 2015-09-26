<?php
namespace DominionEnterprises\Filter;

use DominionEnterprises\Filter\Bool as B;

/**
 * @coversDefaultClass \DominionEnterprises\Filter\Bool
 */
final class BoolTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @covers ::filter
     */
    public function filterBasic()
    {
        $this->assertTrue(B::filter(true));
        $this->assertTrue(B::filter('   true'));
        $this->assertTrue(B::filter(' TRUE '));
        $this->assertTrue(B::filter('True '));

        $this->assertFalse(B::filter('false   '));
        $this->assertFalse(B::filter('FALSE  '));
        $this->assertFalse(B::filter(' False '));
        $this->assertFalse(B::filter(false));
    }

    /**
     * @test
     * @covers ::filter
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage $allowNull was not a bool
     */
    public function filterAllowNullIsNotBool()
    {
        B::filter('true', 1);
    }

    /**
     * @test
     * @covers ::filter
     */
    public function filterAllowNullIsTrueAndNullValue()
    {
        $this->assertNull(B::filter(null, true));
    }

    /**
     * @test
     * @covers ::filter
     * @expectedException \Exception
     * @expectedExceptionMessage "1" $value is not a string
     */
    public function filterNonStringAndNonBoolValue()
    {
        B::filter(1);
    }

    /**
     * @test
     * @covers ::filter
     * @expectedException \Exception
     * @expectedExceptionMessage invalid is not 'true' or 'false' disregarding case and whitespace
     */
    public function filterInvalidString()
    {
        B::filter('invalid');
    }

    /**
     * @test
     * @covers ::filter
     */
    public function filterCustomTrueValues()
    {
        $this->assertTrue(B::filter('Y', false, ['y']));
    }

    /**
     * @test
     * @covers ::filter
     */
    public function filterCustomFalseValues()
    {
        $this->assertFalse(B::filter('0', false, ['true'], ['0']));
    }

    /**
     * @test
     * @covers ::filter
     * @expectedException \Exception
     * @expectedExceptionMessage true is not 'y' or '1' or 'n' or '0' disregarding case and whitespace
     */
    public function filterCustomBoolValuesInvalidString()
    {
        $this->assertFalse(B::filter('true', false, ['y', '1'], ['n', '0']));
    }
}
