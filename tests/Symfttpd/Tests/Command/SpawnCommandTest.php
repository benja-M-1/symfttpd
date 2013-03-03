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

namespace Symfttpd\Tests\Command;

use Symfony\Component\Console\Tester\CommandTester;
use Symfttpd\Options;
use Symfttpd\Server\Server;
use Symfttpd\Console\Command\SpawnCommand;

/**
 * SpawnCommand test class.
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
class SpawnCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Symfony\Component\Console\Tester\CommandTester $command
     */
    protected $command;

    public function setUp()
    {
        $this->command = new SpawnCommand();
    }

    /**
     * @covers \Symfttpd\Console\Command\SpawnCommand::execute
     * @covers \Symfttpd\Console\Command\SpawnCommand::getMessage
     */
    public function testExecute()
    {
        $options = new Options(array(
            'address' => '127.0.0.1',
            'port' => '4043',
            'executableFiles' => array('index.php')
        ));

        $server = $this->getMock('Symfttpd\Server\ServerInterface');
        $server->expects($this->any())
            ->method('getOptions')
            ->will($this->returnValue($options));

        $pimple = new \Pimple(array(
            'server'           => $server,
            'generator.server' => $this->getMock('\Symfttpd\Generator\ServerConfigurationGenerator', array(), array(), '', false),
            'filesystem'       => $this->getMock('\Symfony\Component\Filesystem\Filesystem'),
            'watcher'          => $this->getMock('\Symfttpd\Watcher\Watcher')
        ));
        $application = new \Symfttpd\Console\Application();
        $application->setContainer($pimple);
        $application->add($this->command);

        $commandTester = new CommandTester($this->command);
        $commandTester->execute(array(
            'command' => $this->command->getName(),
            '-p' => 4043
        ));

        $this->assertRegExp('/started on 127.0.0.1, port 4043./', $commandTester->getDisplay());
        $this->assertRegExp('#http://127\.0\.0\.1:4043/index.php#', $commandTester->getDisplay());
        $this->assertNotRegExp('/The server cannot start/', $commandTester->getDisplay());
    }

    /**
     * @covers \Symfttpd\Console\Command\SpawnCommand::execute
     * @covers \Symfttpd\Console\Command\SpawnCommand::getMessage
     */
    public function testExecuteOnAllInterfaces()
    {
        $options = new Options(array('port' => '4043', 'executableFiles' => array('index.php')));

        $server = $this->getMock('Symfttpd\Server\ServerInterface');
        $server->expects($this->any())
            ->method('getOptions')
            ->will($this->returnValue($options));

        $application = new \Symfttpd\Console\Application();
        $application->setContainer(new \Pimple(array(
            'server'     => $server,
            'generator'  => $this->getMock('\Symfttpd\ConfigurationGenerator', array(), array(), '', false),
            'filesystem' => $this->getMock('\Symfony\Component\Filesystem\Filesystem'),
            'watcher'    => $this->getMock('\Symfttpd\Watcher\Watcher')
        )));
        $application->add($this->command);

        $commandTester = new CommandTester($this->command);
        $commandTester->execute(array(
            'command' => $this->command->getName(),
            '-p' => 4043,
            '--all' => true
        ));

        $this->assertRegExp('/started on all interfaces, port 4043./', $commandTester->getDisplay());
        $this->assertRegExp('#http://localhost:4043/index.php#', $commandTester->getDisplay());
        $this->assertNotRegExp('/The server cannot start/', $commandTester->getDisplay());
    }

    public function testGetMessage()
    {
        $server = $this->getMock('\Symfttpd\Server\ServerInterface');

        $options = new Options(
            array(
                'address' => 'localhost',
                'port'    => '4042',
                'executableFiles' => array('app.php', 'app_dev.php'),
            )
        );

        $server->expects($this->any())
            ->method('getOptions')
            ->will($this->returnValue($options));

        $command = new SpawnCommand();
        $message = $command->getMessage($server);

        $this->assertRegExp('~localhost:4042~', $message);
    }
}
