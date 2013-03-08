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

use Symfttpd\EventDispatcher\Event\ServerEvent;
use Symfttpd\Server\ServerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfttpd\Generator\ServerConfigurationGenerator;

/**
 * ServerListener description
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
class ServerListener
{
    /**
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    protected $filesystem;

    /**
     * @var \Symfttpd\Generator\ServerConfigurationGenerator
     */
    protected $generator;

    /**
     * @param \Symfony\Component\Filesystem\Filesystem         $filesystem
     * @param \Symfttpd\Generator\ServerConfigurationGenerator $generator
     */
    public function __construct(Filesystem $filesystem, ServerConfigurationGenerator $generator)
    {
        $this->filesystem = $filesystem;
        $this->generator  = $generator;
    }

    /**
     * Creates log directory for the server.
     *
     * @param \Symfttpd\Server\ServerInterface $server
     */
    public function createLogDirectory(ServerInterface $server)
    {
        $paths = array();
        foreach (array($server->getOptions()->get('accessLog'), $server->getOptions()->get('errorLog')) as $path) {
            if (null !== $path && $dirname = dirname($path)) {
                $paths[] = $dirname;
            }
        }

        $this->filesystem->mkdir($paths);
    }

    /**
     * @param \Symfttpd\EventDispatcher\Event\ServerEvent $event
     */
    public function onStart(ServerEvent $event)
    {
        $server = $event->getServer();

        $this->generator->dump($server);
        $this->createLogDirectory($server);
    }
}
