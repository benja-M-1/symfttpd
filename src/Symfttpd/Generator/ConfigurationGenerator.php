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

use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * ConfigurationGenerator generates and dumps the configuration
 * generated with twig.
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
class ConfigurationGenerator implements ConfigurationGeneratorInterface
{
    /**
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    protected $filesystem;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var string
     */
    protected $path;

    /**
     * @param \Twig_Environment                        $twig
     * @param \Symfony\Component\Filesystem\Filesystem $filesystem
     * @param \Psr\Log\LoggerInterface                 $logger
     */
    public function __construct(\Twig_Environment $twig, Filesystem $filesystem, LoggerInterface $logger = null)
    {
        $this->twig       = $twig;
        $this->filesystem = $filesystem;
        $this->logger     = $logger;
    }

    /**
     * @param string $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * {@inheritdoc}
     */
    public function dump($configuration, $filename, $force = false)
    {
        $file = $this->getPath().'/'.$filename;

        // Don't rewrite existing configuration if not forced to.
        if (false === $force && file_exists($file)) {
            return;
        }

        if (!$this->filesystem->exists($this->getPath())) {
            $this->filesystem->mkdir($this->getPath());
        }

        if (false === file_put_contents($file, $configuration)) {
            throw new \RuntimeException(sprintf('Cannot generate the file "%s".', $this->getPath()));
        }

        return $file;
    }

    /**
     * @param string $template
     * @param array  $parameters
     *
     * @return string
     */
    public function generate($template, array $parameters)
    {
        return $this->twig->render($template, $parameters);
    }
}
