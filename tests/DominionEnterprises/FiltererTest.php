<?php

namespace DominionEnterprises;
use DominionEnterprises\Filterer as F;

final class FiltererTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function requiredPass()
    {
        $result = F::filter(array('fieldOne' => array('required' => false)), array());
        $this->assertSame(array('status' => true, 'result' => array(), 'unknowns' => array(), 'error' => null), $result);
    }

    /**
     * @test
     */
    public function requiredFail()
    {
        $result = F::filter(array('fieldOne' => array('required' => true)), array());
        $this->assertSame(
            array('status' => false, 'result' => null, 'unknowns' => array(), 'error' => "Field 'fieldOne' was required and not present"),
            $result
        );
    }

    /**
     * @test
     */
    public function requiredDefaultPass()
    {
        $result = F::filter(array('fieldOne' => array()), array());
        $this->assertSame(array('status' => true, 'result' => array(), 'unknowns' => array(), 'error' => null), $result);
    }

    /**
     * @test
     */
    public function requiredDefaultFail()
    {
        $result = F::filter(array('fieldOne' => array()), array(), array('defaultRequired' => true));
        $this->assertSame(
            array('status' => false, 'result' => null, 'unknowns' => array(), 'error' => "Field 'fieldOne' was required and not present"),
            $result
        );
    }

    /**
     * @test
     */
    public function filterPass()
    {
        $result = F::filter(array('fieldOne' => array(array('floatval'))), array('fieldOne' => '3.14'));
        $this->assertSame(array('status' => true, 'result' => array('fieldOne' => 3.14), 'unknowns' => array(), 'error' => null), $result);
    }

    /**
     * @test
     */
    public function filterFail()
    {
        $result = F::filter(
            array('fieldOne' => array(array('\DominionEnterprises\FiltererTest::failingFilter'))),
            array('fieldOne' => 'valueOne')
        );
        $this->assertSame(
            array(
                'status' => false,
                'result' => null,
                'unknowns' => array(),
                'error' => "Field 'fieldOne' with value 'valueOne' failed filtering, message 'i failed'",
            ),
            $result
        );
    }

    /**
     * @test
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
        $this->assertSame(array('status' => true, 'result' => array('fieldOne' => 3.14), 'unknowns' => array(), 'error' => null), $result);
    }

    /**
     * @test
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
        $this->assertSame(
            array(
                'status' => false,
                'result' => null,
                'unknowns' => array(),
                'error' => "Field 'fieldOne' with value 'the value' failed filtering, message 'i failed'",
            ),
            $result
        );
    }

    /**
     * @test
     */
    public function multiInputPass()
    {
        $result = F::filter(
            array('fieldOne' => array(array('trim')), 'fieldTwo' => array(array('strtoupper'))),
            array('fieldOne' => ' value', 'fieldTwo' => 'bob')
        );
        $this->assertSame(
            array('status' => true, 'result' => array('fieldOne' => 'value', 'fieldTwo' => 'BOB'), 'unknowns' => array(), 'error' => null),
            $result
        );
    }

    /**
     * @test
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
        $this->assertSame(array('status' => false, 'result' => null, 'unknowns' => array(), 'error' => $expectedMessage), $result);
    }

    /**
     * @test
     */
    public function emptyFilter()
    {
        $result = F::filter(array('fieldOne' => array(array())), array('fieldOne' => 0));
        $this->assertSame(array('status' => true, 'result' => array('fieldOne' => 0), 'unknowns' => array(), 'error' => null), $result);
    }

    /**
     * @test
     */
    public function unknownsAllowed()
    {
        $result = F::filter(array(), array('fieldTwo' => 0), array('allowUnknowns' => true));
        $this->assertSame(array('status' => true, 'result' => array(), 'unknowns' => array('fieldTwo' => 0), 'error' => null), $result);
    }

    /**
     * @test
     */
    public function unknownsNotAllowed()
    {
        $result = F::filter(array(), array('fieldTwo' => 0));
        $this->assertSame(
            array(
                'status' => false,
                'result' => null,
                'unknowns' => array('fieldTwo' => 0),
                'error' => "Field 'fieldTwo' with value '0' is unknown",
            ),
            $result
        );
    }

    /**
     * @test
     */
    public function objectFilter()
    {
        $result = F::filter(array('fieldOne' => array(array(array(new TestFilter(), 'filter')))), array('fieldOne' => 'foo'));
        $this->assertSame(array('status' => true, 'result' => array('fieldOne' => 'fooboo'), 'unknowns' => array(), 'error' => null), $result);
    }

    /**
     * @test
     * @expectedException Exception
     * @expectedExceptionMessage Function 'boo' for field 'foo' is not callable
     */
    public function notCallable()
    {
        F::filter(array('foo' => array(array('boo'))), array('foo' => 0));
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage 'allowUnknowns' option was not a bool
     */
    public function allowUnknownsNotBool()
    {
        F::filter(array(), array(), array('allowUnknowns' => 1));
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage 'defaultRequired' option was not a bool
     */
    public function defaultRequiredNotBool()
    {
        F::filter(array(), array(), array('defaultRequired' => 1));
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage filters for field 'boo' was not a array
     */
    public function filtersNotArrayInLeftOverSpec()
    {
        F::filter(array('boo' => 1), array());
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage filters for field 'boo' was not a array
     */
    public function filtersNotArrayWithInput()
    {
        F::filter(array('boo' => 1), array('boo' => 'notUnderTest'));
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage filter for field 'boo' was not a array
     */
    public function filterNotArray()
    {
        F::filter(array('boo' => array(1)), array('boo' => 'notUnderTest'));
    }

    /**
     * @test
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
