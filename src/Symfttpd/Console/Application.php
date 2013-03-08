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

namespace Symfttpd\Console;

use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfttpd\Console\Command\GenconfCommand;
use Symfttpd\Console\Command\InitCommand;
use Symfttpd\Console\Command\SelfupdateCommand;
use Symfttpd\Console\Command\SpawnCommand;
use Symfttpd\Console\Helper\DialogHelper;

/**
 * Application
 *
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 */
class Application extends BaseApplication
{
    /**
     * @var \Pimple
     */
    protected $container;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct('Symfttpd', \Symfttpd\Symfttpd::VERSION);

        $this->container = $c = new \Pimple();

        $c['debug'] = false;

        $c['symfttpd_file'] = $c->share(function ($c) {
            $file = new \Symfttpd\SymfttpdFile();
            $file->setProcessor(new \Symfony\Component\Config\Definition\Processor());
            $file->setConfiguration(new \Symfttpd\SymfttpdConfiguration());

            return $file;
        });

        $c['options'] = $c->share(function ($c) {
            $options = new \Symfttpd\Options();
            $options->merge($c['symfttpd_file']->read());

            if (!$options->has('symfttpd_dir')) {
                $options->get('symfttpd_dir', getcwd().'/symfttpd');
            }

            return $options;
        });

        $c['project.guesser'] = $c->share(function ($c) {
            $guesser = new \Symfttpd\Guesser\ProjectGuesser();
            $guesser->registerChecker(new \Symfttpd\Guesser\Checker\Symfony1Checker());
            $guesser->registerChecker(new \Symfttpd\Guesser\Checker\Symfony2Checker());

            return $guesser;
        });

        $c['finder'] = $c->share(function ($c) {
            $finder = new \Symfony\Component\Process\ExecutableFinder();
            $finder->addSuffix('');

            return $finder;
        });

        $c['twig'] = $c->share(function ($c) {
            $dirs = array(__DIR__ . '/../Resources/templates/');
            $dirs += $c['options']->get('server_templates_dirs', array());

            return new \Twig_Environment(
                new \Twig_Loader_Filesystem($dirs),
                array(
                    'debug'            => true,
                    'strict_variables' => true,
                    'auto_reload'      => true,
                    'cache'            => false,
                )
            );
        });

        $c['filesystem'] = $c->share(function ($c) {
            return new \Symfony\Component\Filesystem\Filesystem();
        });

        $c['generator'] = $c->share(function ($c) {
            $options = $c['options'];
            $generator = new \Symfttpd\Generator\ConfigurationGenerator($c['twig'], $c['filesystem'], $c['logger']);
            $generator->setPath($options->get('server_config_path', $options->get('symfttpd_dir') . '/conf'));

            return $generator;
        });

        $c['project'] = $c->share(function ($c) {
            /** @var $options \Symfttpd\Options */
            $options = $c['options'];

            if (!$options->has('project_type')) {
                try {
                    list($type, $version) = $c['project.guesser']->guess();
                } catch (\Symfttpd\Guesser\Exception\UnguessableException $e) {
                    $type = 'php';
                    $version = null;
                }
            } else {
                $type = $options->get('project_type', 'php');
                $version = $options->get('project_version', null);
            }

            $class = sprintf('Symfttpd\\Project\\%s', ucfirst($type) . str_replace(array('.', '-', 'O'), '', $version));

            if (!class_exists($class)) {
                if (!$version) {
                    $message = sprintf('"%s"', $type);
                } else {
                    $message = sprintf('"%s" (with version "%s")', $type, $version);
                }

                throw new \InvalidArgumentException(sprintf('Project %s is not supported.', $message));
            }

            // @todo create a configure method in the project to not inject anything in the constructor.
            return new $class($options);
        });

        $c['server'] = $c->share(function ($c) {
            /** @var $options \Symfttpd\Options */
            $options = $c['options'];

            $server = $c['server.'.$options->get('server_type')];

            if (!$options->has('server_cmd')) {
                $options->set('server_cmd', $c['finder']->find($server->getName()));
            }

            $server->configure($options, $c['project']);
            $server->setGateway($c['gateway']);
            $server->setProcessBuilder($c['process_builder']);
            $server->setLogger($c['logger']);

            return $server;
        });

        $c['gateway'] = $c->share(function ($c) {
            /** @var $options \Symfttpd\Options */
            $options = $c['options'];

            /** @var \Symfttpd\Gateway\GatewayInterface $gateway */
            $gateway = $c['gateway.'.$options->get('gateway_type', 'fastcgi')];

            // Guess the gateway command if it is not porvided.
            if (!$options->has('gateway_cmd')) {
                $options->set('gateway_cmd', $c['finder']->find($gateway->getName()));
            }

            $gateway->configure($options);
            $gateway->setProcessBuilder($c['process_builder']);
            $gateway->setLogger($c['logger']);

            return $gateway;
        });

        $c['process_builder'] = function ($c) {
            $pb = new \Symfony\Component\Process\ProcessBuilder();
            $pb->setTimeout(null);

            return $pb;
        };

        $c['logger'] = $c->share(function ($c) {
            $level = \Monolog\Logger::ERROR;

            if (true === $c['debug']) {
                $level = \Monolog\Logger::DEBUG;
            }

            $logger = new \Monolog\Logger('symfttpd');
            $logger->pushHandler(new \Monolog\Handler\StreamHandler($c['options']->get('symfttpd_dir').'/log/symfttpd.log', $level));

            return $logger;
        });

        $c['watcher'] = $c->share(function ($c) {
            $watcher = new \Symfttpd\Watcher\Watcher();
            $watcher->setLogger($c['logger']);

            return $watcher;
        });

        $c['dispatcher'] = $c->share(function ($c) {
            $dispatcher = new \Symfony\Component\EventDispatcher\EventDispatcher();
            $dispatcher->addListener('server.pre_start', array($this->container['listener.server'], 'onStart'));
            $dispatcher->addListener('gateway.pre_start', array($this->container['listener.gateway'], 'onStart'));

            return $dispatcher;
        });

        $this->registerGenerators();
        $this->registerServers();
        $this->registerGateways();
        $this->registerListeners();
    }

    /**
     * Register generators in the container.
     */
    protected function registerGenerators()
    {
        $this->container['generator.server'] = $this->container->share(function ($c) {
                return new \Symfttpd\Generator\ServerConfigurationGenerator($c['generator']);
            });

        $this->container['generator.gateway'] = $this->container->share(function ($c) {
                return new \Symfttpd\Generator\GatewayConfigurationGenerator($c['generator']);
            });
    }

    /**
     * Register servers in the container.
     */
    protected function registerServers()
    {
        $this->container['server.lighttpd'] = $this->container->share(function ($c) {
            return new \Symfttpd\Server\Lighttpd($c['dispatcher']);
        });

        $this->container['server.nginx'] = $this->container->share(function ($c) {
            return new \Symfttpd\Server\Nginx($c['dispatcher']);
        });
    }

    /**
     * Register gateways in the container.
     */
    protected function registerGateways()
    {
        $this->container['gateway.fastcgi'] = $this->container->share(function ($c) {
            return new \Symfttpd\Gateway\Fastcgi($c['dispatcher']);
        });

        $this->container['gateway.php-fpm'] = $this->container->share(function ($c) {
            return new \Symfttpd\Gateway\PhpFpm($c['dispatcher']);
        });
    }

    /**
     * Register listeners in the container.
     */
    protected function registerListeners()
    {
        $this->container['listener.server'] = $this->container->share(function ($c) {
            return new \Symfttpd\EventDispatcher\Listener\ServerListener($c['filesystem'], $c['generator.server']);
        });

        $this->container['listener.gateway'] = $this->container->share(function ($c) {
            return new \Symfttpd\EventDispatcher\Listener\GatewayListener($c['filesystem'], $c['generator.gateway']);
        });
    }

    /**
     * @return array
     */
    public function getServerNames()
    {
        $servers = array_filter($this->container->keys(), function ($key) {
            return false !== strpos($key, 'server.');
        });

        $serverNames = array_map(function ($server) {
            return str_replace('server.', '', $server);
        }, $servers);

        return array_values($serverNames);
    }

    /**
     * @return array
     */
    public function getGatewayNames()
    {
        $gateways = array_filter($this->container->keys(), function ($key) {
                return false !== strpos($key, 'gateway.');
            });

        $gatewayNames = array_map(function ($gateway) {
                return str_replace('gateway.', '', $gateway);
            }, $gateways);

        return array_values($gatewayNames);
    }

    /**
     * Return the service container
     *
     * @return \Pimple
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @param $container
     */
    public function setContainer($container)
    {
        $this->container = $container;
    }

    /**
     * Initializes Symfttpd commands.
     *
     * @return array
     */
    public function getDefaultCommands()
    {
        $commands = parent::getDefaultCommands();
        $commands[] = new InitCommand();
        $commands[] = new GenconfCommand();
        $commands[] = new SpawnCommand();

        if (strpos($this->getExecutable(), 'phar')) {
            $commands[] = new SelfupdateCommand();
        }

        return $commands;
    }

    /**
     * @return \Symfony\Component\Console\Input\InputDefinition
     */
    protected function getDefaultInputDefinition()
    {
        $definition = parent::getDefaultInputDefinition();

        // Add options to Symfttpd globally
        $definition->addOptions(array(
            new InputOption('--debug', '-d', InputOption::VALUE_NONE, 'Switch on debug mode.'),
            new InputOption('--config',  '-c', InputOption::VALUE_OPTIONAL, 'Specify config file to use.'),
        ));

        return $definition;
    }

    /**
     * {@inheritdoc}
     */
    public function doRun(InputInterface $input, OutputInterface $output)
    {
        if (true === $input->hasParameterOption('--debug')) {
            $this->container['debug'] = true;
        }

        if (true === $input->hasParameterOption('--config')) {
            $file = $input->getParameterOption('--config');

            $this->container['symfttpd_file']->addPath(realpath($file));
        }

        return parent::doRun($input, $output);
    }

    /**
     * Gets the default helper set with the helpers that should always be available.
     *
     * @return HelperSet A HelperSet instance
     */
    protected function getDefaultHelperSet()
    {
        $helperSet = parent::getDefaultHelperSet();

        $helperSet->set(new DialogHelper(), 'dialog');

        return $helperSet;
    }

    /**
     * @return string
     */
    public function getExecutable()
    {
        $args = $_SERVER['argv'];
        $executable = reset($args);

        if (strpos($executable, 'phar')) {
            $executable = "php $executable";
        }

        return $executable;
    }
}
