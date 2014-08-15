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
    public function filter_basic()
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
    public function filter_allowNullIsNotBool()
    {
        B::filter('true', 1);
    }

    /**
     * @test
     * @covers ::filter
     */
    public function filter_allowNullIsTrueAndNullValue()
    {
        $this->assertNull(B::filter(null, true));
    }

    /**
     * @test
     * @covers ::filter
     * @expectedException \Exception
     * @expectedExceptionMessage "1" $value is not a string
     */
    public function filter_nonStringAndNonBoolValue()
    {
        B::filter(1);
    }

    /**
     * @test
     * @covers ::filter
     * @expectedException \Exception
     * @expectedExceptionMessage invalid is not 'true' or 'false' disregarding case and whitespace
     */
    public function filter_invalidString()
    {
        B::filter('invalid');
    }

    /**
     * @test
     * @covers ::filter
     */
    public function filter_customTrueValues()
    {
        $this->assertTrue(B::filter('Y', false, ['y']));
    }

    /**
     * @test
     * @covers ::filter
     */
    public function filter_customFalseValues()
    {
        $this->assertFalse(B::filter('0', false, ['true'], ['0']));
    }

    /**
     * @test
     * @covers ::filter
     * @expectedException \Exception
     * @expectedExceptionMessage true is not 'y' or '1' or 'n' or '0' disregarding case and whitespace
     */
    public function filter_customBoolValuesInvalidString()
    {
        $this->assertFalse(B::filter('true', false, ['y', '1'], ['n', '0']));
    }
}
