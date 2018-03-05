<?php

namespace TraderInteractive\Filter;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \TraderInteractive\Filter\Url
 */
final class UrlTest extends TestCase
{
    /**
     * @test
     * @covers ::filter
     */
    public function filter()
    {
        $url = 'http://www.example.com';
        $this->assertSame($url, Url::filter($url));
    }

    /**
     * @test
     * @expectedException \TraderInteractive\Filter\Exception
     * @expectedExceptionMessage Value '1' is not a string
     * @covers ::filter
     */
    public function filterNonString()
    {
        Url::filter(1);
    }

    /**
     * @test
     * @expectedException \TraderInteractive\Filter\Exception
     * @expectedExceptionMessage Value 'www.example.com' is not a valid url
     * @covers ::filter
     */
    public function filterNotValid()
    {
        Url::filter('www.example.com');
    }

    /**
     * @test
     * @covers ::filter
     */
    public function filterNullPass()
    {
        $this->assertSame(null, Url::filter(null, true));
    }

    /**
     * @test
     * @expectedException Exception
     * @expectedExceptionMessage Value 'NULL' is not a string
     * @covers ::filter
     */
    public function filterNullFail()
    {
        Url::filter(null);
    }
}
