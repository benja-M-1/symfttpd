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
     * @var ConfigurationGenerator
     */
    protected $generator;

    /**
     * @param ConfigurationGeneratorInterface $generator
     */
    public function __construct(ConfigurationGeneratorInterface $generator)
    {
        $this->generator = $generator;
    }

    /**
     * @param \Symfttpd\Server\ServerInterface $server
     */
    public function dump(ServerInterface $server)
    {
        $file = $this->generator->dump($this->generate($server), $this->getFilename($server), true);

        $server->setConfigurationFile($file);
    }

    /**
     * @param \Symfttpd\Server\ServerInterface $server
     *
     * @return string
     */
    public function generate(ServerInterface $server)
    {
        $template   = $server->getName().'/'.$this->getFilename($server).'.twig';
        $parameters = $server->getOptions()->all();
        $parameters += array('gateway' => $server->getGateway());

        return $this->generator->generate($template, $parameters);
    }

    /**
     * @param \Symfttpd\Server\ServerInterface $server
     *
     * @return string
     */
    protected function getFilename(ServerInterface $server)
    {
        return $server->getName() . '.conf';
    }
}
