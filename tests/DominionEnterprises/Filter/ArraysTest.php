<?php
namespace DominionEnterprises\Filter;
use DominionEnterprises\Filter\Arrays as A;

final class ArraysTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @covers \DominionEnterprises\Filter\Arrays::filter
     */
    public function filter_basicPass()
    {
        $this->assertSame(array('boo'), A::filter(array('boo')));
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filter\Arrays::filter
     * @expectedException \Exception
     * @expectedExceptionMessage Value '1' is not an array
     */
    public function filter_failNotArray()
    {
        A::filter(1);
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filter\Arrays::filter
     * @expectedException \Exception
     * @expectedExceptionMessage $value count of 0 is less than 1
     */
    public function filter_failEmpty()
    {
        A::filter(array());
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filter\Arrays::filter
     * @expectedException \Exception
     * @expectedExceptionMessage $value count of 1 is less than 2
     */
    public function filter_countLessThanMin()
    {
        A::filter(array(0), 2);
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filter\Arrays::filter
     * @expectedException \Exception
     * @expectedExceptionMessage $value count of 2 is greater than 1
     */
    public function filter_countGreaterThanMax()
    {
        A::filter(array(0, 1), 1, 1);
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filter\Arrays::filter
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage $minCount was not an int
     */
    public function filter_minCountNotInt()
    {
        A::filter(array(), true);
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filter\Arrays::filter
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage $maxCount was not an int
     */
    public function filter_maxCountNotInt()
    {
        A::filter(array(), 0, true);
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filter\Arrays::in
     */
    public function in_passStrict()
    {
        $this->assertSame('boo', A::in('boo', array('boo')));
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filter\Arrays::in
     */
    public function in_failStrict()
    {
        try {
            A::in('0', array(0));
            $this->fail();
        } catch (\Exception $e) {
            $this->assertSame("Value '0' is not in array array (\n  0 => 0,\n)", $e->getMessage());
        }
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filter\Arrays::in
     */
    public function in_failNotStrict()
    {
        try {
            A::in('boo', array('foo'), false);
            $this->fail();
        } catch (\Exception $e) {
            $this->assertSame("Value 'boo' is not in array array (\n  0 => 'foo',\n)", $e->getMessage());
        }
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filter\Arrays::in
     */
    public function in_passNotStrict()
    {
        $this->assertSame('0', A::in('0', array(0), false));
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filter\Arrays::in
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage $strict was not a bool
     */
    public function in_strictNotBool()
    {
        A::in('boo', array(), 1);
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filter\Arrays::ofScalars
     */
    public function ofScalars()
    {
        $this->assertSame(array(1, 2), A::ofScalars(array('1', '2'), array(array('uint'))));
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filter\Arrays::ofScalars
     */
    public function ofScalars_chained()
    {
        $this->assertSame(array(3.3, 5.5), A::ofScalars(array('a3.3', 'a5.5'), array(array('trim', 'a'), array('floatval'))));
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filter\Arrays::ofScalars
     */
    public function ofScalars_withMeaninglessKeys()
    {
        $this->assertSame(array('key1' => 1, 'key2' => 2), A::ofScalars(array('key1' => '1', 'key2' => '2'), array(array('uint'))));
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filter\Arrays::ofScalars
     */
    public function ofScalars_fail()
    {
        try {
            A::ofScalars(array('1', 2, 3), array(array('string')));
            $this->fail();
        } catch (\Exception $e) {
            $expected = "Field '1' with value '2' failed filtering, message 'Value '2' is not a string'\n";
            $expected .= "Field '2' with value '3' failed filtering, message 'Value '3' is not a string'";
            $this->assertSame($expected, $e->getMessage());
        }
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filter\Arrays::ofArrays
     */
    public function ofArrays()
    {
        $expected = array(array('key' => 1), array('key' => 2));
        $this->assertSame($expected, A::ofArrays(array(array('key' => '1'), array('key' => '2')), array('key' => array(array('uint')))));
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filter\Arrays::ofArrays
     */
    public function ofArrays_chained()
    {
        $expected = array(array('key' => 3.3), array('key' => 5.5));
        $spec = array('key' => array(array('trim', 'a'), array('floatval')));
        $this->assertSame($expected, A::ofArrays(array(array('key' => 'a3.3'), array('key' => 'a5.5')), $spec));
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filter\Arrays::ofArrays
     */
    public function ofArrays_requiredAndUnknown()
    {
        try {
            A::ofArrays(array(array('key' => '1'), array('key2' => '2')), array('key' => array('required' => true, array('uint'))));
            $this->fail();
        } catch (\Exception $e) {
            $expected = "Field 'key' was required and not present\nField 'key2' with value '2' is unknown";
            $this->assertSame($expected, $e->getMessage());
        }
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filter\Arrays::ofArrays
     */
    public function ofArrays_fail()
    {
        try {
            A::ofArrays(array(array('key' => '1'), array('key' => 2), array('key' => 3)), array('key' => array(array('string'))));
            $this->fail();
        } catch (\Exception $e) {
            $expected = "Field 'key' with value '2' failed filtering, message 'Value '2' is not a string'\n";
            $expected .= "Field 'key' with value '3' failed filtering, message 'Value '3' is not a string'";
            $this->assertSame($expected, $e->getMessage());
        }
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filter\Arrays::ofArray
     */
    public function ofArray()
    {
        $expected = array('key1' => 1, 'key2' => 2);
        $spec = array('key1' => array(array('uint')), 'key2' => array(array('uint')));
        $this->assertSame($expected, A::ofArray(array('key1' => '1', 'key2' => '2'), $spec));
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filter\Arrays::ofArray
     */
    public function ofArray_chained()
    {
        $expected = array('key' => 3.3);
        $spec = array('key' => array(array('trim', 'a'), array('floatval')));
        $this->assertSame($expected, A::ofArray(array('key' => 'a3.3'), $spec));
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filter\Arrays::ofArray
     */
    public function ofArray_requiredSuccess()
    {
        $expected = array('key2' => 2);
        $spec = array('key1' => array(array('uint')), 'key2' => array('required' => true, array('uint')));
        $this->assertSame($expected, A::ofArray(array('key2' => '2'), $spec));
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filter\Arrays::ofArray
     */
    public function ofArray_requiredFail()
    {
        try {
            A::ofArray(array('key1' => '1'), array('key1' => array(array('uint')), 'key2' => array('required' => true, array('uint'))));
            $this->fail();
        } catch (\Exception $e) {
            $expected = "Field 'key2' was required and not present";
            $this->assertSame($expected, $e->getMessage());
        }
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filter\Arrays::ofArray
     */
    public function ofArray_unknown()
    {
        try {
            A::ofArray(array('key' => '1'), array('key2' => array(array('uint'))));
            $this->fail();
        } catch (\Exception $e) {
            $expected = "Field 'key' with value '1' is unknown";
            $this->assertSame($expected, $e->getMessage());
        }
    }
}
