<?php

namespace CommunityDS\Deputy\Api\Console\Command;

use CommunityDS\Deputy\Api\Console\ConsoleApplication;
use CommunityDS\Deputy\Api\Console\ConsoleBootstrapInterface;
use CommunityDS\Deputy\Api\LoggerLocatorTrait;
use CommunityDS\Deputy\Api\Model\Me;
use CommunityDS\Deputy\Api\Model\Record;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

abstract class BaseCommand extends Command
{
    use LoggerLocatorTrait;

    /**
     * @return ConsoleApplication|null
     */
    public function getApplication(): ?Application
    {
        return parent::getApplication();
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        if ($this->getLogger() instanceof ConsoleBootstrapInterface) {
            $this->getLogger()->consoleBootstrap($this, $input, $output);
        }
        parent::initialize($input, $output);
    }

    /**
     * Returns a textual description of a record.
     *
     * @param Record $record
     *
     * @return string
     */
    protected function recordInfo(Record $record)
    {
        if ($record instanceof Me) {
            return get_class($record);
        }
        return '[' . get_class($record) . " #{$record->id}]";
    }

    /**
     * Outputs the details of a specific record.
     *
     * @param SymfonyStyle $io
     * @param Record $record
     *
     * @return Record[] Related records
     */
    protected function outputRecord(SymfonyStyle $io, Record $record)
    {
        $schema = $record->getSchema();

        $io->title($this->recordInfo($record));

        $names = $schema->fieldNames();
        sort($names);

        $rows = [];
        $headers = ['Field', 'Value'];
        foreach ($names as $name) {
            $rows[] = [$schema->fieldName($name), var_export($record->{$name}, true)];
        }
        $io->table($headers, $rows);

        $names = $schema->relationNames();
        sort($names);

        $relations = [];
        $rows = [];
        $headers = ['Relation', 'Value'];
        foreach ($names as $name) {
            $relation = $record->getRelation($name);
            if ($relation) {
                $rows[] = [$schema->relationName($name), $this->recordInfo($relation)];
                $relations[] = $relation;
            } else {
                $rows[] = [$schema->relationName($name), var_export($relation, true)];
            }
        }
        $io->table($headers, $rows);

        return $relations;
    }
}
