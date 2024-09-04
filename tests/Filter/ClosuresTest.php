<?php

namespace TraderInteractive\Filter;

use PHPUnit\Framework\TestCase;
use TraderInteractive\Exceptions\FilterException;

/**
 * @coversDefaultClass \TraderInteractive\Filter\Closures
 * @covers ::<private>
 */
final class ClosuresTest extends TestCase
{
    /**
     * @test
     * @covers ::filter
     */
    public function filterAllowNullAndNullValue()
    {
        $result = Closures::filter(null, true);
        $this->assertSame(null, $result);
    }

    /**
     * @test
     * @covers ::filter
     */
    public function filterAllowNullIsFalseAndNullValue()
    {
        $this->expectException(FilterException::class);
        Closures::filter(null, false);
    }

    /**
     * @test
     * @covers ::filter
     */
    public function filterClosure()
    {
        $closureFunction = function () {
            return 'do nothing';
        };
        $result = Closures::filter($closureFunction);
        $this->assertTrue($result instanceof \Closure);
    }

    public static function myCallableFunction()
    {
        return 'do nothing';
    }

    /**
     * @test
     * @covers ::filter
     */
    public function filterCallable()
    {
        $result = Closures::filter(['\\TraderInteractive\\Filter\\ClosuresTest', 'myCallableFunction']);
        $this->assertTrue($result instanceof \Closure);
    }

    /**
     * @test
     * @covers ::filter
     */
    public function filterNotCallableString()
    {
        $this->expectException(FilterException::class);
        Closures::filter('string');
    }

    /**
     * @test
     * @covers ::filter
     */
    public function filterNotCallableInt()
    {
        $this->expectException(FilterException::class);
        Closures::filter(123);
    }
}
