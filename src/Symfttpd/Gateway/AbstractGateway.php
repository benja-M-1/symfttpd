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

namespace Symfttpd\Gateway;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Process\ProcessBuilder;
use Symfttpd\Options;
use Symfttpd\Gateway\GatewayInterface;

/**
 * AbstractGateway
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
abstract class AbstractGateway implements GatewayInterface
{
    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var \Symfony\Component\Process\ProcessBuilder
     */
    protected $processBuilder;

    /**
     * @var Options
     */
    public $options;

    /**
     * @var string
     */
    public $configurationFile;

    /**
     * @var \Psr\Log\LoggerInterface;
     */
    protected $logger;

    /**
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher
     */
    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * @param \Symfttpd\Options $options
     */
    public function configure(Options $options)
    {
        $this->options = new Options();

        $baseDir = $options->get('symfttpd_dir', getcwd().'/symfttpd');

        // Create an id for the socket file
        $id = $this->getName().time();

        $this->options['executable'] = $options->get('gateway_cmd', $options->get('php_cgi_cmd'));
        $this->options['errorLog']   = $options->get('gateway_error_log', "$baseDir/log/{$this->getName()}-error.log");
        $this->options['pidfile']    = $options->get('gateway_pidfile', "$baseDir/symfttpd-{$this->getName()}.pid");
        $this->options['socket']     = $options->get('gateway_socket', "$baseDir/symfttpd-{$id}.sock");

        $group = posix_getgrgid(posix_getgid());
        $this->options['group'] = $group['name'];
        $this->options['user']  = get_current_user();
    }

    /**
     * Return the parts of the command line to run the process.
     *
     * @return mixed
     */
    abstract protected function getCommandLineArguments();

    /**
     * {@inheritdoc}
     */
    public function setProcessBuilder(ProcessBuilder $pb)
    {
        $this->processBuilder = $pb;
    }

    /**
     * {@inheritdoc}
     */
    public function getProcessBuilder()
    {
        return $this->processBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function setConfigurationFile($configurationFile)
    {
        $this->configurationFile = $configurationFile;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFile()
    {
        return $this->configurationFile;
    }

    /**
     * @return \Symfttpd\Options
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
}
