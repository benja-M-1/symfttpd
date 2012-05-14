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

use Symfttpd\Server\ServerInterface;
use Symfttpd\Project\ProjectInterface;
use Symfttpd\Server\Exception\ServerException;
use Symfttpd\Filesystem\Filesystem;
use Symfttpd\Configuration\OptionBag;
use Symfttpd\Renderer\RendererInterface;
use Symfttpd\Renderer\TwigRenderer;
use Symfttpd\Configuration\SymfttpdConfiguration;
use Symfttpd\Configuration\ConfigurationInterface;
use Symfttpd\Configuration\Exception\ConfigurationException;
use Symfttpd\Exception\ExecutableNotFoundException;
use Symfony\Component\Process\ExecutableFinder;

/**
 * Lighttpd class
 *
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 */
class Lighttpd implements ServerInterface
{
    public $name = 'lighttpd';

    /**
     * The shell command to run lighttpd.
     *
     * @var string
     */
    protected $command;

    /**
     * The file that configures the server.
     *
     * @var string
     */
    protected $configFilename = 'lighttpd.conf';

    /**
     * The generated configuration used by lighttpd.
     *
     * @var string
     */
    protected $lighttpdConfig;

    /**
     * The path to the configuration file.
     *
     * @var string
     */
    protected $configFile;

    /**
     * The file that configures rewriting rules (mainly) for lighttpd.
     *
     * @var string
     */
    protected $rulesFilename = 'rules.conf';

    /**
     * The generated rules.
     *
     * @var string
     */
    protected $rules;

    /**
     * The generated rules file used by lighttpd.
     *
     * @var string
     */
    protected $rulesFile;

    /**
     * The directory of the project.
     *
     * @var string
     */
    protected $workingDir;

    /**
     * The collection of configuration options.
     *
     * @var OptionBag
     */
    public $options;

    /**
     * @var ProjectInterface
     */
    public $project;

    /**
     * @var RendererInterface
     */
    public $renderer;

    /**
     * Constructor class
     *
     * @param null $workingDir
     * @param null|\Symfttpd\Configuration\OptionBag $options
     */
    public function __construct(ProjectInterface $project, OptionBag $options = null, RendererInterface $renderer = null)
    {
        $this->project = $project;
        $this->options = $options ?: new OptionBag();
        $this->renderer = $renderer ?: new TwigRenderer();

        $this->setup();

        $this->options->set('pidfile', $this->options->get('cache_dir').'/.sf');
        $this->options->set('restartfile', $this->options->get('cache_dir').'/.symfttpd_restart');

        $this->rotate();
    }

    /**
     * Update the options.
     */
    public function setup()
    {
        // Set the defaults settings
        $this->options->merge(array(
            // Lighttpd configuration options.
            'document_root' => $this->project->getWebDir(),
            'log_dir'       => $this->project->getLogDir().'/lighttpd',
            'cache_dir'     => $this->project->getCacheDir().'/lighttpd',
            // Rewrite rules options.
            'nophp'         => array(),
            'default'       => $this->project->getIndexFile(),
            'phps'          => $this->project->readablePhpFiles,
            'files'         => $this->project->readableFiles,
            'dirs'          => $this->project->readableDirs,
        ));
    }

    /**
     * Read the server configuration.
     *
     * @param string $separator
     * @return string
     */
    public function read($separator = PHP_EOL)
    {
        return $this->readConfiguration().$separator.$this->readRules();
    }

    /**
     * Return the lighttpd configuration content.
     * Read the lighttpd.conf in the cache file
     * if needed.
     *
     * @return string
     * @throws Exception\ConfigurationException
     */
    public function readConfiguration()
    {
        if (null !== $this->lighttpdConfig) {
            return $this->lighttpdConfig;
        }

        if (null == $this->configFile) {
            $this->configFile = $this->options->get('cache_dir').DIRECTORY_SEPARATOR.$this->configFilename;
        }

        if (false == file_exists($this->getConfigFile())) {
            throw new ConfigurationException('The lighttpd configuration has not been generated.');
        }

        $this->lighttpdConfig = file_get_contents($this->getConfigFile());

        return $this->lighttpdConfig;
    }

    /**
     * Return the rules configuration content.
     * Read the rules.conf in the cache directory
     * if needed.
     *
     * @return string
     * @throws Exception\ConfigurationException
     */
    public function readRules()
    {
        if (null !== $this->rules) {
            return $this->rules;
        }

        if (null == $this->rulesFile) {
            $this->rulesFile = $this->options->get('cache_dir').DIRECTORY_SEPARATOR.$this->rulesFilename;
        }

        if (false == file_exists($this->rulesFile)) {
            throw new ConfigurationException('The rules configuration has not been generated.');
        }

        $this->rules = file_get_contents($this->rulesFile);

        return $this->rules;
    }

    /**
     * Write the configurations files.
     *
     * @param bool $force If false, does not overwrite the existing files.
     * @throws \Symfttpd\Configuration\Exception\ConfigurationException
     */
    public function write($force = false)
    {
        $file = '';
        $type = count(func_get_args()) > 0 ? func_get_arg(0) : 'all';

        switch ($type) {
            case 'config':
            case 'configuration':
                $file = $this->configFile;
                $content = $this->lighttpdConfig;
                break;
            case 'rules':
                $file = $this->rulesFile;
                $content = $this->rules;
                break;
            case 'all':
            default:
                $this->write('config');
                $this->write('rules');
                break;
        }

        // Don't rewrite existing configuration if not forced to.
        if (false === $force && file_exists($file)) {
            return;
        }

        if ($type !== 'all' && false === file_put_contents($file, $content)) {
            throw new ConfigurationException(sprintf("Cannot generate the lighttpd %s file.", $type));
        }
    }

    /**
     * Write the configuration file.
     *
     * @throws Exception\ConfigurationException
     */
    public function writeConfiguration()
    {
        $this->write('config');
    }

    /**
     * Write the rules configuration file.
     *
     * @throws Exception\ConfigurationException
     */
    public function writeRules()
    {
        $this->write('rules');
    }

    /**
     * Generate the whole configuration :
     * the server configuration based on the lighttpd.conf.php template
     * the rules configuration with the rewrite rules based on the rules.conf.php template
     *
     * @param SymfttpdConfiguration $configuration
     */
    public function generate(SymfttpdConfiguration $configuration)
    {
        $this->generateRules($configuration);
        $this->generateConfiguration($configuration);
    }

    /**
     * Generate the lighttpd configuration file.
     *
     * @param SymfttpdConfiguration $configuration
     */
    public function generateConfiguration(SymfttpdConfiguration $configuration)
    {
        $this->lighttpdConfig = $this->renderer->render(
            $this->getTemplateDir(),
            $this->configFilename.'.twig',
            array(
                'document_root' => $this->options->get('document_root'),
                'port'          => $this->options->get('port'),
                'bind'          => $this->options->get('bind', null),
                'error_log'     => $this->options->get('log_dir').'/error.log',
                'access_log'    => $this->options->get('log_dir').'/access.log',
                'pidfile'       => $this->getPidfile(),
                'rules_file'    => $this->getRulesFile(),
                'php_cgi_cmd'   => $configuration->get('php_cgi_cmd'),
            )
        );
        $this->configFile = $this->options->get('cache_dir').DIRECTORY_SEPARATOR.$this->configFilename;
    }

    /**
     * Generate the lighttpd rules configuration.
     *
     * @param SymfttpdConfiguration $configuration
     */
    public function generateRules(SymfttpdConfiguration $configuration)
    {
        $this->rules = $this->renderer->render(
            $this->getTemplateDir(),
            $this->rulesFilename.'.twig',
            array(
                'dirs'    => $this->options->get('dirs'),
                'files'   => $this->options->get('files'),
                'phps'    => $this->options->get('phps'),
                'default' => $this->options->get('default'),
                'nophp'   => $this->options->get('nophp'),
            )
        );
        $this->rulesFile = $this->options->get('cache_dir').DIRECTORY_SEPARATOR.$this->rulesFilename;
    }

    /**
     * @return string
     */
    public function getTemplateDir()
    {
        return __DIR__ . sprintf('/../Resources/templates/lighttpd');
    }

    /**
     * Return the configuration template path.
     *
     * @return string
     */
    public function getConfigurationTemplate()
    {
        return $this->getTemplateDir().sprintf('/%s.php', $this->configFilename);
    }

    /**
     * Return the rules template path.
     *
     * @return string
     */
    public function getRulesTemplate()
    {
        return $this->getTemplateDir().sprintf('/%s.php', $this->rulesFilename);
    }

    /**
     * Remove the log and cache directory of
     * lighttpd and recreate them.
     *
     * @param null|\Symfttpd\Filesystem\Filesystem $filesystem
     */
    public function rotate($clear = false, Filesystem $filesystem = null)
    {
        $directories = array(
            $this->options->get('cache_dir'),
            $this->options->get('log_dir'),
        );

        $filesystem = $filesystem ?: new Filesystem();

        if (true === $clear) {
            $filesystem->remove($directories);
        }
        $filesystem->mkdir($directories);
    }

    /**
     * Return the lighttpd configuration file path.
     *
     * @return string
     */
    public function getConfigFile()
    {
        return $this->configFile;
    }

    /**
     * Return the rules config file path.
     *
     * @return string
     */
    public function getRulesFile()
    {
        return $this->rulesFile;
    }

    /**
     * Return the name of the configuration file.
     *
     * @return string
     */
    public function getConfigFilename()
    {
        return $this->configFilename;
    }

    /**
     * Return the name of the rules file.
     *
     * @return string
     */
    public function getRulesFilename()
    {
        return $this->rulesFilename;
    }

    /**
     * Return the server command value
     *
     * @param null|\Symfony\Component\Process\ExecutableFinder $finder
     * @return string
     * @throws \Symfttpd\Exception\ExecutableNotFoundException
     */
    public function getCommand(ExecutableFinder $finder = null)
    {
        if (null == $this->command) {

            if (null == $finder) {
                $finder = new ExecutableFinder();
            }

            $finder->addSuffix('');
            $cmd = $finder->find('lighttpd');

            if (null == $cmd) {
                throw new ExecutableNotFoundException('lighttpd executable not found.');
            }

            $this->command = $cmd;
        }

        return $this->command;
    }

    /**
     * Set the command to use.
     *
     * @param $command
     */
    public function setCommand($command)
    {
        $this->command = $command;
    }

    /**
     * Start the server.
     */
    public function start()
    {
        // Remove an possible existing restart file
        $this->removeRestartFile();
        $command = $this->getCommand() . ' -D -f ' . escapeshellarg($this->getConfigFile());

        $process = new \Symfony\Component\Process\Process($command, $this->project->getRootDir(), null, null, null);
        $process->run();
    }

    /**
     * Return the restartfile.
     *
     * If the server configuration (rules or base configuration)
     * changed, it generates a restart file that means that
     * the server must be restarted.
     *
     * @return mixed|null
     */
    public function getRestartFile()
    {
        return $this->options->get('restartfile');
    }

    /**
     * Return the pidfile which contains the pid of the process
     * of the server.
     *
     * @return mixed|null
     */
    public function getPidfile()
    {
        return $this->options->get('pidfile');
    }

    /**
     * Delete the restart file if exists.
     */
    public function removeRestartFile()
    {
        if (file_exists($this->getRestartFile())) {
            unlink($this->getRestartFile());
        }
    }

    /**
     * @return \Symfttpd\Project\ProjectInterface
     */
    public function getProject()
    {
        return $this->project;
    }
}
