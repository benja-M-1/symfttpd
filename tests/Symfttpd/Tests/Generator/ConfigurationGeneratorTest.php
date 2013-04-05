<?php
/**
 * This generator is part of the Symfttpd Project
 *
 * (c) Laurent Bachelier <laurent@bachelier.name>
 * (c) Benjamin Grandfond <benjamin.grandfond@gmail.com>
 *
 * This source generator is subject to the MIT license that is bundled
 * with this source code in the generator LICENSE.
 */

namespace Symfttp\Tests\Generator\ConfigurationGenerator;

use Symfttpd\Generator\ConfigurationGenerator;

/**
 * PhpFpmFileTest description
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
class ConfigurationGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    public $path;

    /**
     * @var ConfigurationGenerator
     */
    public $generator;

    /**
     * @var Twig_Environment
     */
    public $twig;

    /**
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    public $filesystem;

    public function setUp()
    {
        $this->path = sys_get_temp_dir().'/symfttpd';

        if (!is_dir($this->path)) {
            mkdir($this->path);
        }

        $this->twig = $this->getMock('\Twig_Environment');
        $this->filesystem = new \Symfony\Component\Filesystem\Filesystem();
        $this->generator = new ConfigurationGenerator($this->twig, $this->filesystem);
    }

    public function tearDown()
    {
        $this->filesystem->remove($this->path);
    }

    /**
     * @testdox should dump the configuration
     */
    public function testDump()
    {
        $this->generator->setPath($this->path);

        $this->generator->dump('foo', 'foo', true);

        $this->assertTrue(file_exists($this->generator->getPath()));
    }

    /**
     * @testdox should generate the configuration
     */
    public function testGenerate()
    {
        $name = 'bar';

        $this->twig->expects($this->once())
            ->method('render')
            ->with($this->equalTo($name.'/'.$name.'.conf.twig'), $this->isType('array'))
            ->will($this->returnValue('foo'));

        $configuration = $this->generator->generate($name.'/'.$name.'.conf.twig', array());

        $this->assertFalse(empty($configuration));
        $this->assertEquals('foo', $configuration);
    }
}
