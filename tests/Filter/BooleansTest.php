<?php
namespace DominionEnterprises\Filter;

/**
 * @coversDefaultClass \DominionEnterprises\Filter\Booleans
 */
final class BooleansTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @covers ::filter
     */
    public function filterBasic()
    {
        $this->assertTrue(Booleans::filter(true));
        $this->assertTrue(Booleans::filter('   true'));
        $this->assertTrue(Booleans::filter(' TRUE '));
        $this->assertTrue(Booleans::filter('True '));

        $this->assertFalse(Booleans::filter('false   '));
        $this->assertFalse(Booleans::filter('FALSE  '));
        $this->assertFalse(Booleans::filter(' False '));
        $this->assertFalse(Booleans::filter(false));
    }

    /**
     * @test
     * @covers ::filter
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage $allowNull was not a bool
     */
    public function filterAllowNullIsNotBool()
    {
        Booleans::filter('true', 1);
    }

    /**
     * @test
     * @covers ::filter
     */
    public function filterAllowNullIsTrueAndNullValue()
    {
        $this->assertNull(Booleans::filter(null, true));
    }

    /**
     * @test
     * @covers ::filter
     * @expectedException \Exception
     * @expectedExceptionMessage "1" $value is not a string
     */
    public function filterNonStringAndNonBoolValue()
    {
        Booleans::filter(1);
    }

    /**
     * @test
     * @covers ::filter
     * @expectedException \Exception
     * @expectedExceptionMessage invalid is not 'true' or 'false' disregarding case and whitespace
     */
    public function filterInvalidString()
    {
        Booleans::filter('invalid');
    }

    /**
     * @test
     * @covers ::filter
     */
    public function filterCustomTrueValues()
    {
        $this->assertTrue(Booleans::filter('Y', false, ['y']));
    }

    /**
     * @test
     * @covers ::filter
     */
    public function filterCustomFalseValues()
    {
        $this->assertFalse(Booleans::filter('0', false, ['true'], ['0']));
    }

    /**
     * @test
     * @covers ::filter
     * @expectedException \Exception
     * @expectedExceptionMessage true is not 'y' or '1' or 'n' or '0' disregarding case and whitespace
     */
    public function filterCustomBoolValuesInvalidString()
    {
        $this->assertFalse(Booleans::filter('true', false, ['y', '1'], ['n', '0']));
    }
}
