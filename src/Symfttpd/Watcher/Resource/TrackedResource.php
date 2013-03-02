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

namespace Symfttpd\Watcher\Resource;

/**
 * TrackedResource description
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
class TrackedResource implements ResourceInterface
{
    /**
     * @var string
     */
    protected $resource;

    /**
     * @var \DateTime The unix timestamp when the resource has been tracked
     */
    protected $updatedAt;

    /**
     * @param string $resource The filesystem resource
     */
    public function __construct($resource)
    {
        $this->resource  = new \SplFileInfo(realpath($resource));

        if (!$this->resource->isReadable()) {
            throw new \Symfttpd\Exception\FileNotFoundException("$resource does not exist, Symfttpd can't track its changes.");
        }

        $this->updatedAt = $this->resource->getMTime();
    }

    /**
     * {@inheritdoc}
     */
    public function getResource()
    {
        return $this->resource->getRealPath();
    }

    /**
     * {@inheridoc}
     */
    public function hasChanged()
    {
        if ($this->resource->getMTime() > $this->updatedAt) {
            $this->updatedAt = $this->resource->getMTime();

            return true;
        }

        return false;
    }
}
