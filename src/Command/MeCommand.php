<?php

namespace CommunityDS\Deputy\Api\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MeCommand extends BaseCommand
{
    // phpcs:ignore
    protected static $defaultName = 'me';

    public function configure()
    {
        parent::configure();
        $this->setDescription('Outputs the information from the me endpoint');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $this->outputRecord($io, $this->getWrapper()->getMe());

        return static::SUCCESS;
    }
}
