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

/**
 * ConfigurationGeneratorInterface generates and dumps a configuration
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
interface ConfigurationGeneratorInterface
{
    /**
     * @param string $configuration
     * @param string $filename
     * @param bool   $force
     *
     * @return string The full path to the generated file
     * @throw \RuntimeException
     */
    public function dump($configuration, $filename, $force = false);

    /**
     * @param string $template
     * @param array  $parameters
     *
     * @return string
     */
    public function generate($template, array $parameters);
}
