<?php

namespace Symfttpd\Tests\EventDispatcher\Listener;

use Symfony\Component\Filesystem\Filesystem;
use Symfttpd\EventDispatcher\Listener\ServerListener;

/**
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
class ServerListenerTest extends \PHPUnit_Framework_TestCase
{
    protected
        $fixtures,
        $filesystem;

    public function setup()
    {
        $this->fixtures = sys_get_temp_dir().'/log';
        $this->filesystem = new Filesystem();
    }

    public function tearDown()
    {
        $this->filesystem->remove($this->fixtures);
    }

    public function testShouldCreateServerLogDirectory()
    {
        $options = new \Symfttpd\Options(array(
            'errorLog'  => $this->fixtures.'/error.log',
            'accessLog' => $this->fixtures.'/access.log',
        ));

        $server =$this->getMock('Symfttpd\Server\ServerInterface');
        $server->expects($this->any())
            ->method('getOptions')
            ->will($this->returnValue($options));

        $generator = $this->getMock('Symfttpd\Generator\ServerConfigurationGenerator', array(), array(), '', false);

        $listener = new ServerListener($this->filesystem, $generator);
        $listener->createLogDirectory($server);
        $this->assertFileExists($this->fixtures);
    }
}
