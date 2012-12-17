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

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfttpd\Gateway\BaseGateway;
use Symfttpd\Gateway\GatewayProcessableInterface;
use Symfttpd\ConfigurationGenerator;

/**
 * PhpFpm description
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
class PhpFpm extends BaseGateway implements GatewayProcessableInterface
{
    /**
     * @var \Symfony\Component\Process\Process
     */
    protected $process;

    /**
     * @return string
     */
    public function getName()
    {
        return 'php-fpm';
    }

    /**
     * @param \Symfttpd\ConfigurationGenerator                  $generator
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return mixed|void
     * @throws \RuntimeException
     */
    public function start(ConfigurationGenerator $generator, OutputInterface $output)
    {
        $generator->dump($this, false);

        $this->process = new Process(null);
        $this->process->setCommandLine(implode(' ', array($this->getCommand(), '-y', $generator->getPath())));
        $this->process->setTimeout(null);
        $this->process->run();

        $stderr = $this->process->getErrorOutput();

        if (!empty($stderr)) {
            throw new \RuntimeException($stderr);
        }
    }

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return mixed
     */
    public function stop(OutputInterface $output)
    {
        $this->process->stop(0);

        $output->writeln('<info>'.$this->getName().' stopped</info>');
    }

    /**
     * @return string
     */
    public function getSocket()
    {
        return sys_get_temp_dir().'/symfttpd-php-fpm.sock';
    }

    /**
     * @todo configure this
     * @return string
     */
    public function getPidfile()
    {
        return sys_get_temp_dir().'/'.$this->getName().'-pidfile.pid';
    }

    /**
     * @todo configure this
     * @return string
     */
    public function getErrorLog()
    {
        return sys_get_temp_dir().'/'.$this->getName().'-error.log';
    }
}