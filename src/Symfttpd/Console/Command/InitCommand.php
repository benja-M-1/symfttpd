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

namespace Symfttpd\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfttpd\Console\Command\Command;
use Symfttpd\Console\Helper\SymfttpdHelper;

/**
 * InitCommand class
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
class InitCommand extends Command
{
    protected $userChoices = array();

    protected function configure()
    {
        $this->setName('init');
        $this->setDescription('Initialize the Symfttpd configuration file.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $file = new \Symfttpd\SymfttpdFile();

        if ($input->isInteractive() && file_exists($file->getDefaultFilePath())) {
            $dialog = $this->getHelper('dialog');
            if (!$dialog->askConfirmation($output, $dialog->getQuestion('The file already exists, do you overwrite it', 'yes', '?'), true)) {
                $output->writeln('<error>Generation aborted</error>');

                return 1;
            }
        }

        $file->write($this->userChoices);

        $output->writeln(array(
            '',
            '<comment>Symfttpd file successfuly created!</comment>',
            '',
            sprintf('<info>You can now start your webserver: <comment>"%s spawn"</comment>.</info>', $this->getApplication()->getExecutable())
        ));
    }

    /**
     * {@inheritdoc}
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getApplication()->getContainer();

        /** @var $dialog \Symfttpd\Console\Helper\DialogHelper */
        $dialog = $this->getHelper('dialog');

        /** @var  $symfttpdHelper SymfttpdHelper */
        $symfttpdHelper = $this->getHelper('symfttpd');

        // Project related configuration
        $output->writeln(array('','Configure your project.',''));

        $projects = array('php', 'symfony');
        $type = $dialog->select($output, "<info>What is the type of your project?</info>", $projects);
        $this->userChoices['project_type'] = $projects[$type];
        if ('symfony' == $projects[$type]) {
            $version = $dialog->ask($output, $dialog->getQuestion('Which version, 1 or 2?', 2), 2);
        } else {
            $version = null;
        }
        $this->userChoices['project_version'] = $version;

        // Server related configuration
        $output->writeln(array('', 'Configure the server used by Symfttpd', ''));

        $this->userChoices['server_type'] = $symfttpdHelper->selectServer($output);

        $cmd = $container['finder']->find($this->userChoices['server_type']);
        $this->userChoices['server_cmd'] = $dialog->ask($output, $dialog->getQuestion('Set the server executable command', $cmd), $cmd);

        // gateway related configuration
        $output->writeln(array('', 'Configure the gateway used by the server.', ''));

        $this->userChoices['gateway_type'] = $symfttpdHelper->selectGateway($output);

        if ('fastcgi' !== $this->userChoices['gateway_type']) {
            $cmd = $container['finder']->find($this->userChoices['gateway_type']);
            $this->userChoices['gateway_cmd'] = $dialog->ask($output, $dialog->getQuestion('Set the gateway executable command', $cmd), $cmd);
        }
    }

    /**
     * @return array
     */
    public function getUserChoices()
    {
        return $this->userChoices;
    }
}
