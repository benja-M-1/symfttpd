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

use Symfttpd\Gateway\GatewayInterface;
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
     * @var array
     */
    protected $servers = array();

    protected $gateways = array();

    public function __construct()
    {
        $this->registerServer(new \Symfttpd\Server\Nginx());
        $this->registerServer(new \Symfttpd\Server\Lighttpd());

        $this->registerGateway(new \Symfttpd\Gateway\Fastcgi());
        $this->registerGateway(new \Symfttpd\Gateway\PhpFpm());
    }

    /**
     * Register a server
     *
     * @param ServerInterface $server
     */
    public function registerServer(ServerInterface $server)
    {
        $this->servers[$server->getName()] = $server;
    }

    /**
     * @param GatewayInterface $gateway
     */
    public function registerGateway(GatewayInterface $gateway)
    {
        $this->gateways[$gateway->getName()] = $gateway;
    }

    /**
     * @param $name
     *
     * @return ServerInterface
     */
    public function getServer($name)
    {
        return $this->servers[$name];
    }

    /**
     * @param $name
     *
     * @return GatewayInterface
     */
    public function getGateway($name)
    {
        return $this->gateways[$name];
    }

    /**
     * @return array
     */
    public function getGatewayNames()
    {
        return array_keys($this->gateways);
    }

    /**
     * @return array
     */
    public function getServerNames()
    {
        return array_keys($this->servers);
    }
}
