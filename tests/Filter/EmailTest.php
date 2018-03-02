<?php

namespace TraderInteractive\Filter;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \TraderInteractive\Filter\Email
 */
final class EmailTest extends TestCase
{
    /**
     * @test
     * @covers ::filter
     */
    public function filter()
    {
        $email = 'first.last@email.com';
        $this->assertSame($email, Email::filter($email));
    }

    /**
     * @test
     * @expectedException Exception
     * @expectedExceptionMessage Value '1' is not a string
     * @covers ::filter
     */
    public function filterNonstring()
    {
        Email::filter(1);
    }

    /**
     * @test
     * @expectedException Exception
     * @expectedExceptionMessage Value '@email.com' is not a valid email
     * @covers ::filter
     */
    public function filterNotValid()
    {
        Email::filter('@email.com');
    }
}
