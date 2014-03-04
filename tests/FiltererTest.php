<?php

namespace DominionEnterprises;
use DominionEnterprises\Filterer as F;

final class FiltererTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @covers \DominionEnterprises\Filterer::filter
     */
    public function requiredPass()
    {
        $result = F::filter(array('fieldOne' => array('required' => false)), array());
        $this->assertSame(array(true, array(), null, array()), $result);
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filterer::filter
     */
    public function requiredFail()
    {
        $result = F::filter(array('fieldOne' => array('required' => true)), array());
        $this->assertSame(array(false, null, "Field 'fieldOne' was required and not present", array()), $result);
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filterer::filter
     */
    public function requiredDefaultPass()
    {
        $result = F::filter(array('fieldOne' => array()), array());
        $this->assertSame(array(true, array(), null, array()), $result);
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filterer::filter
     */
    public function requiredDefaultFail()
    {
        $result = F::filter(array('fieldOne' => array()), array(), array('defaultRequired' => true));
        $this->assertSame(array(false, null, "Field 'fieldOne' was required and not present", array()), $result);
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filterer::filter
     */
    public function filterPass()
    {
        $result = F::filter(array('fieldOne' => array(array('floatval'))), array('fieldOne' => '3.14'));
        $this->assertSame(array(true, array('fieldOne' => 3.14), null, array()), $result);
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filterer::filter
     */
    public function filterDefaultShortNamePass()
    {
        $result = F::filter(array('fieldOne' => array(array('float'))), array('fieldOne' => '3.14'));
        $this->assertSame(array(true, array('fieldOne' => 3.14), null, array()), $result);
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filterer::filter
     * @covers \DominionEnterprises\Filterer::setFilterAliases
     */
    public function filterCustomShortNamePass()
    {
        F::setFilterAliases(array('aShortName' => 'floatval'));
        $result = F::filter(array('fieldOne' => array(array('floatval'))), array('fieldOne' => '3.14'));
        $this->assertSame(array(true, array('fieldOne' => 3.14), null, array()), $result);
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filterer::filter
     * @covers \DominionEnterprises\Filterer::setFilterAliases
     * @covers \DominionEnterprises\Filterer::getFilterAliases
     */
    public function filterGetSetKnownFilters()
    {
        $knownFilters = array('shortNameOne' => 'fullNameOne', 'shortNameTwo' => 'fullNameTwo');
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
            array('fieldOne' => array(array('\DominionEnterprises\FiltererTest::failingFilter'))),
            array('fieldOne' => 'valueOne')
        );
        $this->assertSame(array(false, null, "Field 'fieldOne' with value 'valueOne' failed filtering, message 'i failed'", array()), $result);
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filterer::filter
     */
    public function chainPass()
    {
        $result = F::filter(
            array(
                'fieldOne' => array(
                    array('trim', 'a'),
                    array('floatval'),
                ),
            ),
            array('fieldOne' => 'a3.14')
        );
        $this->assertSame(array(true, array('fieldOne' => 3.14), null, array()), $result);
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filterer::filter
     */
    public function chainFail()
    {
        $result = F::filter(
            array(
                'fieldOne' => array(
                    array('trim'),
                    array('\DominionEnterprises\FiltererTest::failingFilter'),
                ),
            ),
            array('fieldOne' => 'the value')
        );
        $this->assertSame(array(false, null, "Field 'fieldOne' with value 'the value' failed filtering, message 'i failed'", array()), $result);
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filterer::filter
     */
    public function multiInputPass()
    {
        $result = F::filter(
            array('fieldOne' => array(array('trim')), 'fieldTwo' => array(array('strtoupper'))),
            array('fieldOne' => ' value', 'fieldTwo' => 'bob')
        );
        $this->assertSame(array(true, array('fieldOne' => 'value', 'fieldTwo' => 'BOB'), null, array()), $result);
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filterer::filter
     */
    public function multiInputFail()
    {
        $result = F::filter(
            array(
                'fieldOne' => array(array('\DominionEnterprises\FiltererTest::failingFilter')),
                'fieldTwo' => array(array('\DominionEnterprises\FiltererTest::failingFilter')),
            ),
            array('fieldOne' => 'value one', 'fieldTwo' => 'value two')
        );
        $expectedMessage = "Field 'fieldOne' with value 'value one' failed filtering, message 'i failed'\n";
        $expectedMessage .= "Field 'fieldTwo' with value 'value two' failed filtering, message 'i failed'";
        $this->assertSame(array(false, null, $expectedMessage, array()), $result);
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filterer::filter
     */
    public function emptyFilter()
    {
        $result = F::filter(array('fieldOne' => array(array())), array('fieldOne' => 0));
        $this->assertSame(array(true, array('fieldOne' => 0), null, array()), $result);
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filterer::filter
     */
    public function unknownsAllowed()
    {
        $result = F::filter(array(), array('fieldTwo' => 0), array('allowUnknowns' => true));
        $this->assertSame(array(true, array(), null, array('fieldTwo' => 0)), $result);
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filterer::filter
     */
    public function unknownsNotAllowed()
    {
        $result = F::filter(array(), array('fieldTwo' => 0));
        $this->assertSame(array(false, null, "Field 'fieldTwo' with value '0' is unknown", array('fieldTwo' => 0)), $result);
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filterer::filter
     */
    public function objectFilter()
    {
        $result = F::filter(array('fieldOne' => array(array(array(new TestFilter(), 'filter')))), array('fieldOne' => 'foo'));
        $this->assertSame(array(true, array('fieldOne' => 'fooboo'), null, array()), $result);
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filterer::filter
     * @expectedException Exception
     * @expectedExceptionMessage Function 'boo' for field 'foo' is not callable
     */
    public function notCallable()
    {
        F::filter(array('foo' => array(array('boo'))), array('foo' => 0));
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filterer::filter
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage 'allowUnknowns' option was not a bool
     */
    public function allowUnknownsNotBool()
    {
        F::filter(array(), array(), array('allowUnknowns' => 1));
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filterer::filter
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage 'defaultRequired' option was not a bool
     */
    public function defaultRequiredNotBool()
    {
        F::filter(array(), array(), array('defaultRequired' => 1));
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filterer::filter
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage filters for field 'boo' was not a array
     */
    public function filtersNotArrayInLeftOverSpec()
    {
        F::filter(array('boo' => 1), array());
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filterer::filter
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage filters for field 'boo' was not a array
     */
    public function filtersNotArrayWithInput()
    {
        F::filter(array('boo' => 1), array('boo' => 'notUnderTest'));
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filterer::filter
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage filter for field 'boo' was not a array
     */
    public function filterNotArray()
    {
        F::filter(array('boo' => array(1)), array('boo' => 'notUnderTest'));
    }

    /**
     * @test
     * @covers \DominionEnterprises\Filterer::filter
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage 'required' for field 'boo' was not a bool
     */
    public function requiredNotBool()
    {
        F::filter(array('boo' => array('required' => 1)), array());
    }

    public static function failingFilter($val)
    {
        throw new \Exception('i failed');
    }
}

final class TestFilter
{
    public function filter($value)
    {
        return $value . 'boo';
    }
}
