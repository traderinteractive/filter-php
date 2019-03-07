<?php

namespace TraderInteractive;

use PHPUnit\Framework\TestCase;
use TraderInteractive\Exceptions\FilterException;

/**
 * @coversDefaultClass \TraderInteractive\InvokableFilterer
 * @covers ::__construct
 * @covers ::<private>
 */
final class InvokableFiltererTest extends TestCase
{
    /**
     * @test
     * @covers ::execute
     */
    public function execute()
    {
        $expected = new FilterResponse(['some' => 'response']);
        $mock = $this->getMockFilterer();
        $mock->method('execute')->willReturn($expected);

        $filterer = new InvokableFilterer($mock);
        $actual = $filterer->execute([]);

        $this->assertSame($expected, $actual);
    }

    /**
     * @test
     * @covers ::getAliases
     */
    public function getAliases()
    {
        $expected = ['some' => 'alias'];
        $mock = $this->getMockFilterer();
        $mock->method('getAliases')->willReturn($expected);

        $filterer = new InvokableFilterer($mock);
        $actual = $filterer->getAliases();

        $this->assertSame($expected, $actual);
    }

    /**
     * @test
     * @covers ::getSpecification
     */
    public function getSpecification()
    {
        $expected = ['some' => 'specification'];
        $mock = $this->getMockFilterer();
        $mock->method('getSpecification')->willReturn($expected);

        $filterer = new InvokableFilterer($mock);
        $actual = $filterer->getSpecification();

        $this->assertSame($expected, $actual);
    }

    /**
     * @test
     * @covers ::withAliases
     */
    public function withAliases()
    {
        $mock = $this->getMockFilterer();
        $mock->method('withAliases')->willReturnSelf();

        $filterer = new InvokableFilterer($mock);
        $actual = $filterer->withAliases([]);

        $this->assertSame($mock, $actual);
    }

    /**
     * @test
     * @covers ::withSpecification
     */
    public function withSpecification()
    {
        $mock = $this->getMockFilterer();
        $mock->method('withSpecification')->willReturnSelf();

        $filterer = new InvokableFilterer($mock);
        $actual = $filterer->withSpecification([]);

        $this->assertSame($mock, $actual);
    }

    /**
     * @test
     * @covers ::__invoke
     * @dataProvider provideInvoke
     *
     * @param array      $filter
     * @param array      $options
     * @param array|null $filterAliases
     * @param mixed      $value
     * @param array      $expected
     */
    public function invoke(array $filter, array $options, $filterAliases, $value, array $expected)
    {
        $filterer = new InvokableFilterer(new Filterer($filter, $options, $filterAliases));
        $response = $filterer($value);

        $this->assertSame($expected, $response);
    }

    /**
     * @returns array
     */
    public function provideInvoke() : array
    {
        return [
            'empty' => [
                'filter' => [],
                'options' => [],
                'filterAliases' => null,
                'value' => [],
                'expected' => [],
            ],
            'basic use' => [
                'filter' => ['id' => [['uint']]],
                'options' => ['defaultRequired' => true],
                'filterAliases' => null,
                'value' => ['id' => '1'],
                'expected' => ['id' => 1],
            ],
            'with custom alias' => [
                'filter' => ['id' => [['hocuspocus']]],
                'options' => ['defaultRequired' => true],
                'filterAliases' => ['hocuspocus' => 'intval'],
                'value' => ['id' => '1'],
                'expected' => ['id' => 1],
            ],
        ];
    }

    /**
     * @test
     * @covers ::__invoke
     */
    public function invokeThrowsFilterException()
    {
        $filter = ['id' => ['required' => true]];
        $options = [];
        $value = [];

        $this->expectException(FilterException::class);
        $this->expectExceptionMessage("Field 'id' was required and not present");

        $filterer = new InvokableFilterer(new Filterer($filter, $options));
        $filterer($value);
    }

    /**
     * @return FiltererInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private function getMockFilterer()
    {
        return $this->getMockBuilder(FiltererInterface::class)->getMock();
    }
}
