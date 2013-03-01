<?php
/**
 * This file is part of the Symfttpd Project
 *
 * (c) Laurent Bachelier <laurent@bachelier.name>
 * (c) Benjamin Grandfond <benjamin.grandfond@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Symfttpd\Tests;

use Symfttpd\Symfttpd;

/**
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
class SymfttpdTest extends \PHPUnit_Framework_TestCase
{
    public function testShouldRegisterAServer()
    {
        $server = $this->getMock('Symfttpd\Server\ServerInterface');
        $server->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('foo'));

        $symfttpd = new Symfttpd();
        $symfttpd->registerServer($server);

        $this->assertContains('foo', $symfttpd->getServerNames());
    }

    public function testShouldRegisterAGateway()
    {
        $gateway = $this->getMock('Symfttpd\Gateway\GatewayInterface');
        $gateway->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('foo'));

        $symfttpd = new Symfttpd();
        $symfttpd->registerGateway($gateway);

        $this->assertContains('foo', $symfttpd->getGatewayNames());
    }
}
