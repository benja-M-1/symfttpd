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

/**
 * Lighttpd server
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
class Lighttpd extends AbstractServer
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'lighttpd';
    }

    /**
     * {@inheritdoc}
     */
    protected function getCommandLineArguments()
    {
        return array($this->options['executable'], '-f', $this->getConfigurationFile());
    }
}
