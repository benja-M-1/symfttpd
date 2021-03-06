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

namespace Symfttpd\Tests;

use Symfttpd\SymfttpdFile;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
class SymfttpdFileTest extends \PHPUnit_Framework_TestCase
{
    public
        $file,
        $filesystem;

    public function setUp()
    {
        $processor = $this->getMock('\Symfony\Component\Config\Definition\Processor');
        $processor->expects($this->any())
            ->method('processConfiguration')
            ->will($this->returnValue(array()));

        $configuration = $this->getMock('\Symfony\Component\Config\Definition\ConfigurationInterface');

        $this->file = new SymfttpdFile();
        $this->file->setProcessor($processor);
        $this->file->setConfiguration($configuration);

        $this->filesystem = new Filesystem();
        $this->filesystem->touch(sys_get_temp_dir().'/symfttpd.conf.php');
    }

    public function tearDown()
    {
        $this->filesystem->remove(sys_get_temp_dir().'/symfttpd.conf.php');
    }

    public function testShouldAddPath()
    {
        $dir = sys_get_temp_dir().'/symfttpd.conf.php';

        $this->file->addPath($dir);
        $this->assertContains($dir, $this->file->getFilePaths());
    }

    /**
     * @expectedException \Symfttpd\Exception\FileNotFoundException
     */
    public function testAddingNotExistingPathShouldThrowException()
    {
        $dir = '/foo/bar';
        $this->file->addPath($dir);
    }

    public function testShouldReadTheFile()
    {
        $configuration = $this->file->read();
        $this->assertInternalType('array', $configuration);
    }

    public function testShouldWriteTheFile()
    {
        $file = new SymfttpdFile();
        $file->write(array('foo' => 'bar'));

        $this->assertFileExists($file->getDefaultFilePath());
        $this->assertEquals(<<<PHP
<?php

\$options = array (
  'foo' => 'bar',
);
PHP
        , file_get_contents($file->getDefaultFilePath()));

        unlink($file->getDefaultFilePath());
    }
}
