<?php

namespace CommunityDS\Deputy\Api\Console\Adapter;

use CommunityDS\Deputy\Api\Adapter\Logger\Psr3Logger;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Bridge between wrapper logger and the Symfony ConsoleLogger.
 */
class ConsoleOutputLogger extends Psr3Logger implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            ConsoleEvents::COMMAND => ['onCommand', 0],
        ];
    }

    public function onCommand(ConsoleCommandEvent $event)
    {
        $this->logger = new ConsoleLogger($event->getOutput());
    }
}
