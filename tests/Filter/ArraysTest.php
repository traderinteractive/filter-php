<?php
namespace DominionEnterprises\Filter;

/**
 * @coversDefaultClass \DominionEnterprises\Filter\Arrays
 */
final class ArraysTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @covers ::filter
     */
    public function filterBasicPass()
    {
        $this->assertSame(['boo'], Arrays::filter(['boo']));
    }

    /**
     * @test
     * @covers ::filter
     * @expectedException \Exception
     * @expectedExceptionMessage Value '1' is not an array
     */
    public function filterFailNotArray()
    {
        Arrays::filter(1);
    }

    /**
     * @test
     * @covers ::filter
     * @expectedException \Exception
     * @expectedExceptionMessage $value count of 0 is less than 1
     */
    public function filterFailEmpty()
    {
        Arrays::filter([]);
    }

    /**
     * @test
     * @covers ::filter
     * @expectedException \Exception
     * @expectedExceptionMessage $value count of 1 is less than 2
     */
    public function filterCountLessThanMin()
    {
        Arrays::filter([0], 2);
    }

    /**
     * @test
     * @covers ::filter
     * @expectedException \Exception
     * @expectedExceptionMessage $value count of 2 is greater than 1
     */
    public function filterCountGreaterThanMax()
    {
        Arrays::filter([0, 1], 1, 1);
    }

    /**
     * @test
     * @covers ::filter
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage $minCount was not an int
     */
    public function filterMinCountNotInt()
    {
        Arrays::filter([], true);
    }

    /**
     * @test
     * @covers ::filter
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage $maxCount was not an int
     */
    public function filterMaxCountNotInt()
    {
        Arrays::filter([], 0, true);
    }

    /**
     * @test
     * @covers ::in
     */
    public function inPassStrict()
    {
        $this->assertSame('boo', Arrays::in('boo', ['boo']));
    }

    /**
     * @test
     * @covers ::in
     */
    public function inFailStrict()
    {
        try {
            Arrays::in('0', [0]);
            $this->fail();
        } catch (\Exception $e) {
            $this->assertSame("Value '0' is not in array array (\n  0 => 0,\n)", $e->getMessage());
        }
    }

    /**
     * @test
     * @covers ::in
     */
    public function inFailNotStrict()
    {
        try {
            Arrays::in('boo', ['foo'], false);
            $this->fail();
        } catch (\Exception $e) {
            $this->assertSame("Value 'boo' is not in array array (\n  0 => 'foo',\n)", $e->getMessage());
        }
    }

    /**
     * @test
     * @covers ::in
     */
    public function inPassNotStrict()
    {
        $this->assertSame('0', Arrays::in('0', [0], false));
    }

    /**
     * @test
     * @covers ::in
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage $strict was not a bool
     */
    public function inStrictNotBool()
    {
        Arrays::in('boo', [], 1);
    }

    /**
     * @test
     * @covers ::ofScalars
     */
    public function ofScalars()
    {
        $this->assertSame([1, 2], Arrays::ofScalars(['1', '2'], [['uint']]));
    }

    /**
     * @test
     * @covers ::ofScalars
     */
    public function ofScalarsChained()
    {
        $this->assertSame([3.3, 5.5], Arrays::ofScalars(['a3.3', 'a5.5'], [['trim', 'a'], ['floatval']]));
    }

    /**
     * @test
     * @covers ::ofScalars
     */
    public function ofScalarsWithMeaninglessKeys()
    {
        $this->assertSame(['key1' => 1, 'key2' => 2], Arrays::ofScalars(['key1' => '1', 'key2' => '2'], [['uint']]));
    }

    /**
     * @test
     * @covers ::ofScalars
     */
    public function ofScalarsFail()
    {
        try {
            Arrays::ofScalars(['1', [], new \StdClass], [['string']]);
            $this->fail();
        } catch (\Exception $e) {
            $expected = <<<TXT
Field '1' with value 'array (
)' failed filtering, message 'Value 'array (
)' is not a string'
Field '2' with value 'stdClass::__set_state(array(
))' failed filtering, message 'Value 'stdClass::__set_state(array(
))' is not a string'
TXT;
            $this->assertSame($expected, $e->getMessage());
        }
    }

    /**
     * @test
     * @covers ::ofArrays
     */
    public function ofArrays()
    {
        $expected = [['key' => 1], ['key' => 2]];
        $this->assertSame($expected, Arrays::ofArrays([['key' => '1'], ['key' => '2']], ['key' => [['uint']]]));
    }

    /**
     * @test
     * @covers ::ofArrays
     */
    public function ofArraysChained()
    {
        $expected = [['key' => 3.3], ['key' => 5.5]];
        $spec = ['key' => [['trim', 'a'], ['floatval']]];
        $this->assertSame($expected, Arrays::ofArrays([['key' => 'a3.3'], ['key' => 'a5.5']], $spec));
    }

    /**
     * @test
     * @covers ::ofArrays
     */
    public function ofArraysRequiredAndUnknown()
    {
        try {
            Arrays::ofArrays([['key' => '1'], ['key2' => '2']], ['key' => ['required' => true, ['uint']]]);
            $this->fail();
        } catch (\Exception $e) {
            $expected = "Field 'key' was required and not present\nField 'key2' with value '2' is unknown";
            $this->assertSame($expected, $e->getMessage());
        }
    }

    /**
     * @test
     * @covers ::ofArrays
     */
    public function ofArraysFail()
    {
        try {
            Arrays::ofArrays(
                [['key' => new \StdClass], ['key' => []], ['key' => null], 'key'],
                ['key' => [['string']]]
            );
            $this->fail();
        } catch (\Exception $e) {
            $expected = <<<TXT
Field 'key' with value 'stdClass::__set_state(array(
))' failed filtering, message 'Value 'stdClass::__set_state(array(
))' is not a string'
Field 'key' with value 'array (
)' failed filtering, message 'Value 'array (
)' is not a string'
Field 'key' with value 'NULL' failed filtering, message 'Value 'NULL' is not a string'
Value at position '3' was not an array
TXT;
            $this->assertSame($expected, $e->getMessage());
        }
    }

    /**
     * @test
     * @covers ::ofArray
     */
    public function ofArray()
    {
        $expected = ['key1' => 1, 'key2' => 2];
        $spec = ['key1' => [['uint']], 'key2' => [['uint']]];
        $this->assertSame($expected, Arrays::ofArray(['key1' => '1', 'key2' => '2'], $spec));
    }

    /**
     * @test
     * @covers ::ofArray
     */
    public function ofArrayChained()
    {
        $expected = ['key' => 3.3];
        $spec = ['key' => [['trim', 'a'], ['floatval']]];
        $this->assertSame($expected, Arrays::ofArray(['key' => 'a3.3'], $spec));
    }

    /**
     * @test
     * @covers ::ofArray
     */
    public function ofArrayRequiredSuccess()
    {
        $expected = ['key2' => 2];
        $spec = ['key1' => [['uint']], 'key2' => ['required' => true, ['uint']]];
        $this->assertSame($expected, Arrays::ofArray(['key2' => '2'], $spec));
    }

    /**
     * @test
     * @covers ::ofArray
     */
    public function ofArrayRequiredFail()
    {
        try {
            Arrays::ofArray(['key1' => '1'], ['key1' => [['uint']], 'key2' => ['required' => true, ['uint']]]);
            $this->fail();
        } catch (\Exception $e) {
            $expected = "Field 'key2' was required and not present";
            $this->assertSame($expected, $e->getMessage());
        }
    }

    /**
     * @test
     * @covers ::ofArray
     */
    public function ofArrayUnknown()
    {
        try {
            Arrays::ofArray(['key' => '1'], ['key2' => [['uint']]]);
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
        $this->assertSame([1, 2, 3, 4, 5], Arrays::flatten([[1, 2], [[3, [4, 5]]]]));
    }

    /**
     * Verify the behavior of in() with callable haystack
     *
     * @test
     * @covers ::in
     *
     * @return void
     */
    public function in_callableHayStack()
    {
        $callable = function () {
            return ['foo', 'bar'];
        };

        $this->assertSame('bar', A::in('bar', $callable));
    }

    /**
     * Verify the behavior of in() with invalid haystack
     *
     * @test
     * @covers ::in
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Given $haystack was not an array or result from callable was not an array
     *
     * @return void
     */
    public function in_invalidHayStack()
    {
        A::in('foo', 'haystack');
    }
}
