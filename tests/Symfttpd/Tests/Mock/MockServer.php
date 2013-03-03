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

namespace Symfttpd\Tests\Mock;

use Symfttpd\Tail\TailInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfttpd\Server\AbstractServer as BaseServer;

/**
 * MockServer class
 *
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 */
class MockServer extends BaseServer
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'mock';
    }

    /**
     * Set the command to use.
     *
     * @param $command
     */
    public function setCommand($command)
    {
        $this->command = $command;
    }

    /**
     * Run the server command to start it.
     *
     * @param \Symfttpd\ConfigurationGenerator                  $generator
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param \Symfttpd\Tail\                                   $tail
     *
     * @return mixed
     */
    public function start()
    {
        // TODO: Implement start() method.
    }

    /**
     * Restart the server command to start it.
     *
     * @param \Symfttpd\ConfigurationGenerator                  $generator
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param \Symfttpd\Tail\TailInterface                      $tail
     *
     * @return mixed
     */
    public function restart()
    {
        // TODO: Implement restart() method.
    }

    /**
     * Stop the server.
     *
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return mixed
     */
    public function stop()
    {
        // TODO: Implement stop() method.
    }

    /**
     * Return the command line executed by the process.
     *
     * @return array
     * @throws \RuntimeException
     */
    protected function getCommandLineArguments()
    {
        return array();
    }
}
