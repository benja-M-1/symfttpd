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
     * @param \Symfttpd\Gateway\GatewayInterface $gateway
     */
    public function dump(GatewayInterface $gateway)
    {
        $file = $this->generator->dump($this->generate($gateway), $this->getFilename($gateway), true);

        $gateway->setConfigurationFile($file);
    }

    /**
     * @param \Symfttpd\Gateway\GatewayInterface $gateway
     *
     * @return string
     */
    public function generate(GatewayInterface $gateway)
    {
        $template   = $gateway->getName().'/'.$this->getFilename($gateway).'.twig';
        $parameters = $gateway->getOptions()->all();

        return $this->generator->generate($template, $parameters);
    }

    /**
     * @param \Symfttpd\Gateway\GatewayInterface $gateway
     *
     * @return string
     */
    protected function getFilename(GatewayInterface $gateway)
    {
        return $gateway->getName().'.conf';
    }
}
