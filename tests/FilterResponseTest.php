<?php

namespace TraderInteractiveTest;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use TraderInteractive\Exceptions\ReadOnlyViolationException;
use TraderInteractive\FilterResponse;

/**
 * @coversDefaultClass \TraderInteractive\FilterResponse
 */
class FilterResponseTest extends TestCase
{
    /**
     * @test
     * @covers ::__construct
     */
    public function construct()
    {
        $value = ['foo' => 'bar'];
        $errors = [];
        $unknowns = ['other' => 'unknown'];

        $response = new FilterResponse($value, $errors, $unknowns);

        $this->assertSame(true, $response->success);
        $this->assertSame($value, $response->filteredValue);
        $this->assertSame($errors, $response->errors);
        $this->assertSame(null, $response->errorMessage);
        $this->assertSame($unknowns, $response->unknowns);
    }

    /**
     * @test
     * @covers ::__construct
     */
    public function constructWithErrors()
    {
        $value = ['foo' => 'bar'];
        $errors = ['something bad happened', 'and something else too'];
        $unknowns = ['other' => 'unknown'];

        $response = new FilterResponse($value, $errors, $unknowns);

        $this->assertSame(false, $response->success);
        $this->assertSame($value, $response->filteredValue);
        $this->assertSame($errors, $response->errors);
        $this->assertSame("something bad happened\nand something else too", $response->errorMessage);
        $this->assertSame($unknowns, $response->unknowns);
    }

    /**
     * @test
     * @covers ::__construct
     */
    public function constructDefault()
    {
        $input = ['filtered' => 'input'];

        $response = new FilterResponse($input);

        $this->assertSame(true, $response->success);
        $this->assertSame($input, $response->filteredValue);
        $this->assertSame([], $response->errors);
        $this->assertSame(null, $response->errorMessage);
        $this->assertSame([], $response->unknowns);
    }

    /**
     * @test
     * @covers ::__construct
     */
    public function gettingInvalidPropertyThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Property 'foo' does not exist");

        $response = new FilterResponse([]);
        $response->foo;
    }

    /**
     * @test
     * @covers ::__construct
     */
    public function settingValidPropertyThrowsAnException()
    {
        $this->expectException(ReadOnlyViolationException::class);
        $this->expectExceptionMessage("Property 'success' is read-only");

        $response = new FilterResponse([]);
        $response->success = false;
    }

    /**
     * @test
     * @covers ::__construct
     */
    public function settingInvalidPropertyThrowsAnException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Property 'foo' does not exist");

        $response = new FilterResponse([]);
        $response->foo = false;
    }
}
