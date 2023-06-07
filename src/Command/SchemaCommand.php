<?php

namespace CommunityDS\Deputy\Api\Console\Command;

use InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SchemaCommand extends BaseCommand
{
    // phpcs:ignore
    protected static $defaultName = 'schema';

    public function configure()
    {
        parent::configure();
        $this->setDescription('Details the schema of a particular resource');
        $this->addArgument('resource', InputArgument::REQUIRED, 'Name of the Resource');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $resourceName = $input->getArgument('resource');

        $schema = $this->getWrapper()->schema->resource($resourceName);
        if ($schema == null) {
            throw new InvalidArgumentException("Resource not found: {$resourceName}");
        }

        $io = new SymfonyStyle($input, $output);

        $pure = $schema->fetchDefinitions();
        $merged = $schema->toConfig();

        $io->title("Schema for {$resourceName} resource");

        $groups = ['fields', 'assocs', 'joins'];
        foreach ($groups as $group) {
            $headers = [ucwords($group), 'DataType', 'FromApi', 'Notes'];
            $rows = [];
            $keys = array_unique(
                array_merge(
                    isset($pure[$group]) ? array_keys($pure[$group]) : [],
                    isset($merged[$group]) ? array_keys($merged[$group]) : []
                )
            );
            sort($keys);
            foreach ($keys as $key) {
                $dataType = isset($merged[$group][$key]) ? $merged[$group][$key] : '';
                $apiType = isset($pure[$group][$key]) ? $pure[$group][$key] : '';
                $rows[] = [
                    $key,
                    $dataType,
                    $apiType,
                    ($dataType == $apiType ? '' : ' *'),
                ];
            }
            $io->table($headers, $rows);
        }

        $io->note('* indicates a data type that is different from the INFO endpoint');

        return static::SUCCESS;
    }
}
