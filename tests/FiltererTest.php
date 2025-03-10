<?php

namespace TraderInteractive;

use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use stdClass;
use Throwable;
use TraderInteractive\Exceptions\FilterException;
use TraderInteractive\Filter\Arrays;
use TypeError;

/**
 * @coversDefaultClass \TraderInteractive\Filterer
 * @covers ::__construct
 * @covers ::<private>
 */
final class FiltererTest extends TestCase
{
    /**
     * @var string
     */
    const FULL_XML = (''
        . "<?xml version=\"1.0\"?>\n"
            . '<books>'
                . '<book id="bk101">'
                    . '<author>Gambardella, Matthew</author>'
                    . "<title>XML Developer's Guide</title>"
                    . '<genre>Computer</genre>'
                    . '<price>44.95</price>'
                    . '<publish_date>2000-10-01</publish_date>'
                    . '<description>An in-depth look at creating applications with XML.</description>'
            . '</book>'
        . '<book id="bk102">'
                    . '<author>Ralls, Kim</author>'
                    . '<title>Midnight Rain</title>'
                    . '<genre>Fantasy</genre>'
                    . '<price>5.95</price>'
                    . '<publish_date>2000-12-16</publish_date>'
                    . '<description>A former architect battles corporate zombies</description>'
            . '</book>'
        . "</books>\n"
    );

    public function setUp(): void
    {
        Filterer::setFilterAliases(Filterer::DEFAULT_FILTER_ALIASES);
    }

    /**
     * @test
     * @covers ::filter
     * @covers ::execute
     * @dataProvider provideValidFilterData
     *
     * @param array $spec  The filter specification to be use.
     * @param array $input The input to be filtered.
     * @param array $options The filterer options
     * @param array $expectedResult The expected filterer result.
     */
    public function filter(array $spec, array $input, array $options, array $expectedResult)
    {
        $this->assertSame($expectedResult, Filterer::filter($spec, $input, $options));
    }

    public function provideValidFilterData() : array
    {
        return [
            'not required field not present' => [
                'spec' => ['fieldOne' => ['required' => false]],
                'input' => [],
                'options' => [],
                'result' => [true, [], null, []],
            ],
            'Required With A Default Without Input' => [
                'spec' => ['fieldOne' => ['required' => true, 'default' => 'theDefault']],
                'input' => [],
                'options' => [],
                'result' => [true, ['fieldOne' => 'theDefault'], null, []],
            ],
            'Required With A Null Default Without Input' => [
                'spec' => ['fieldOne' => ['required' => true, 'default' => null]],
                'input' => [],
                'options' => [],
                'result' => [true, ['fieldOne' => null], null, []],
            ],
            'Required With A Default With Input' => [
                'spec' => ['fieldOne' => ['required' => true, 'default' => 'theDefault']],
                'input' => ['fieldOne' => 'notTheDefault'],
                'options' => [],
                'result' => [true, ['fieldOne' => 'notTheDefault'], null, []],
            ],
            'Not Required With A Default Without Input' => [
                'spec' => ['fieldOne' => ['default' => 'theDefault']],
                'input' => [],
                'options' => [],
                'result' => [true, ['fieldOne' => 'theDefault'], null, []],
            ],
            'Not Required With A Default With Input' => [
                'spec' => ['fieldOne' => ['default' => 'theDefault']],
                'input' => ['fieldOne' => 'notTheDefault'],
                'options' => [],
                'result' => [true, ['fieldOne' => 'notTheDefault'], null, []],
            ],
            'Required Fail' => [
                'spec' => ['fieldOne' => ['required' => true]],
                'input' => [],
                'options' => [],
                'result' => [false, null, "Field 'fieldOne' was required and not present", []],
            ],
            'Required Default Pass' => [
                'spec' => ['fieldOne' => []],
                'input' => [],
                'options' => [],
                'result' => [true, [], null, []],
            ],
            'requiredDefaultFail' => [
                'spec' => ['fieldOne' => []],
                'input' => [],
                'options' => ['defaultRequired' => true],
                'result' => [false, null, "Field 'fieldOne' was required and not present", []],
            ],
            'filterPass' => [
                'spec' => ['fieldOne' => [['floatval']]],
                'input' => ['fieldOne' => '3.14'],
                'options' => [],
                'result' => [true, ['fieldOne' => 3.14], null, []],
            ],
            'filterDefaultShortNamePass' => [
                'spec' => ['fieldOne' => [['float']]],
                'input' => ['fieldOne' => '3.14'],
                'options' => [],
                'result' => [true, ['fieldOne' => 3.14], null, []],
            ],
            'filterFail' => [
                'spec' => ['fieldOne' => [['\TraderInteractive\FiltererTest::failingFilter']]],
                'input' => ['fieldOne' => 'valueOne'],
                'options' => [],
                'result' => [
                    false,
                    null,
                    "Field 'fieldOne' with value 'valueOne' failed filtering, message 'i failed'",
                    [],
                ],
            ],
            'chainPass' => [
                'spec' => ['fieldOne' => [['trim', 'a'], ['floatval']]],
                'input' => ['fieldOne' => 'a3.14'],
                'options' => [],
                'result' => [true, ['fieldOne' => 3.14], null, []],
            ],
            'chainFail' => [
                'spec' => ['fieldOne' => [['trim'], ['\TraderInteractive\FiltererTest::failingFilter']]],
                'input' => ['fieldOne' => 'the value'],
                'options' => [],
                'result' => [
                    false,
                    null,
                    "Field 'fieldOne' with value 'the value' failed filtering, message 'i failed'",
                    [],
                ],
            ],
            'multiInputPass' => [
                'spec' => ['fieldOne' => [['trim']], 'fieldTwo' => [['strtoupper']]],
                'input' => ['fieldOne' => ' value', 'fieldTwo' => 'bob'],
                'options' => [],
                'result' => [true, ['fieldOne' => 'value', 'fieldTwo' => 'BOB'], null, []],
            ],
            'multiInputFail' => [
                'spec' => [
                    'fieldOne' => [['\TraderInteractive\FiltererTest::failingFilter']],
                    'fieldTwo' => [['\TraderInteractive\FiltererTest::failingFilter']],
                ],
                'input' => ['fieldOne' => 'value one', 'fieldTwo' => 'value two'],
                'options' => [],
                'result' => [
                    false,
                    null,
                    "Field 'fieldOne' with value 'value one' failed filtering, message 'i failed'\n"
                    . "Field 'fieldTwo' with value 'value two' failed filtering, message 'i failed'",
                    [],
                ],
            ],
            'emptyFilter' => [
                'spec' => ['fieldOne' => [[]]],
                'input' => ['fieldOne' => 0],
                'options' => [],
                'result' => [true, ['fieldOne' => 0], null, []],
            ],
            'unknownsAllowed' => [
                'spec' => [],
                'input'=> ['fieldTwo' => 0],
                'options' => ['allowUnknowns' => true],
                'result' => [true, [], null, ['fieldTwo' => 0]],
            ],
            'unknownsNotAllowed' => [
                'spec' => [],
                'input' => ['fieldTwo' => 0],
                'options' => [],
                'result' => [false, null, "Field 'fieldTwo' with value '0' is unknown", ['fieldTwo' => 0]],
            ],
            'objectFilter' => [
                'spec' => ['fieldOne' => [[[$this, 'passingFilter']]]],
                'input' => ['fieldOne' => 'foo'],
                'options' => [],
                'result' => [true, ['fieldOne' => 'fooboo'], null, []],
            ],
            'filterWithCustomError' => [
                'spec' => [
                    'fieldOne' => [
                        'error' => 'My custom error message',
                        ['\TraderInteractive\FiltererTest::failingFilter'],
                    ],
                ],
                'input' => ['fieldOne' => 'valueOne'],
                'options' => [],
                'result' => [false, null, 'My custom error message', []],
            ],
            'filterWithCustomErrorContainingValuePlaceholder' => [
                'spec' => [
                    'fieldOne' => [
                        'error' => "The value '{value}' is invalid.",
                        ['uint'],
                    ],
                ],
                'input' => ['fieldOne' => 'abc'],
                'options' => [],
                'result' => [false, null, "The value 'abc' is invalid.", []],
            ],
            'arrayizeAliasIsCalledProperly' => [
                'spec' => ['field' => [['arrayize']]],
                'input' => ['field' => 'a string value'],
                'options' => [],
                'result' => [true, ['field' => ['a string value']], null, []],
            ],
            'concatAliasIsCalledProperly' => [
                'spec' => ['field' => [['concat', '%', '%']]],
                'input' => ['field' => 'value'],
                'options' => [],
                'result' => [true, ['field' => '%value%'], null, []],
            ],
            'translate alias' => [
                'spec' => ['field' => [['translate', ['active' => 'A', 'inactive' => 'I']]]],
                'input' => ['field' => 'inactive'],
                'options' => [],
                'result' => [true, ['field' => 'I'], null, []],
            ],
            'redact alias' => [
                'spec' => ['field' => [['redact', ['other'], '*']]],
                'input' => ['field' => 'one or other'],
                'options' => [],
                'result' => [true, ['field' => 'one or *****'], null, []],
            ],
            'compress-string alias' => [
                'spec' => ['field' => [['compress-string']]],
                'input' => ['field' => ' a  string    with    extra spaces '],
                'options' => [],
                'result' => [true, ['field' => 'a string with extra spaces'], null, []],
            ],
            'compress-string alias include newlines' => [
                'spec' => ['field' => [['compress-string', true]]],
                'input' => ['field' => " a  string\n    with\nnewlines  and    extra spaces\n "],
                'options' => [],
                'result' => [true, ['field' => 'a string with newlines and extra spaces'], null, []],
            ],
            'conflicts with single' => [
                'spec' => [
                    'fieldOne' => [FilterOptions::CONFLICTS_WITH => 'fieldThree', ['string']],
                    'fieldTwo' => [['string']],
                    'fieldThree' => [FilterOptions::CONFLICTS_WITH => 'fieldOne', ['string']],
                ],
                'input' => [
                    'fieldOne' => 'abc',
                    'fieldTwo' => '123',
                    'fieldThree' => 'xyz',
                ],
                'options' => [],
                'result' => [
                    false,
                    null,
                    "Field 'fieldOne' cannot be given if field 'fieldThree' is present.\n"
                    . "Field 'fieldThree' cannot be given if field 'fieldOne' is present.",
                    [],
                ],
            ],
            'conflicts with multiple' => [
                'spec' => [
                    'fieldOne' => [FilterOptions::CONFLICTS_WITH => ['fieldTwo', 'fieldThree'], ['string']],
                    'fieldTwo' => [['string']],
                    'fieldThree' => [['string']],
                ],
                'input' => [
                    'fieldOne' => 'abc',
                    'fieldTwo' => '123',
                ],
                'options' => [],
                'result' => [
                    false,
                    null,
                    "Field 'fieldOne' cannot be given if field 'fieldTwo' is present.",
                    [],
                ],
            ],
            'conflicts with not present' => [
                'spec' => [
                    'fieldOne' => [FilterOptions::CONFLICTS_WITH => 'fieldThree', ['string']],
                    'fieldTwo' => [['string']],
                ],
                'input' => [
                    'fieldOne' => 'abc',
                    'fieldTwo' => '123',
                ],
                'options' => [],
                'result' => [
                    true,
                    [
                        'fieldOne' => 'abc',
                        'fieldTwo' => '123',
                    ],
                    null,
                    [],
                ],
            ],
            'uses' => [
                'spec' => [
                    'fieldOne' => [['uint']],
                    'fieldTwo' => [
                        ['uint'],
                        [
                            FilterOptions::USES => ['fieldOne'],
                            function (int $input, int $fieldOneValue) : int {
                                return $input * $fieldOneValue;
                            },
                        ],
                    ],
                ],
                'input' => [
                    'fieldOne' => '5',
                    'fieldTwo' => '2',
                ],
                'options' => [],
                'result' => [
                    true,
                    [
                        'fieldOne' => 5,
                        'fieldTwo' => 10,
                    ],
                    null,
                    [],
                ],
            ],
            'input order does not matter for uses' => [
                'spec' => [
                    'fieldOne' => [['uint']],
                    'fieldTwo' => [
                        ['uint'],
                        [
                            FilterOptions::USES => ['fieldOne'],
                            function (int $input, int $fieldOneValue) : int {
                                return $input * $fieldOneValue;
                            },
                        ],
                    ],
                ],
                'input' => [
                    'fieldTwo' => '2',
                    'fieldOne' => '5',
                ],
                'options' => [],
                'result' => [
                    true,
                    [
                        'fieldOne' => 5,
                        'fieldTwo' => 10,
                    ],
                    null,
                    [],
                ],
            ],
            'uses missing field' => [
                'spec' => [
                    'fieldOne' => [['uint']],
                    'fieldTwo' => [
                        ['uint'],
                        [
                            FilterOptions::USES => ['fieldOne'],
                            function (int $input, int $fieldOneValue) : int {
                                return $input * $fieldOneValue;
                            },
                        ],
                    ],
                ],
                'input' => [
                    'fieldTwo' => '2',
                ],
                'options' => [],
                'result' => [
                    false,
                    null,
                    "Field 'fieldTwo' with value '2' failed filtering, message 'fieldTwo uses fieldOne but fieldOne was"
                    . " not given.'",
                    [],
                ],
            ],
            'returnOnNull filter option' => [
                'spec' => ['field' => [FilterOptions::RETURN_ON_NULL => true, ['string', true], ['string']]],
                'input' => ['field' => null],
                'options' => [],
                'result' => [true, ['field' => null], null, []],
            ],
            'phone alias' => [
                'spec' => ['field' => [['phone']]],
                'input' => ['field' => '(234) 567 8901'],
                'options' => [],
                'result' => [true, ['field' => '2345678901'], null, []],
            ],
            'json alias' => [
                'spec' => ['field' => [['json']]],
                'input' => ['field' => '{"foo": "bar"}'],
                'options' => [],
                'result' => [true, ['field' => '{"foo": "bar"}'], null, []],
            ],
            'json-decode alias' => [
                'spec' => ['field' => [['json-decode']]],
                'input' => ['field' => '{"foo": "bar"}'],
                'options' => [],
                'result' => [true, ['field' => ['foo' => 'bar']], null, []],
            ],
            'xml alias' => [
                'spec' => ['field' => [['xml']]],
                'input' => ['field' => self::FULL_XML],
                'options' => [],
                'result' => [true, ['field' => self::FULL_XML], null, []],
            ],
            'xml-validate alias' => [
                'spec' => ['field' => [['xml-validate', __DIR__ . '/_files/books.xsd']]],
                'input' => ['field' => self::FULL_XML],
                'options' => [],
                'result' => [true, ['field' => self::FULL_XML], null, []],
            ],
            'xml-extract alias' => [
                'spec' => ['field' => [['xml-extract', "/books/book[@id='bk101']"]]],
                'input' => ['field' => self::FULL_XML],
                'options' => [],
                'result' => [
                    true,
                    [
                        'field' => (''
                            . '<book id="bk101">'
                                . '<author>Gambardella, Matthew</author>'
                                . "<title>XML Developer's Guide</title>"
                                . '<genre>Computer</genre>'
                                . '<price>44.95</price>'
                                . '<publish_date>2000-10-01</publish_date>'
                                . '<description>An in-depth look at creating applications with XML.</description>'
                            . '</book>'
                        ),
                    ],
                    null,
                    []
                ],
            ],
            'array-copy' => [
                'spec' => [
                    'field' => [['array-copy', ['FOO_VALUE' => 'foo', 'BAR_VALUE' => 'bar']]],
                ],
                'input' => ['field' => ['foo' => 'abc', 'bar' => 123]],
                'options' => [],
                'result' => [
                    true,
                    ['field' => ['FOO_VALUE' => 'abc', 'BAR_VALUE' => 123]],
                    null,
                    [],
                ],
            ],
            'array-copy-each' => [
                'spec' => [
                    'field' => [['array-copy-each', ['FOO_VALUE' => 'foo', 'BAR_VALUE' => 'bar']]],
                ],
                'input' => [
                    'field' => [
                        ['foo' => 'abc', 'bar' => 123],
                        ['foo' => 'xyz', 'bar' => 789],
                    ],
                ],
                'options' => [],
                'result' => [
                    true,
                    [
                        'field' => [
                            ['FOO_VALUE' => 'abc', 'BAR_VALUE' => 123],
                            ['FOO_VALUE' => 'xyz', 'BAR_VALUE' => 789],
                        ],
                    ],
                    null,
                    [],
                ],
            ],
            'array-pad' => [
                'spec' => [
                    'field' => [['array-pad', 5, 0, Arrays::ARRAY_PAD_FRONT]],
                ],
                'input' => [
                    'field' => ['a', 'b', 'c'],
                ],
                'options' => [],
                'result' => [
                    true,
                    [
                        'field' => [0, 0, 'a', 'b', 'c'],
                    ],
                    null,
                    [],
                ],
            ],
            'time-of-day' => [
                'spec' => [
                    'field' => [['time-of-day']],
                ],
                'input' => [
                    'field' => '23:59:59',
                ],
                'options' => [],
                'result' => [
                    true,
                    [
                        'field' => '23:59:59',
                    ],
                    null,
                    [],
                ],
            ],
            'implode' => [
                'spec' => [
                    'field' => [['array'], ['implode', ',']],
                ],
                'input' => [
                    'field' => ['one', 'two', 'three'],
                ],
                'options' => [],
                'result' => [
                    true,
                    [
                        'field' => 'one,two,three',
                    ],
                    null,
                    [],
                ],
            ],
            'uuid' => [
                'spec' => [
                    'field' => [['uuid', false, false, [4]]],
                ],
                'input' => [
                    'field' => '2c02b87a-97ec-4de0-8c50-6721a29c150f',
                ],
                'options' => [],
                'result' => [
                    true,
                    [
                        'field' => '2c02b87a-97ec-4de0-8c50-6721a29c150f',
                    ],
                    null,
                    [],
                ],
            ],
            'strip-emoji' => [
                'spec' => [
                    'field' => [['strip-emoji']],
                ],
                'input' => [
                    'field' => 'This 💩 text contains 😞 multiple emoji 🍔 characters 🍚. As well as an alphanumeric '
                    . 'supplement 🆗 and flag 🚩',
                ],
                'options' => [],
                'result' => [
                    true,
                    [
                        'field' => 'This  text contains  multiple emoji  characters . As well as an alphanumeric '
                        . 'supplement  and flag ',
                    ],
                    null,
                    [],
                ],
            ],
        ];
    }

    /**
     * @test
     * @covers ::execute
     */
    public function executeThrowsOnError()
    {
        $exception = new RuntimeException('the error');
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage($exception->getMessage());
        $filter = function () use ($exception) {
            throw $exception;
        };

        $specification = [
            'id' => [
                FilterOptions::THROW_ON_ERROR => true,
                [$filter],
            ],
        ];
        $filterer = new Filterer($specification);
        $filterer->execute(['id' => 1]);
    }

    /**
     * @test
     * @covers ::execute
     */
    public function executeValidatesThrowsOnError()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            sprintf(Filterer::INVALID_BOOLEAN_FILTER_OPTION, FilterOptions::THROW_ON_ERROR, 'id')
        );
        $specification = [
            'id' => [
                FilterOptions::THROW_ON_ERROR => 'abc',
                ['uint'],
            ],
        ];
        $filterer = new Filterer($specification);
        $filterer->execute(['id' => 1]);
    }

    /**
     * @test
     * @covers ::execute
     */
    public function executeValidatesReturnOnNull()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            sprintf(Filterer::INVALID_BOOLEAN_FILTER_OPTION, FilterOptions::RETURN_ON_NULL, 'id')
        );
        $specification = [
            'id' => [
                FilterOptions::RETURN_ON_NULL => 'abc',
                ['uint'],
            ],
        ];
        $filterer = new Filterer($specification);
        $filterer->execute(['id' => 1]);
    }

    /**
     * @test
     * @covers ::filter
     * @covers ::execute
     */
    public function filterReturnsResponseType()
    {
        $specification = ['id' => [['uint']]];
        $input = ['id' => 1];
        $options = ['responseType' => Filterer::RESPONSE_TYPE_FILTER];

        $result = Filterer::filter($specification, $input, $options);

        $this->assertInstanceOf(FilterResponse::class, $result);
        $this->assertSame(true, $result->success);
        $this->assertSame($input, $result->filteredValue);
        $this->assertSame([], $result->errors);
        $this->assertSame(null, $result->errorMessage);
        $this->assertSame([], $result->unknowns);
    }

    /**
     * @test
     * @covers ::filter
     * @covers ::execute
     */
    public function filterReturnsResponseTypeWithErrors()
    {
        $specification = ['name' => [['string']]];
        $input = ['name' => 'foo', 'id' => 1];
        $options = ['responseType' => Filterer::RESPONSE_TYPE_FILTER];

        $result = Filterer::filter($specification, $input, $options);

        $this->assertInstanceOf(FilterResponse::class, $result);
        $this->assertSame(false, $result->success);
        $this->assertSame(['name' => 'foo'], $result->filteredValue);
        $this->assertSame(['id' => "Field 'id' with value '1' is unknown"], $result->errors);
        $this->assertSame("Field 'id' with value '1' is unknown", $result->errorMessage);
        $this->assertSame(['id' => 1], $result->unknowns);
    }

    /**
     * @test
     * @covers ::filter
     * @covers ::execute
     * @covers ::setFilterAliases
     */
    public function filterCustomShortNamePass()
    {
        Filterer::setFilterAliases(['fval' => 'floatval']);
        $result = Filterer::filter(['fieldOne' => [['fval']]], ['fieldOne' => '3.14']);
        $this->assertSame([true, ['fieldOne' => 3.14], null, []], $result);
    }

    /**
     * @test
     * @covers ::filter
     * @covers ::execute
     * @covers ::setFilterAliases
     * @covers ::getFilterAliases
     */
    public function filterGetSetKnownFilters()
    {
        $knownFilters = ['lower' => 'strtolower', 'upper' => 'strtoupper'];
        Filterer::setFilterAliases($knownFilters);
        $this->assertSame($knownFilters, Filterer::getFilterAliases());
    }

    /**
     * @test
     * @covers ::setFilterAliases
     */
    public function setBadFilterAliases()
    {
        $originalAliases = Filterer::getFilterAliases();

        $actualThrowable = null;
        try {
            Filterer::setFilterAliases(['foo' => 'not callable']);
        } catch (Throwable $throwable) {
            $actualThrowable = $throwable;
        }

        $this->assertSame($originalAliases, Filterer::getFilterAliases());
        $this->assertInstanceOf(TypeError::class, $actualThrowable);
    }

    /**
     * @test
     * @covers ::filter
     * @covers ::execute
     */
    public function notCallable()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Function 'boo' for field 'foo' is not callable");
        Filterer::filter(['foo' => [['boo']]], ['foo' => 0]);
    }

    /**
     * @test
     * @covers ::filter
     * @covers ::execute
     */
    public function allowUnknownsNotBool()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("'allowUnknowns' option was not a bool");
        Filterer::filter([], [], ['allowUnknowns' => 1]);
    }

    /**
     * @test
     * @covers ::filter
     * @covers ::execute
     */
    public function defaultRequiredNotBool()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("'defaultRequired' option was not a bool");
        Filterer::filter([], [], ['defaultRequired' => 1]);
    }

    /**
     * @test
     * @covers ::filter
     * @covers ::execute
     */
    public function filterThrowsExceptionOnInvalidResponseType()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("'responseType' was not a recognized value");

        Filterer::filter([], [], ['responseType' => 'invalid']);
    }

    /**
     * @test
     * @covers ::filter
     * @covers ::execute
     */
    public function filtersNotArrayInLeftOverSpec()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("filters for field 'boo' was not a array");
        Filterer::filter(['boo' => 1], []);
    }

    /**
     * @test
     * @covers ::filter
     * @covers ::execute
     */
    public function filtersNotArrayWithInput()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("filters for field 'boo' was not a array");
        Filterer::filter(['boo' => 1], ['boo' => 'notUnderTest']);
    }

    /**
     * @test
     * @covers ::filter
     * @covers ::execute
     */
    public function filterNotArray()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("filter for field 'boo' was not a array");
        Filterer::filter(['boo' => [1]], ['boo' => 'notUnderTest']);
    }

    /**
     * @test
     * @covers ::filter
     * @covers ::execute
     */
    public function requiredNotBool()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("'required' for field 'boo' was not a bool");
        Filterer::filter(['boo' => ['required' => 1]], []);
    }

    /**
     * @test
     * @covers ::registerAlias
     */
    public function registerAliasAliasNotString()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('$alias was not a string or int');
        Filterer::registerAlias(true, 'strtolower');
    }

    /**
     * @test
     * @covers ::registerAlias
     */
    public function registerExistingAliasOverwriteFalse()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Alias 'upper' exists");
        Filterer::setFilterAliases([]);
        Filterer::registerAlias('upper', 'strtoupper');
        Filterer::registerAlias('upper', 'strtoupper', false);
    }

    /**
     * @test
     * @covers ::registerAlias
     */
    public function registerExistingAliasOverwriteTrue()
    {
        Filterer::setFilterAliases(['upper' => 'strtoupper', 'lower' => 'strtolower']);
        Filterer::registerAlias('upper', 'ucfirst', true);
        $this->assertSame(['upper' => 'ucfirst', 'lower' => 'strtolower'], Filterer::getFilterAliases());
    }

    public static function failingFilter()
    {
        throw new Exception('i failed');
    }

    public static function passingFilter($value)
    {
        return $value . 'boo';
    }

    /**
     * Verify behavior of filter() when 'error' is not a string value.
     *
     * @test
     * @covers ::filter
     * @covers ::execute
     *
     * @return void
     */
    public function filterWithNonStringError()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("error for field 'fieldOne' was not a non-empty string");
        Filterer::filter(
            ['fieldOne' => [['strtoupper'], 'error' => new stdClass()]],
            ['fieldOne' => 'valueOne']
        );
    }

    /**
     * Verify behavior of filter() when 'error' is an empty string.
     *
     * @test
     * @covers ::filter
     * @covers ::execute
     *
     * @return void
     */
    public function filterWithEmptyStringError()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("error for field 'fieldOne' was not a non-empty string");
        Filterer::filter(
            ['fieldOne' => [['strtoupper'], 'error' => "\n   \t"]],
            ['fieldOne' => 'valueOne']
        );
    }

    /**
     * @test
     * @covers ::ofScalars
     */
    public function ofScalars()
    {
        $this->assertSame([1, 2], Filterer::ofScalars(['1', '2'], [['uint']]));
    }

    /**
     * @test
     * @covers ::ofScalars
     */
    public function ofScalarsChained()
    {
        $this->assertSame([3.3, 5.5], Filterer::ofScalars(['a3.3', 'a5.5'], [['trim', 'a'], ['floatval']]));
    }

    /**
     * @test
     * @covers ::ofScalars
     */
    public function ofScalarsWithMeaninglessKeys()
    {
        $this->assertSame(['key1' => 1, 'key2' => 2], Filterer::ofScalars(['key1' => '1', 'key2' => '2'], [['uint']]));
    }

    /**
     * @test
     * @covers ::ofScalars
     */
    public function ofScalarsFail()
    {
        try {
            Filterer::ofScalars(['1', [], new stdClass], [['string']]);
            $this->fail();
        } catch (FilterException $e) {
            $expected = <<<TXT
Field '1' with value 'array (
)' failed filtering, message 'Value 'array (
)' is not a string'
Field '2' with value '(object) array(
)' failed filtering, message 'Value '(object) array(
)' is not a string'
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
        $this->assertSame($expected, Filterer::ofArrays([['key' => '1'], ['key' => '2']], ['key' => [['uint']]]));
    }

    /**
     * @test
     * @covers ::ofArrays
     */
    public function ofArraysChained()
    {
        $expected = [['key' => 3.3], ['key' => 5.5]];
        $spec = ['key' => [['trim', 'a'], ['floatval']]];
        $this->assertSame($expected, Filterer::ofArrays([['key' => 'a3.3'], ['key' => 'a5.5']], $spec));
    }

    /**
     * @test
     * @covers ::ofArrays
     */
    public function ofArraysRequiredAndUnknown()
    {
        try {
            Filterer::ofArrays([['key' => '1'], ['key2' => '2']], ['key' => ['required' => true, ['uint']]]);
            $this->fail();
        } catch (Exception $e) {
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
            Filterer::ofArrays(
                [['key' => new stdClass], ['key' => []], ['key' => null], 'key'],
                ['key' => [['string']]]
            );
            $this->fail();
        } catch (FilterException $e) {
            $expected = <<<TXT
Field 'key' with value '(object) array(
)' failed filtering, message 'Value '(object) array(
)' is not a string'
Field 'key' with value 'array (
)' failed filtering, message 'Value 'array (
)' is not a string'
Field 'key' with value 'NULL' failed filtering, message 'Value failed filtering, \$allowNull is set to false'
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
        $this->assertSame($expected, Filterer::ofArray(['key1' => '1', 'key2' => '2'], $spec));
    }

    /**
     * @test
     * @covers ::ofArray
     */
    public function ofArrayChained()
    {
        $expected = ['key' => 3.3];
        $spec = ['key' => [['trim', 'a'], ['floatval']]];
        $this->assertSame($expected, Filterer::ofArray(['key' => 'a3.3'], $spec));
    }

    /**
     * @test
     * @covers ::ofArray
     */
    public function ofArrayRequiredSuccess()
    {
        $expected = ['key2' => 2];
        $spec = ['key1' => [['uint']], 'key2' => ['required' => true, ['uint']]];
        $this->assertSame($expected, Filterer::ofArray(['key2' => '2'], $spec));
    }

    /**
     * @test
     * @covers ::ofArray
     */
    public function ofArrayRequiredFail()
    {
        try {
            Filterer::ofArray(['key1' => '1'], ['key1' => [['uint']], 'key2' => ['required' => true, ['uint']]]);
            $this->fail();
        } catch (FilterException $e) {
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
            Filterer::ofArray(['key' => '1'], ['key2' => [['uint']]]);
            $this->fail();
        } catch (FilterException $e) {
            $expected = "Field 'key' with value '1' is unknown";
            $this->assertSame($expected, $e->getMessage());
        }
    }

    /**
     * @test
     * @covers ::getAliases
     */
    public function getAliases()
    {
        $expected = ['some' => 'alias'];

        $filterer = new Filterer([], [], $expected);
        $actual = $filterer->getAliases();

        $this->assertSame($expected, $actual);
    }

    /**
     * @test
     * @covers ::getAliases
     */
    public function getAliasesReturnsStaticValueIfNull()
    {
        $filterer = new Filterer([]);
        $actual = $filterer->getAliases();

        $this->assertSame(Filterer::getFilterAliases(), $actual);
    }

    /**
     * @test
     * @covers ::getSpecification
     */
    public function getSpecification()
    {
        $expected = ['some' => 'specification'];

        $filterer = new Filterer($expected);
        $actual = $filterer->getSpecification();

        $this->assertSame($expected, $actual);
    }

    /**
     * @test
     * @covers ::withAliases
     */
    public function withAliases()
    {
        $expected = ['foo' => 'bar'];

        $filterer = new Filterer([]);
        $filtererCopy = $filterer->withAliases($expected);

        $this->assertNotSame($filterer, $filtererCopy);
        $this->assertSame($expected, $filtererCopy->getAliases());
    }

    /**
     * @test
     * @covers ::withSpecification
     */
    public function withSpecification()
    {
        $expected = ['foo' => 'bar'];

        $filterer = new Filterer([]);
        $filtererCopy = $filterer->withSpecification($expected);

        $this->assertNotSame($filterer, $filtererCopy);
        $this->assertSame($expected, $filtererCopy->getSpecification());
    }
}
