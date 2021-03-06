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

namespace Symfttpd\Project;

use Symfttpd\Project\BaseProject;

/**
 * Symfony2 class
 *
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 */
class Symfony2 extends BaseProject
{
    /**
     * Return the project name.
     *
     * @return string
     */
    public function getName()
    {
        return 'symfony';
    }

    /**
     * Return the project version.
     *
     * @return string
     */
    public function getVersion()
    {
        return '2';
    }

    /**
     * Return the cache directory of the project.
     *
     * @return mixed
     */
    public function getCacheDir()
    {
        return $this->rootDir.'/app/cache';
    }

    /**
     * Return the log directory of the project.
     *
     * @return mixed
     */
    public function getLogDir()
    {
        return $this->rootDir.'/app/logs';
    }

    /**
     * Return the web directory of the project.
     *
     * @return mixed
     */
    public function getWebDir()
    {
        return $this->rootDir.'/web';
    }

    /**
     * Return the index file.
     *
     * @return mixed
     */
    public function getIndexFile()
    {
        return 'app.php';
    }

    /**
     * Return default executables files of a Symfony project.
     *
     * @return array
     */
    public function getDefaultExecutableFiles()
    {
        $files = array();

        foreach (array('app_dev.php', $this->getIndexFile()) as $file) {
            if (file_exists($this->getWebDir().DIRECTORY_SEPARATOR.$file)) {
                $files[] = $file;
            }
        }

        return $files ;
    }

    /**
     * Return default readable dirs of a Symfony 2 project:
     * * css
     * * js
     * * bundles
     * * images
     *
     * @return array
     */
    public function getDefaultReadableDirs()
    {
        return array('css', 'js', 'bundles', 'images');
    }

    /**
     * Return default readable files of a Symfony 2 project.
     *
     * @return array
     */
    public function getDefaultReadableFiles()
    {
        return array('favicon.ico', 'apple-touch-icon.png', 'robots.txt');
    }
}
