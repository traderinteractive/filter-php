<?php

namespace DominionEnterprises;

use DominionEnterprises\Filterer as F;

/**
 * @coversDefaultClass \DominionEnterprises\Filterer
 */
final class FiltererTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @covers ::filter
     */
    public function requiredPass()
    {
        $result = F::filter(['fieldOne' => ['required' => false]], []);
        $this->assertSame([true, [], null, []], $result);
    }

    /**
     * @test
     * @covers ::filter
     */
    public function requiredFail()
    {
        $result = F::filter(['fieldOne' => ['required' => true]], []);
        $this->assertSame([false, null, "Field 'fieldOne' was required and not present", []], $result);
    }

    /**
     * @test
     * @covers ::filter
     */
    public function requiredWithADefaultWithoutInput()
    {
        $result = F::filter(['fieldOne' => ['required' => true, 'default' => 'theDefault']], []);
        $this->assertSame([true, ['fieldOne' => 'theDefault'], null, []], $result);
    }

    /**
     * @test
     * @covers ::filter
     */
    public function requiredWithANullDefaultWithoutInput()
    {
        $result = F::filter(['fieldOne' => ['required' => true, 'default' => null]], []);
        $this->assertSame([true, ['fieldOne' => null], null, []], $result);
    }

    /**
     * @test
     * @covers ::filter
     */
    public function requiredWithADefaultWithInput()
    {
        $result = F::filter(
            ['fieldOne' => ['required' => true, 'default' => 'theDefault']],
            ['fieldOne' => 'notTheDefault']
        );
        $this->assertSame([true, ['fieldOne' => 'notTheDefault'], null, []], $result);
    }

    /**
     * @test
     * @covers ::filter
     */
    public function notRequiredWithADefaultWithoutInput()
    {
        $result = F::filter(['fieldOne' => ['default' => 'theDefault']], []);
        $this->assertSame([true, ['fieldOne' => 'theDefault'], null, []], $result);
    }

    /**
     * @test
     * @covers ::filter
     */
    public function notRequiredWithADefaultWithInput()
    {
        $result = F::filter(['fieldOne' => ['default' => 'theDefault']], ['fieldOne' => 'notTheDefault']);
        $this->assertSame([true, ['fieldOne' => 'notTheDefault'], null, []], $result);
    }

    /**
     * @test
     * @covers ::filter
     */
    public function requiredDefaultPass()
    {
        $result = F::filter(['fieldOne' => []], []);
        $this->assertSame([true, [], null, []], $result);
    }

    /**
     * @test
     * @covers ::filter
     */
    public function requiredDefaultFail()
    {
        $result = F::filter(['fieldOne' => []], [], ['defaultRequired' => true]);
        $this->assertSame([false, null, "Field 'fieldOne' was required and not present", []], $result);
    }

    /**
     * @test
     * @covers ::filter
     */
    public function filterPass()
    {
        $result = F::filter(['fieldOne' => [['floatval']]], ['fieldOne' => '3.14']);
        $this->assertSame([true, ['fieldOne' => 3.14], null, []], $result);
    }

    /**
     * @test
     * @covers ::filter
     */
    public function filterDefaultShortNamePass()
    {
        $result = F::filter(['fieldOne' => [['float']]], ['fieldOne' => '3.14']);
        $this->assertSame([true, ['fieldOne' => 3.14], null, []], $result);
    }

    /**
     * @test
     * @covers ::filter
     * @covers ::setFilterAliases
     */
    public function filterCustomShortNamePass()
    {
        F::setFilterAliases(['fval' => 'floatval']);
        $result = F::filter(['fieldOne' => [['fval']]], ['fieldOne' => '3.14']);
        $this->assertSame([true, ['fieldOne' => 3.14], null, []], $result);
    }

    /**
     * @test
     * @covers ::filter
     * @covers ::setFilterAliases
     * @covers ::getFilterAliases
     */
    public function filterGetSetKnownFilters()
    {
        $knownFilters = ['lower' => 'strtolower', 'upper' => 'strtoupper'];
        F::setFilterAliases($knownFilters);
        $this->assertSame($knownFilters, F::getFilterAliases());
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filterer::filter
     */
    public function filterFail()
    {
        $result = F::filter(
            ['fieldOne' => [['\DominionEnterprises\FiltererTest::failingFilter']]],
            ['fieldOne' => 'valueOne']
        );
        $this->assertSame(
            [false, null, "Field 'fieldOne' with value 'valueOne' failed filtering, message 'i failed'", []],
            $result
        );
    }

    /**
     * @test
     * @covers ::filter
     */
    public function chainPass()
    {
        $result = F::filter(['fieldOne' => [['trim', 'a'], ['floatval']]], ['fieldOne' => 'a3.14']);
        $this->assertSame([true, ['fieldOne' => 3.14], null, []], $result);
    }

    /**
     * @test
     * @covers ::filter
     */
    public function chainFail()
    {
        $result = F::filter(
            ['fieldOne' => [['trim'], ['\DominionEnterprises\FiltererTest::failingFilter']]],
            ['fieldOne' => 'the value']
        );
        $this->assertSame(
            [false, null, "Field 'fieldOne' with value 'the value' failed filtering, message 'i failed'", []],
            $result
        );
    }

    /**
     * @test
     * @covers ::filter
     */
    public function multiInputPass()
    {
        $result = F::filter(
            ['fieldOne' => [['trim']], 'fieldTwo' => [['strtoupper']]],
            ['fieldOne' => ' value', 'fieldTwo' => 'bob']
        );
        $this->assertSame([true, ['fieldOne' => 'value', 'fieldTwo' => 'BOB'], null, []], $result);
    }

    /**
     * @test
     * @covers ::filter
     */
    public function multiInputFail()
    {
        $result = F::filter(
            [
                'fieldOne' => [['\DominionEnterprises\FiltererTest::failingFilter']],
                'fieldTwo' => [['\DominionEnterprises\FiltererTest::failingFilter']],
            ],
            ['fieldOne' => 'value one', 'fieldTwo' => 'value two']
        );
        $expectedMessage = "Field 'fieldOne' with value 'value one' failed filtering, message 'i failed'\n";
        $expectedMessage .= "Field 'fieldTwo' with value 'value two' failed filtering, message 'i failed'";
        $this->assertSame([false, null, $expectedMessage, []], $result);
    }

    /**
     * @test
     * @covers ::filter
     */
    public function emptyFilter()
    {
        $result = F::filter(['fieldOne' => [[]]], ['fieldOne' => 0]);
        $this->assertSame([true, ['fieldOne' => 0], null, []], $result);
    }

    /**
     * @test
     * @covers ::filter
     */
    public function unknownsAllowed()
    {
        $result = F::filter([], ['fieldTwo' => 0], ['allowUnknowns' => true]);
        $this->assertSame([true, [], null, ['fieldTwo' => 0]], $result);
    }

    /**
     * @test
     * @covers ::filter
     */
    public function unknownsNotAllowed()
    {
        $result = F::filter([], ['fieldTwo' => 0]);
        $this->assertSame([false, null, "Field 'fieldTwo' with value '0' is unknown", ['fieldTwo' => 0]], $result);
    }

    /**
     * @test
     * @covers ::filter
     */
    public function objectFilter()
    {
        $result = F::filter(['fieldOne' => [[[$this, 'passingFilter']]]], ['fieldOne' => 'foo']);
        $this->assertSame([true, ['fieldOne' => 'fooboo'], null, []], $result);
    }

    /**
     * @test
     * @covers ::filter
     * @expectedException Exception
     * @expectedExceptionMessage Function 'boo' for field 'foo' is not callable
     */
    public function notCallable()
    {
        F::filter(['foo' => [['boo']]], ['foo' => 0]);
    }

    /**
     * @test
     * @covers ::filter
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage 'allowUnknowns' option was not a bool
     */
    public function allowUnknownsNotBool()
    {
        F::filter([], [], ['allowUnknowns' => 1]);
    }

    /**
     * @test
     * @covers ::filter
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage 'defaultRequired' option was not a bool
     */
    public function defaultRequiredNotBool()
    {
        F::filter([], [], ['defaultRequired' => 1]);
    }

    /**
     * @test
     * @covers ::filter
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage filters for field 'boo' was not a array
     */
    public function filtersNotArrayInLeftOverSpec()
    {
        F::filter(['boo' => 1], []);
    }

    /**
     * @test
     * @covers ::filter
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage filters for field 'boo' was not a array
     */
    public function filtersNotArrayWithInput()
    {
        F::filter(['boo' => 1], ['boo' => 'notUnderTest']);
    }

    /**
     * @test
     * @covers ::filter
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage filter for field 'boo' was not a array
     */
    public function filterNotArray()
    {
        F::filter(['boo' => [1]], ['boo' => 'notUnderTest']);
    }

    /**
     * @test
     * @covers ::filter
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage 'required' for field 'boo' was not a bool
     */
    public function requiredNotBool()
    {
        F::filter(['boo' => ['required' => 1]], []);
    }

    /**
     * @test
     * @covers ::registerAlias
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage $alias was not a string or int
     */
    public function registerAliasAliasNotString()
    {
        F::registerAlias(true, 'strtolower');
    }

    /**
     * @test
     * @covers ::registerAlias
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage $overwrite was not a bool
     */
    public function registerAliasOverwriteNotBool()
    {
        F::registerAlias('lower', 'strtolower', 'foo');
    }

    /**
     * @test
     * @covers ::registerAlias
     * @expectedException \Exception
     * @expectedExceptionMessage Alias 'upper' exists
     */
    public function registerExistingAliasOverwriteFalse()
    {
        F::setFilterAliases([]);
        F::registerAlias('upper', 'strtoupper');
        F::registerAlias('upper', 'strtoupper', false);
    }

    /**
     * @test
     * @covers ::registerAlias
     */
    public function registerExistingAliasOverwriteTrue()
    {
        F::setFilterAliases(['upper' => 'strtoupper', 'lower' => 'strtolower']);
        F::registerAlias('upper', 'ucfirst', true);
        $this->assertSame(['upper' => 'ucfirst', 'lower' => 'strtolower'], F::getFilterAliases());
    }

    public static function failingFilter($val)
    {
        throw new \Exception('i failed');
    }

    public static function passingFilter($value)
    {
        return $value . 'boo';
    }

    /**
     * Verify custom errors can be added to filter spec.
     *
     * @test
     * @covers ::filter
     *
     * @return void
     */
    public function filterWithCustomError()
    {
        $result = F::filter(
            [
                'fieldOne' => [
                    ['\DominionEnterprises\FiltererTest::failingFilter'],
                    'error' => 'My custom error message'
                ],
            ],
            ['fieldOne' => 'valueOne']
        );
        $this->assertSame(
            [false, null, 'My custom error message', []],
            $result
        );
    }

    /**
     * Verify behavior of filter() when 'error' is not a string value.
     *
     * @test
     * @covers ::filter
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage error for field 'fieldOne' was not a non-empty string
     *
     * @return void
     */
    public function filterWithNonStringError()
    {
        F::filter(
            ['fieldOne' => [['strtoupper'], 'error' => new \StdClass()]],
            ['fieldOne' => 'valueOne']
        );
    }

    /**
     * Verify behavior of filter() when 'error' is an empty string.
     *
     * @test
     * @covers ::filter
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage error for field 'fieldOne' was not a non-empty string
     *
     * @return void
     */
    public function filterWithEmptyStringError()
    {
        F::filter(
            ['fieldOne' => [['strtoupper'], 'error' => "\n   \t"]],
            ['fieldOne' => 'valueOne']
        );
    }
}
