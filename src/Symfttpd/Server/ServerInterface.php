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

namespace Symfttpd\Server;

use Symfttpd\Options;
use Symfttpd\ProcessAwareInterface;
use Symfttpd\Project\ProjectInterface;

/**
 * ServerInterface
 *
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 */
interface ServerInterface extends ProcessAwareInterface
{
    /**
     * Return the name of the server
     *
     * @return mixed
     */
    public function getName();

    /**
     * Configure the server.
     *
     * @param \Symfttpd\Options                   $config
     * @param \Symfttpd\Project\ProjectInterface $project
     */
    public function configure(Options $config, ProjectInterface $project);

    /**
     * @param      $address
     * @param null $port
     */
    public function bind($address, $port = null);

    /**
     * @return Options
     */
    public function getOptions();

    /**
     * Run the server command to start it.
     *
     * @return mixed
     * @throws \RuntimeException
     */
    public function start();

    /**
     * Stop the server.
     *
     * @return mixed
     */
    public function stop();

    /**
     * Restart the server command to start it.
     *
     * @return mixed
     */
    public function restart();

    /**
     * Return the gateway instance used by the server e.g. php-fpm, fastcgi.
     *
     * @return \Symfttpd\Gateway\GatewayInterface
     */
    public function getGateway();

    /**
     * @return string
     */
    public function getConfigurationFile();

    /**
     * @param string $file
     */
    public function setConfigurationFile($file);
}
