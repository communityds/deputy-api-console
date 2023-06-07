<?php

namespace CommunityDS\Deputy\Api\Console;

use CommunityDS\Deputy\Api\Console\Command\MeCommand;
use CommunityDS\Deputy\Api\Console\Command\PhpdocCommand;
use CommunityDS\Deputy\Api\Console\Command\RecordCommand;
use CommunityDS\Deputy\Api\Console\Command\SchemaCommand;
use CommunityDS\Deputy\Api\Wrapper;
use RuntimeException;
use Symfony\Component\Console\Application;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ConsoleApplication extends Application
{
    /**
     * @var static
     */
    protected static $instance;

    public function __construct()
    {
        parent::__construct('Deputy API Wrapper', 'current');

        $this->add(new MeCommand());
        $this->add(new PhpdocCommand());
        $this->add(new RecordCommand());
        $this->add(new SchemaCommand());

        static::$instance = $this;
        $wrapper = Wrapper::setInstance($this->getConfig());

        $dispatcher = new EventDispatcher();
        $this->setDispatcher($dispatcher);
        if ($wrapper->logger instanceof EventSubscriberInterface) {
            $dispatcher->addSubscriber($wrapper->logger);
        }
    }

    /**
     * Returns the configuration.
     *
     * @return array
     *
     * @throws RuntimeException When configuration can not be found
     */
    public function getConfig()
    {
        $filePath = __DIR__ . '/../config.php';
        if (!file_exists($filePath)) {
            throw new RuntimeException("Missing config.php file");
        }

        $config = require $filePath;

        if (!isset($config['auth'])) {
            throw new RuntimeException("Missing 'auth' configuration");
        }
        if (!isset($config['target'])) {
            throw new RuntimeException("Missing 'target' configuration");
        }

        return $config;
    }

    /**
     * @return static
     */
    public static function getInstance()
    {
        return static::$instance;
    }
}
