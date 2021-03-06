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

use Symfttpd\Gateway\AbstractGateway;

/**
 * FastCGI Gateway
 *
 * Fastcgi is mainly used with lighttpd. For the moment
 * we don't care about making it working with NGinx.
 *
 * @see issue https://github.com/benja-M-1/symfttpd/issues/38
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
class Fastcgi extends AbstractGateway
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'fastcgi';
    }

    /**
     * {@inheritdoc}
     */
    public function start()
    {
        // Fastcgi is run by Lighttpd we don't need to start a process.
        if (null !== $this->logger) {
            $this->logger->debug("{$this->getName()} started.");
        }
    }

    /**
     * {@inheritdoc}
     */
    public function stop()
    {
        if (null !== $this->logger) {
            $this->logger->debug("{$this->getName()} stopped.");
        }
    }

    /**
     * {@inheritdoccom}
     */
    protected function getCommandLineArguments()
    {
        return array();
    }
}
