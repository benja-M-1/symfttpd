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
use Symfony\Component\Process\ProcessBuilder;
use Symfttpd\Options;
use Symfttpd\ConfigurationGenerator;
use Symfttpd\Gateway\GatewayInterface;

/**
 * BaseGateway
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
abstract class BaseGateway implements GatewayInterface
{
    /**
     * @var \Symfony\Component\Process\ProcessBuilder
     */
    protected $processBuilder;

    /**
     * @var \Psr\Log\LoggerInterface;
     */
    protected $logger;

    /**
     * @var Options
     */
    public $options;

    /**
     * @param \Symfttpd\Options $options
     *
     * @return \Symfttpd\Options|void
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
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function start(ConfigurationGenerator $generator)
    {
        // Create the socket file first.
        touch($this->options['socket']);

        $process = $this->getProcessBuilder()
            ->setArguments($this->getCommandLineArguments())
            ->getProcess();

        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException($process->getErrorOutput());
        }

        if (null !== $this->logger) {
            $this->logger->debug("{$this->getName()} started.");
        }
    }

    /**
     * {@inheritdoc}
     */
    public function stop()
    {
        \Symfttpd\Utils\PosixTools::killPid($this->options['pidfile']);

        if (null !== $this->logger) {
            $this->logger->debug("{$this->getName()} stopped.");
        }
    }

    /**
     * Return the parts of the command line to run the process.
     *
     * @return mixed
     */
    abstract protected function getCommandLineArguments();
}
