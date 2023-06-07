<?php

namespace CommunityDS\Deputy\Api\Console\Command;

use InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class RecordCommand extends BaseCommand
{
    // phpcs:ignore
    protected static $defaultName = 'record';

    public function configure()
    {
        parent::configure();
        $this->setDescription('Outputs the information of a specific record');
        $this->addArgument('resource', InputArgument::REQUIRED, 'Name of the Resource');
        $this->addArgument('id', InputArgument::REQUIRED, 'ID of the record');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $resourceName = $input->getArgument('resource');
        $schema = $this->getWrapper()->schema->resource($resourceName);
        if ($schema == null) {
            throw new InvalidArgumentException("Unknown resource: {$resourceName}");
        }

        $id = $input->getArgument('id');

        $record = $schema->findOne($id);
        if ($record == null) {
            throw new InvalidArgumentException("Record not found: {$resourceName} #{$id}");
        }

        $this->outputRecord($io, $record);

        return static::SUCCESS;
    }
}
