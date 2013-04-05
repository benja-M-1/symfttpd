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

namespace Symfttpd\EventDispatcher\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfttpd\Gateway\GatewayInterface;

/**
 * GatewayEvent
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
class GatewayEvent extends Event
{
    /**
     * @var \Symfttpd\Gateway\GatewayInterface
     */
    protected $gateway;

    /**
     * @param \Symfttpd\Gateway\GatewayInterface $gateway
     */
    public function __construct(GatewayInterface $gateway)
    {
        $this->gateway = $gateway;
    }

    /**
     * @return \Symfttpd\Gateway\GatewayInterface
     */
    public function getGateway()
    {
        return $this->gateway;
    }
}
