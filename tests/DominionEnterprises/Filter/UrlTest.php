<?php
namespace DominionEnterprises\Filter;
use DominionEnterprises\Filter\Url as U;

final class UrlTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @covers \DominionEnterprises\Filter\Url::filter
     */
    public function filter()
    {
        $url = 'http://www.example.com';
        $this->assertSame($url, U::filter($url));
    }

    /**
     * @test
     * @expectedException Exception
     * @expectedExceptionMessage Value '1' is not a string
     * @covers \DominionEnterprises\Filter\Url::filter
     */
    public function filter_nonstring()
    {
        U::filter(1);
    }

    /**
     * @test
     * @expectedException Exception
     * @expectedExceptionMessage Value 'www.example.com' is not a valid url
     * @covers \DominionEnterprises\Filter\Url::filter
     */
    public function filter_notValid()
    {
        U::filter('www.example.com');
    }
}
