<?php
namespace DominionEnterprises\Filter;

/**
 * @coversDefaultClass \DominionEnterprises\Filter\Url
 */
final class UrlTest extends \PHPUnit_Framework_TestCase
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
     * @expectedException Exception
     * @expectedExceptionMessage Value '1' is not a string
     * @covers ::filter
     */
    public function filterNonstring()
    {
        Url::filter(1);
    }

    /**
     * @test
     * @expectedException Exception
     * @expectedExceptionMessage Value 'www.example.com' is not a valid url
     * @covers ::filter
     */
    public function filterNotValid()
    {
        Url::filter('www.example.com');
    }
}
