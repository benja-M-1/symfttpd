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

namespace Symfttpd\Gateway;

use Symfttpd\EventDispatcher\Event\GatewayEvent;
use Symfttpd\Gateway\AbstractGateway;

/**
 * PHP FPM gateway
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
class PhpFpm extends AbstractGateway
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'php-fpm';
    }

    /**
     * {@inheritdoc}
     */
    protected function getCommandLineArguments()
    {
        return array($this->options['executable'], '-y', $this->getConfigurationFile());
    }

    /**
     * {@inheritdoc}
     */
    public function start()
    {
        $this->dispatcher->dispatch('gateway.pre_start', new GatewayEvent($this));

        $process = $this->getProcessBuilder()
            ->setArguments($this->getCommandLineArguments())
            ->getProcess();

        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException($process->getErrorOutput());
        }

        if (null !== $this->logger) {
            $this->logger->debug("{$this->getName()} started.");
        }

        $this->dispatcher->dispatch('gateway.post_start', new GatewayEvent($this));
    }

    /**
     * {@inheritdoc}
     */
    public function stop()
    {
        \Symfttpd\Utils\PosixTools::killPid($this->options['pidfile']);

        if (null !== $this->logger) {
            $this->logger->debug("{$this->getName()} stopped.");
        }
    }
}
