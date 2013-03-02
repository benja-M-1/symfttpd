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
use Symfttpd\Gateway\GatewayInterface;

/**
 * ConfigurationGenerator generates and dumps the configuration
 * generated with twig.
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
class GatewayConfigurationGenerator
{
    /**
     * @var \Symfttpd\Gateway\GatewayInterface
     */
    protected $gateway;

    /**
     * @var ConfigurationGenerator
     */
    protected $generator;

    /**
     * @param \Symfttpd\Gateway\GatewayInterface $gateway
     * @param ConfigurationGeneratorInterface  $generator
     */
    public function __construct(GatewayInterface $gateway, ConfigurationGeneratorInterface $generator)
    {
        $this->gateway   = $gateway;
        $this->generator = $generator;
    }

    /**
     * @return string
     */
    public function dump()
    {
        $filename   = $this->gateway->getName().'.conf';
        $template   = $this->gateway->getName().'/'.$filename.'.twig';
        $parameters = $this->gateway->getOptions()->all();

        $configuration = $this->generator->generate($template, $parameters);

        $this->gateway->setConfigurationFile($this->generator->dump($configuration, $filename, true));
    }
}
