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
use Symfttpd\Server\ServerInterface;

/**
 * ServerEvent
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
class ServerEvent extends Event
{
    /**
     * @var \Symfttpd\Server\ServerInterface
     */
    protected $server;

    /**
     * @param \Symfttpd\Server\ServerInterface $server
     */
    public function __construct(ServerInterface $server)
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
}
