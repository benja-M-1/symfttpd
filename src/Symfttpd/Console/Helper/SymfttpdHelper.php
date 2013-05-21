<?php

namespace Symfttpd\Console\Helper;

use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * SymfttpdHelper
 * 
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 */
class SymfttpdHelper extends Helper
{
    /**
     * @var array
     */
    protected $servers;

    /**
     * @var array
     */
    protected $gateways;

    /**
     * @param array $servers
     * @param array $gateways
     */
    public function __construct(array $servers = array(), array $gateways = array())
    {
        $this->servers = $servers;
        $this->gateways = $gateways;
    }

    /**
     * @param mixed $inputStream
     *
     * @return SymfttpdHelper
     */
    public function setInputStream($inputStream)
    {
        $this->inputStream = $this->getHelperSet()->get('dialog')->setInputStream($inputStream);

        return $this;
    }

    /**
     * Returns the canonical name of this helper.
     *
     * @return string The canonical name
     *
     * @api
     */
    public function getName()
    {
        return 'symfttpd';
    }

    /**
     * @param OutputInterface $output
     *
     * @return null|string
     */
    public function selectServer(OutputInterface $output)
    {
        $question = '<info>Which server server do you want to use?</info>';

        $key = $this->getHelperSet()->get('dialog')->select($output, $question, $this->servers);

        return $this->servers[$key];
    }

    /**
     * @param OutputInterface $output
     *
     * @return null|string
     */
    public function selectGateway(OutputInterface $output)
    {
        $question = '<info>Which gateway do you want to use?</info>';

        $key = $this->getHelperSet()->get('dialog')->select($output, $question, $this->gateways);

        return $this->gateways[$key];
    }

    /**
     * @param array $gateways
     *
     * @return SymfttpdHelper
     */
    public function setGateways($gateways)
    {
        $this->gateways = $gateways;

        return $this;
    }

    /**
     * @return array
     */
    public function getGateways()
    {
        return $this->gateways;
    }

    /**
     * @param array $servers
     *
     * @return SymfttpdHelper
     */
    public function setServers($servers)
    {
        $this->servers = $servers;

        return $this;
    }

    /**
     * @return array
     */
    public function getServers()
    {
        return $this->servers;
    }
}
