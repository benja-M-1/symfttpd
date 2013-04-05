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

namespace Symfttpd\Tests\Debug;

use Symfttpd\Debug\SignalHandler;

/**
 * SignalHandlerTest description
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
class SignalHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testShutdown()
    {
        $server  = $this->getMock('\Symfttpd\Server\ServerInterface');
        $server->expects($this->once())
            ->method('stop');

        $handler = new SignalHandler($server);
        $handler->shutdown();
    }
}
