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

namespace Symfttpd\EventDispatcher\Listener;

use Symfttpd\EventDispatcher\Event\GatewayEvent;
use Symfony\Component\Filesystem\Filesystem;
use Symfttpd\Generator\GatewayConfigurationGenerator;

/**
 * GatewayListener
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
class GatewayListener
{
    /**
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    protected $filesystem;

    /**
     * @var \Symfttpd\Generator\GatewayConfigurationGenerator
     */
    protected $generator;

    /**
     * @param \Symfony\Component\Filesystem\Filesystem          $filesystem
     * @param \Symfttpd\Generator\GatewayConfigurationGenerator $generator
     */
    public function __construct(Filesystem $filesystem, GatewayConfigurationGenerator $generator)
    {
        $this->filesystem = $filesystem;
        $this->generator  = $generator;
    }

    /**
     * @param \Symfttpd\EventDispatcher\Event\GatewayEvent $event
     */
    public function onStart(GatewayEvent $event)
    {
        $gateway = $event->getGateway();

        $this->generator->dump($gateway);
        $this->filesystem->touch($gateway->options['socket']);
    }
}
