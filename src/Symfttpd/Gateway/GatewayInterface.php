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

use Symfttpd\Options;
use Symfttpd\ConfigurationGenerator;
use Symfttpd\ProcessAwareInterface;

/**
 * GatewayInterface
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
interface GatewayInterface extends ProcessAwareInterface
{
    /**
     * Return the name of the gateway.
     *
     * @return string
     */
    public function getName();

    /**
     * Configure the gateway with settings of the
     * Symfttpd configuration file.
     *
     * @param \Symfttpd\Options $options
     */
    public function configure(Options $options);

    /**
     * Start the gateway.
     * @param \Symfttpd\ConfigurationGenerator $generator
     *
     * @return mixed
     * @throws \RuntimeException When the gateway failed to start.
     */
    public function start(ConfigurationGenerator $generator);

    /**
     * Stop the gateway.
     *
     * @return mixed
     */
    public function stop();
}
