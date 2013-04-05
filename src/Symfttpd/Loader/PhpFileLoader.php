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

namespace Symfttpd\Loader;

use Symfony\Component\Config\Loader\FileLoader;

/**
 * PhpFileLoader description
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
class PhpFileLoader extends FileLoader
{
    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function load($resource, $type = null)
    {
        $options = array();
        $file = $this->locator->locate($resource);

        require $file;

        return $options;
    }

    /**
     * Returns true if this class supports the given resource.
     *
     * @param mixed  $resource A resource
     * @param string $type     The resource type
     *
     * @return Boolean true if this class supports the given resource, false otherwise
     */
    public function supports($resource, $type = null)
    {
        return is_string($resource) && 'php' == pathinfo($resource, PATHINFO_EXTENSION);
    }

}
