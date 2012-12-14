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

use Symfttpd\Config;
use Symfttpd\Factory;

/**
 * FactoryTest description
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
class FactoryTest extends \PHPUnit_Framework_TestCase
{
    public $factory;

    public function setUp()
    {
        $execFinder = $this->getMock('\Symfony\Component\Process\ExecutableFinder');

        $execFinder->expects($this->any())
            ->method('find')
            ->will($this->returnValue('/foo/lighttpd'));

        $guesser = $this->getMock('\Symfttpd\Guesser\ProjectGuesser');

        $this->factory = new Factory($execFinder, $guesser);
    }

    /**
     * @covers \Symfttpd\Factory::create
     * @covers \Symfttpd\Factory::createSymfttpd
     * @covers \Symfttpd\Factory::createConfig
     * @covers \Symfttpd\Factory::createProject
     * @covers \Symfttpd\Factory::createServerConfiguration
     * @covers \Symfttpd\Symfttpd::setServerConfiguration
     * @covers \Symfttpd\Symfttpd::getServerConfiguration
     */
    public function testCreate()
    {
        $symfttpd = $this->factory->create(array('project_type' => 'php', 'project_version' => null));
        $config   = $symfttpd->getConfig();
        $project  = $symfttpd->getProject();
        $server   = $symfttpd->getServer();
        $generator = $symfttpd->getServerConfigurationFile();

        $this->assertInstanceOf('\\Symfttpd\\Symfttpd', $symfttpd);
        $this->assertInstanceOf('\\Symfttpd\\Config', $config);
        $this->assertInstanceOf('\\Symfttpd\\Project\\ProjectInterface', $project);
        $this->assertInstanceOf('\\Symfttpd\\Server\\ServerInterface', $server);
        $this->assertInstanceOf('\\Symfttpd\\ConfigurationFile\\ConfigurationFileInterface', $generator);
    }

    public function testCreateProject()
    {
        $config = new Config(array('project_type' => 'symfony', 'project_version' => '1.4'));

        $project = $this->factory->createProject($config);

        $this->assertInstanceOf('\\Symfttpd\\Project\\Symfony1', $project);
        $this->assertEquals('1', $project->getVersion());
        $this->assertEquals('symfony', $project->getName());
    }

    /**
     * @dataProvider getInvalidConfig
     * @param $config
     */
    public function testCreateProjectException($config, $exception, $exceptionMessage)
    {
        $this->setExpectedException($exception, $exceptionMessage);
        $this->factory->createProject($config);
    }

    public function getInvalidConfig()
    {
        return array(
            array(
                'config' => new Config(array('project_type' => 'foo')),
                'exception' => '\\InvalidArgumentException',
                'exceptionMessage' => '"foo" is not supported.'
            ),
            array(
                'config' => new Config(array('project_type' => 'foo', 'project_version' => 3)),
                'exception' => '\\InvalidArgumentException',
                'exceptionMessage' => '"foo" (with version "3") is not supported.'
            )
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage "foo" is not supported
     */
    public function testCreateServerException()
    {
        $config = new Config(array('server_type' => 'foo'));

        $this->factory->createServer(
            $config,
            $this->getMock('\\Symfttpd\\Project\\ProjectInterface')
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage "foo" is not supported
     */
    public function testCreateServerConfigurationFileException()
    {
        $config = new Config(array('server_type' => 'foo'));

        $this->factory->createServerConfigurationFile(
            $config,
            $this->getMock('\\Symfttpd\\Server\\ServerInterface'),
            $this->getMock('\\Symfttpd\\Project\\ProjectInterface')
        );
    }

    /**
     * @dataProvider getServerConfig
     *
     * @param $config
     * @param $expected
     */
    public function testCreateServer($config, $expected)
    {
        $config = new Config($config);

        $project = $this->getMock('\\Symfttpd\\Project\\ProjectInterface');
        $project->expects($this->once())->method('getLogDir')->will($this->returnValue('/tmp'));
        $project->expects($this->once())->method('getCacheDir')->will($this->returnValue('/tmp'));
        $project->expects($this->once())->method('getWebDir')->will($this->returnValue('/web'));
        $project->expects($this->once())->method('getIndexFile')->will($this->returnValue('index.php'));
        $project->expects($this->once())->method('getDefaultExecutableFiles')->will($this->returnValue(array('index.php')));
        $project->expects($this->once())->method('getDefaultReadableDirs')->will($this->returnValue(array()));
        $project->expects($this->once())->method('getDefaultReadableFiles')->will($this->returnValue(array()));

        $server = $this->factory->createServer($config, $project);

        $this->assertEquals($expected, $server->getCommand());
    }

    public function getServerConfig()
    {
        return array(
            array(
                'config' => array('server_cmd' => '/usr/foo/lighttpd'),
                'expected' => '/usr/foo/lighttpd'
            ),
        );
    }
}
