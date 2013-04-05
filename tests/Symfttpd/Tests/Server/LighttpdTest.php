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

namespace Symfttpd\Tests\Server;

use Symfttpd\Server\Lighttpd;

/**
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 */
class LighttpdTest extends \PHPUnit_Framework_TestCase
{
    public function testShouldStartLighttpd()
    {
        $server = new Lighttpd($this->getMock('Symfony\Component\EventDispatcher\EventDispatcher'));
        $server->setProcessBuilder($this->getProcessBuilderMock());
        $server->start();
    }

    private function getProcessBuilderMock()
    {
        $processBuilder = $this->getMock('\Symfony\Component\Process\ProcessBuilder');
        $processBuilder->expects($this->once())
            ->method('setArguments')
            ->with($this->isType('array'))
            ->will($this->returnSelf());
        $processBuilder->expects($this->once())
            ->method('getProcess')
            ->will($this->returnValue($this->getProcessMock()));

        return $processBuilder;
    }

    private function getProcessMock()
    {
        $process = $this->getMock('\Symfony\Component\Process\Process', array(), array(null));
        $process->expects($this->once())
            ->method('run')
            ->will($this->returnValue(0));
        $process->expects($this->once())
            ->method('isSuccessful')
            ->will($this->returnValue(true));

        return $process;
    }
}
