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

use Symfttpd\ConfigurationGenerator;

/**
 * Nginx description
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
class Nginx extends Server
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'nginx';
    }

    /**
     * @param ConfigurationGenerator $generator
     *
     * @return array
     * @throws \RuntimeException
     */
    protected function getCommandLineArguments(ConfigurationGenerator $generator)
    {
        return array($this->options['executable'], '-c', $generator->dump($this, true));
    }
}
