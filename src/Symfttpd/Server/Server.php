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

namespace Symfttpd\Server;

use Psr\Log\LoggerInterface;
use Symfony\Component\Process\ProcessBuilder;
use Symfttpd\Options;
use Symfttpd\ConfigurationGenerator;
use Symfttpd\Gateway\GatewayInterface;
use Symfttpd\Project\ProjectInterface;
use Symfttpd\Server\ServerInterface;

/**
 * Server class.
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
abstract class Server implements ServerInterface
{
    /**
     * @var \Symfttpd\Gateway\GatewayInterface
     */
    protected $gateway;

    /**
     * @var \Symfony\Component\Process\ProcessBuilder
     */
    protected $processBuilder;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var Options
     */
    public $options;

    /**
     * Configure the server.
     *
     * @param \Symfttpd\Options                   $options
     * @param \Symfttpd\Project\ProjectInterface $project
     *
     * @throws \RuntimeException
     */
    public function configure(Options $options, ProjectInterface $project)
    {
        $this->options = new Options();

        $baseDir = $options->get('symfttpd_dir', getcwd().'/symfttpd');
        $logDir = $options->get('server_log_dir', $baseDir .'/log');

        $this->bind($options->get('server_address', '127.0.0.1'), $options->get('server_port', '4042'));

        $this->options['executable']       = $options->get('server_cmd');
        $this->options['documentRoot']     = $project->getWebDir();
        $this->options['indexFile']        = $project->getIndexFile();
        $this->options['errorLog']         = $logDir . '/' . $options->get('server_error_log', 'error.log');
        $this->options['accessLog']        = $logDir . '/' . $options->get('server_access_log', 'access.log');
        $this->options['tempPath']         = $baseDir.'/tmp';
        $this->options['pidfile']          = $baseDir . '/' . $options->get('server_pidfile', $this->getName().'.pid');
        $this->options['allowedDirs']      = $options->get('project_readable_dirs', $project->getDefaultReadableDirs());
        $this->options['allowedFiles']     = $options->get('project_readable_files', $project->getDefaultReadableFiles());
        $this->options['executableFiles']  = $options->get('project_readable_phpfiles', $project->getDefaultExecutableFiles());
        $this->options['unexecutableDirs'] = $options->get('project_nophp', array());
    }

    /**
     * {@inheritdoc}
     */
    public function bind($address, $port = null)
    {
        $this->options['address'] = $address;
        $this->options['port']    = $port;
    }

    /**
     * {@inheritdoc}
     */
    public function start(ConfigurationGenerator $generator)
    {
        $process = $this->getProcessBuilder()
            ->setArguments($this->getCommandLineArguments($generator))
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
        // Kill the current server process.
        \Symfttpd\Utils\PosixTools::killPid($this->options['pidfile']);

        if (null !== $this->logger) {
            $this->logger->debug("{$this->getName()} stopped.");
        }
    }

    /**
     * {@inheritdoc}
     */
    public function restart(ConfigurationGenerator $generator)
    {
        $this->stop();
        $this->start($generator);
    }

    /**
     * Return the command line executed by the process.
     *
     * @param \Symfttpd\ConfigurationGenerator $generator
     *
     * @return array
     * @throws \RuntimeException
     */
    abstract protected function getCommandLineArguments(ConfigurationGenerator $generator);

    /**
     * Set the gateway instance used by the server.
     *
     * @param \Symfttpd\Gateway\GatewayInterface $gateway
     */
    public function setGateway(GatewayInterface $gateway)
    {
        $this->gateway = $gateway;
    }

    /**
     * {@inheritdoc}
     */
    public function getGateway()
    {
        return $this->gateway;
    }

    /**
     * Set the process builder instance.
     *
     * @param \Symfony\Component\Process\ProcessBuilder $pb
     */
    public function setProcessBuilder(ProcessBuilder $pb)
    {
        $this->processBuilder = $pb;
    }

    /**
     * Return the process builder instance.
     *
     * @return null|\Symfony\Component\Process\ProcessBuilder $pb
     */
    public function getProcessBuilder()
    {
        return $this->processBuilder;
    }

    /**
     * Set the logger instance.
     *
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Return the logger instance.
     *
     * @return null|\Psr\Log\LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }
}
