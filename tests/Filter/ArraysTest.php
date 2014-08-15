<?php
namespace DominionEnterprises\Filter;
use DominionEnterprises\Filter\Arrays as A;

/**
 * @coversDefaultClass \DominionEnterprises\Filter\Arrays
 */
final class ArraysTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @covers ::filter
     */
    public function filter_basicPass()
    {
        $this->assertSame(['boo'], A::filter(['boo']));
    }

    /**
     * @test
     * @covers ::filter
     * @expectedException \Exception
     * @expectedExceptionMessage Value '1' is not an array
     */
    public function filter_failNotArray()
    {
        A::filter(1);
    }

    /**
     * @test
     * @covers ::filter
     * @expectedException \Exception
     * @expectedExceptionMessage $value count of 0 is less than 1
     */
    public function filter_failEmpty()
    {
        A::filter([]);
    }

    /**
     * @test
     * @covers ::filter
     * @expectedException \Exception
     * @expectedExceptionMessage $value count of 1 is less than 2
     */
    public function filter_countLessThanMin()
    {
        A::filter([0], 2);
    }

    /**
     * @test
     * @covers ::filter
     * @expectedException \Exception
     * @expectedExceptionMessage $value count of 2 is greater than 1
     */
    public function filter_countGreaterThanMax()
    {
        A::filter([0, 1], 1, 1);
    }

    /**
     * @test
     * @covers ::filter
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage $minCount was not an int
     */
    public function filter_minCountNotInt()
    {
        A::filter([], true);
    }

    /**
     * @test
     * @covers ::filter
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage $maxCount was not an int
     */
    public function filter_maxCountNotInt()
    {
        A::filter([], 0, true);
    }

    /**
     * @test
     * @covers ::in
     */
    public function in_passStrict()
    {
        $this->assertSame('boo', A::in('boo', ['boo']));
    }

    /**
     * @test
     * @covers ::in
     */
    public function in_failStrict()
    {
        try {
            A::in('0', [0]);
            $this->fail();
        } catch (\Exception $e) {
            $this->assertSame("Value '0' is not in array array (\n  0 => 0,\n)", $e->getMessage());
        }
    }

    /**
     * @test
     * @covers ::in
     */
    public function in_failNotStrict()
    {
        try {
            A::in('boo', ['foo'], false);
            $this->fail();
        } catch (\Exception $e) {
            $this->assertSame("Value 'boo' is not in array array (\n  0 => 'foo',\n)", $e->getMessage());
        }
    }

    /**
     * @test
     * @covers ::in
     */
    public function in_passNotStrict()
    {
        $this->assertSame('0', A::in('0', [0], false));
    }

    /**
     * @test
     * @covers ::in
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage $strict was not a bool
     */
    public function in_strictNotBool()
    {
        A::in('boo', [], 1);
    }

    /**
     * @test
     * @covers ::ofScalars
     * @uses \DominionEnterprises\Filter\Int
     * @uses \DominionEnterprises\Filter\UnsignedInt
     * @uses \DominionEnterprises\Filterer
     */
    public function ofScalars()
    {
        $this->assertSame([1, 2], A::ofScalars(['1', '2'], [['uint']]));
    }

    /**
     * @test
     * @covers ::ofScalars
     * @uses \DominionEnterprises\Filterer
     */
    public function ofScalars_chained()
    {
        $this->assertSame([3.3, 5.5], A::ofScalars(['a3.3', 'a5.5'], [['trim', 'a'], ['floatval']]));
    }

    /**
     * @test
     * @covers ::ofScalars
     * @uses \DominionEnterprises\Filter\Int
     * @uses \DominionEnterprises\Filter\UnsignedInt
     * @uses \DominionEnterprises\Filterer
     */
    public function ofScalars_withMeaninglessKeys()
    {
        $this->assertSame(['key1' => 1, 'key2' => 2], A::ofScalars(['key1' => '1', 'key2' => '2'], [['uint']]));
    }

    /**
     * @test
     * @covers ::ofScalars
     * @uses \DominionEnterprises\Filter\String
     * @uses \DominionEnterprises\Filterer
     */
    public function ofScalars_fail()
    {
        try {
            A::ofScalars(['1', 2, 3], [['string']]);
            $this->fail();
        } catch (\Exception $e) {
            $expected = "Field '1' with value '2' failed filtering, message 'Value '2' is not a string'\n";
            $expected .= "Field '2' with value '3' failed filtering, message 'Value '3' is not a string'";
            $this->assertSame($expected, $e->getMessage());
        }
    }

    /**
     * @test
     * @covers ::ofArrays
     * @uses \DominionEnterprises\Filter\Int
     * @uses \DominionEnterprises\Filter\UnsignedInt
     * @uses \DominionEnterprises\Filterer
     */
    public function ofArrays()
    {
        $expected = [['key' => 1], ['key' => 2]];
        $this->assertSame($expected, A::ofArrays([['key' => '1'], ['key' => '2']], ['key' => [['uint']]]));
    }

    /**
     * @test
     * @covers ::ofArrays
     * @uses \DominionEnterprises\Filterer
     */
    public function ofArrays_chained()
    {
        $expected = [['key' => 3.3], ['key' => 5.5]];
        $spec = ['key' => [['trim', 'a'], ['floatval']]];
        $this->assertSame($expected, A::ofArrays([['key' => 'a3.3'], ['key' => 'a5.5']], $spec));
    }

    /**
     * @test
     * @covers ::ofArrays
     * @uses \DominionEnterprises\Filter\Int
     * @uses \DominionEnterprises\Filter\UnsignedInt
     * @uses \DominionEnterprises\Filterer
     */
    public function ofArrays_requiredAndUnknown()
    {
        try {
            A::ofArrays([['key' => '1'], ['key2' => '2']], ['key' => ['required' => true, ['uint']]]);
            $this->fail();
        } catch (\Exception $e) {
            $expected = "Field 'key' was required and not present\nField 'key2' with value '2' is unknown";
            $this->assertSame($expected, $e->getMessage());
        }
    }

    /**
     * @test
     * @covers ::ofArrays
     * @uses \DominionEnterprises\Filter\String
     * @uses \DominionEnterprises\Filterer
     */
    public function ofArrays_fail()
    {
        try {
            A::ofArrays([['key' => '1'], ['key' => 2], ['key' => 3]], ['key' => [['string']]]);
            $this->fail();
        } catch (\Exception $e) {
            $expected = "Field 'key' with value '2' failed filtering, message 'Value '2' is not a string'\n";
            $expected .= "Field 'key' with value '3' failed filtering, message 'Value '3' is not a string'";
            $this->assertSame($expected, $e->getMessage());
        }
    }

    /**
     * @test
     * @covers ::ofArray
     * @uses \DominionEnterprises\Filter\Int
     * @uses \DominionEnterprises\Filter\UnsignedInt
     * @uses \DominionEnterprises\Filterer
     */
    public function ofArray()
    {
        $expected = ['key1' => 1, 'key2' => 2];
        $spec = ['key1' => [['uint']], 'key2' => [['uint']]];
        $this->assertSame($expected, A::ofArray(['key1' => '1', 'key2' => '2'], $spec));
    }

    /**
     * @test
     * @covers ::ofArray
     * @uses \DominionEnterprises\Filterer
     */
    public function ofArray_chained()
    {
        $expected = ['key' => 3.3];
        $spec = ['key' => [['trim', 'a'], ['floatval']]];
        $this->assertSame($expected, A::ofArray(['key' => 'a3.3'], $spec));
    }

    /**
     * @test
     * @covers ::ofArray
     * @uses \DominionEnterprises\Filter\Int
     * @uses \DominionEnterprises\Filter\UnsignedInt
     * @uses \DominionEnterprises\Filterer
     */
    public function ofArray_requiredSuccess()
    {
        $expected = ['key2' => 2];
        $spec = ['key1' => [['uint']], 'key2' => ['required' => true, ['uint']]];
        $this->assertSame($expected, A::ofArray(['key2' => '2'], $spec));
    }

    /**
     * @test
     * @covers ::ofArray
     * @uses \DominionEnterprises\Filter\Int
     * @uses \DominionEnterprises\Filter\UnsignedInt
     * @uses \DominionEnterprises\Filterer
     */
    public function ofArray_requiredFail()
    {
        try {
            A::ofArray(['key1' => '1'], ['key1' => [['uint']], 'key2' => ['required' => true, ['uint']]]);
            $this->fail();
        } catch (\Exception $e) {
            $expected = "Field 'key2' was required and not present";
            $this->assertSame($expected, $e->getMessage());
        }
    }

    /**
     * @test
     * @covers ::ofArray
     * @uses \DominionEnterprises\Filterer
     */
    public function ofArray_unknown()
    {
        try {
            A::ofArray(['key' => '1'], ['key2' => [['uint']]]);
            $this->fail();
        } catch (\Exception $e) {
            $expected = "Field 'key' with value '1' is unknown";
            $this->assertSame($expected, $e->getMessage());
        }
    }

    /**
     * Verifies the basic behavior of the flatten filter.
     *
     * @test
     * @covers ::flatten
     */
    public function flatten()
    {
        $this->assertSame([1, 2, 3, 4, 5], A::flatten([[1, 2], [[3, [4, 5]]]]));
    }
}
