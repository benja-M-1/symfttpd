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

namespace Symfttpd\Generator;

use Symfttpd\Generator\ConfigurationGenerator;
use Symfttpd\Generator\ConfigurationGeneratorInterface;
use Symfttpd\Server\ServerInterface;

/**
 * ConfigurationGenerator generates and dumps the configuration
 * generated with twig.
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
class ServerConfigurationGenerator
{
    /**
     * @var \Symfttpd\Server\ServerInterface
     */
    protected $server;

    /**
     * @var ConfigurationGenerator
     */
    protected $generator;

    /**
     * @param \Symfttpd\Server\ServerInterface $server
     * @param ConfigurationGeneratorInterface  $generator
     */
    public function __construct(ServerInterface $server, ConfigurationGeneratorInterface $generator)
    {
        $this->server    = $server;
        $this->generator = $generator;
    }

    /**
     * @return string
     */
    public function dump()
    {
        $file = $this->generator->dump($this->generate(), $this->getFilename(), true);

        $this->server->setConfigurationFile($file);
    }

    /**
     * @return string
     */
    public function generate()
    {
        $template   = $this->server->getName().'/'.$this->getFilename().'.twig';
        $parameters = $this->server->getOptions()->all();
        $parameters += array('gateway' => $this->server->getGateway());

        return $this->generator->generate($template, $parameters);
    }

    /**
     * @return string
     */
    protected function getFilename()
    {
        return $this->server->getName() . '.conf';
    }
}
