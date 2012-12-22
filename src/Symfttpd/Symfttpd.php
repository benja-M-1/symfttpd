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

namespace Symfttpd;

use Symfony\Component\Process\ExecutableFinder;
use Symfttpd\Config;
use Symfttpd\Project\ProjectInterface;
use Symfttpd\Server\ServerInterface;

/**
 * Symfttpd class
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
class Symfttpd
{
    const VERSION = '@package_version@';

    /**
     * @var \Symfttpd\Config
     */
    protected $config;

    /**
     * @var \Symfttpd\Project\ProjectInterface
     */
    protected $project;

    /**
     * @var \Symfttpd\Server\ServerInterface
     */
    protected $server;

    /**
     * @var \Symfttpd\ConfigurationGenerator
     */
    protected $generator;

    /**
     * @param \Symfttpd\Config $config
     */
    public function setConfig(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @return \Symfttpd\Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param \Symfttpd\Project\ProjectInterface $project
     */
    public function setProject(ProjectInterface $project)
    {
        $this->project = $project;
    }

    /**
     * @return \Symfttpd\Project\ProjectInterface
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * @param \Symfttpd\Server\ServerInterface $server
     */
    public function setServer(ServerInterface $server)
    {
        $this->server = $server;
    }

    /**
     * @return \Symfttpd\Server\ServerInterface
     */
    public function getServer()
    {
        return $this->server;
    }

    /**
     * @param \Symfttpd\ConfigurationGenerator
     */
    public function setGenerator(ConfigurationGenerator $configurationFile)
    {
        $this->generator = $configurationFile;
    }

    /**
     * @return \Symfttpd\ConfigurationGenerator
     */
    public function getGenerator()
    {
        return $this->generator;
    }

    /**
     * Find executables.
     */
    public function findExecutables()
    {
        $this->findPhpCgiCmd();
    }

    /**
     * Set the php-cgi command value in the Symfttpd option
     * if it is not already set.
     *
     * @throws \Symfttpd\Exception\ExecutableNotFoundException
     */
    protected function findPhpCgiCmd()
    {
        if (false === $this->getConfig()->has('php_cgi_cmd')) {
            $exeFinder = new ExecutableFinder();
            $exeFinder->addSuffix('');
            $cmd = $exeFinder->find('php-cgi');

            if (false == (boolean) $cmd) {
                throw new \Symfttpd\Exception\ExecutableNotFoundException('php-cgi executable not found.');
            }

            $this->getConfig()->set('php_cgi_cmd', $cmd);
        }
    }
}