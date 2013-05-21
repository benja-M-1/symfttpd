<?php

namespace Symfttpd\Tests\Command\Helper;

use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Output\StreamOutput;
use Symfttpd\Console\Helper\DialogHelper;
use Symfttpd\Console\Helper\SymfttpdHelper;

/**
 * SymfttpHelperTest
 * 
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 */
class SymfttpHelperTest extends \PHPUnit_Framework_TestCase
{
    protected $helper;

    public function setUp()
    {
        $this->helper = new SymfttpdHelper();

        $helperSet = new HelperSet(array(new FormatterHelper(), new DialogHelper()));
        $this->helper->setHelperSet($helperSet);
    }

    public function testShouldSelectAServer()
    {
        $this->helper->setInputStream($this->getInputStream("\n0\n"));
        $this->helper->setServers(array('nginx', 'foo'));

        $this->assertEquals('nginx', $this->helper->selectServer($this->getOutputStream()));
    }

    public function testShouldSelectAGateway()
    {
        $this->helper->setInputStream($this->getInputStream("\n0\n"));
        $this->helper->setGateways(array('php-fpm', 'foo'));

        $this->assertEquals('php-fpm', $this->helper->selectGateway($this->getOutputStream()));
    }

    protected function getInputStream($input)
    {
        $stream = fopen('php://memory', 'r+', false);
        fputs($stream, $input);
        rewind($stream);

        return $stream;
    }

    protected function getOutputStream()
    {
        return new StreamOutput(fopen('php://memory', 'r+', false));
    }

}
