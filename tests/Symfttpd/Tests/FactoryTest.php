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
    /**
     * @var \Symfttpd\Factory
     */
    public $factory;

    /**
     * @var \Symfttpd\Guesser\ProjectGuesser
     */
    public $guesser;

    /**
     * @var \Symfony\Component\Process\ExecutableFinder
     */
    public $execFinder;

    public function setUp()
    {
        $this->execFinder = $this->getMock('\Symfony\Component\Process\ExecutableFinder');

        $this->guesser = $this->getMock('\Symfttpd\Guesser\ProjectGuesser');

        $this->factory = new Factory($this->execFinder, $this->guesser);
    }

    /**
     * @covers \Symfttpd\Factory::create
     * @covers \Symfttpd\Factory::createSymfttpd
     * @covers \Symfttpd\Factory::createConfig
     * @covers \Symfttpd\Factory::createProject
     * @covers \Symfttpd\Factory::createGenerator
     * @covers \Symfttpd\Symfttpd::setGenerator
     * @covers \Symfttpd\Symfttpd::getGenerator
     */
    public function testCreate()
    {
        $this->execFinder->expects($this->any())
            ->method('find')
            ->will($this->returnValue('/foo/lighttpd'));

        $symfttpd = $this->factory->create(array('project_type' => 'php', 'project_version' => null));
        $config   = $symfttpd->getConfig();
        $project  = $symfttpd->getProject();
        $server   = $symfttpd->getServer();
        $generator = $symfttpd->getGenerator();

        $this->assertInstanceOf('\Symfttpd\Symfttpd', $symfttpd);
        $this->assertInstanceOf('\Symfttpd\Config', $config);
        $this->assertInstanceOf('\Symfttpd\Project\ProjectInterface', $project);
        $this->assertInstanceOf('\Symfttpd\Server\ServerInterface', $server);
        $this->assertInstanceOf('\Symfttpd\ConfigurationGenerator', $generator);
    }

    public function testCreateProject()
    {
        $config = new Config(array('project_type' => 'symfony', 'project_version' => '1.4'));

        $project = $this->factory->createProject($config);

        $this->assertInstanceOf('\Symfttpd\Project\ProjectInterface', $project);
    }

    public function testCreateProjectWithoutProjectType()
    {
        $this->guesser->expects($this->once())
            ->method('guess')
            ->will($this->returnValue(array('symfony', '1')));

        $project = $this->factory->createProject(new Config());

        $this->assertInstanceOf('\Symfttpd\Project\ProjectInterface', $project);
    }

    /**
     * @dataProvider getInvalidConfig
     *
     * @param $config
     * @param $exception
     * @param $exceptionMessage
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
                'exception' => '\InvalidArgumentException',
                'exceptionMessage' => '"foo" is not supported.'
            ),
            array(
                'config' => new Config(array('project_type' => 'foo', 'project_version' => 3)),
                'exception' => '\InvalidArgumentException',
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
            $this->getMock('\Symfttpd\Project\ProjectInterface')
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

        $project = $this->getMock('\Symfttpd\Project\ProjectInterface');
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
