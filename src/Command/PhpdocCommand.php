<?php

namespace CommunityDS\Deputy\Api\Console\Command;

use InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class PhpdocCommand extends BaseCommand
{
    // phpcs:ignore
    protected static $defaultName = 'phpdoc';

    /**
     * @var boolean Indicates if new line should be added before `@property` declaration
     */
    private $emptyLine = false;

    /**
     * @var string[] List of names already found
     */
    private $properties = [];

    public function configure()
    {
        parent::configure();
        $this->setDescription('Outputs the PHPDoc @property tags for a specific resource');
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
        $errors = [];

        $config = $schema->toConfig();
        $modelClass = $schema->modelClass;

        $io->title("PHPDoc for {$resourceName} ({$modelClass})");

        $this->emptyLine = false;
        $names = array_keys($config['fields']);
        sort($names);
        foreach ($names as $name) {
            if ($name == 'Id') {
                continue;
            }

            $dataType = $schema->fieldDataType($name);
            if ($dataType == null) {
                continue;
            }

            $this->outputProperty($io, $dataType->phpType(), $name);
        }

        $this->emptyLine = true;
        $names = array_keys($config['joins']);
        sort($names);
        foreach ($names as $name) {
            $relatedResource = $schema->joinResource($name);
            if ($relatedResource == null) {
                continue;
            }

            $relatedSchema = $this->getWrapper()->schema->resource($relatedResource);
            if ($relatedSchema == null) {
                $errors[] = "Unknown resource '{$relatedResource}' for relation '{$name}'";
                continue;
            }

            $relatedClass = $relatedSchema->modelClass;
            if ($relatedClass == null) {
                continue;
            }

            $this->outputProperty($io, $relatedClass, $name);
        }

        $this->emptyLine = true;
        $names = array_keys($config['assocs']);
        sort($names);
        foreach ($names as $name) {
            $relatedResource = $schema->assocResource($name);
            if ($relatedResource == null) {
                continue;
            }

            $relatedSchema = $this->getWrapper()->schema->resource($relatedResource);
            if ($relatedSchema == null) {
                $errors[] = "Unknown resource '{$relatedResource}' for relation '{$name}'";
                continue;
            }

            $relatedClass = $relatedSchema->modelClass;
            if ($relatedClass == null) {
                continue;
            }

            $this->outputProperty($io, $relatedClass, $name);
        }

        $exitCode = static::SUCCESS;
        foreach ($this->properties as $name => $count) {
            if ($count > 1) {
                $io->warning('Duplicate properties: $' . $name);
                $exitCode = static::FAILURE;
            }
        }

        foreach ($errors as $error) {
            $io->error($error);
            $exitCode = static::FAILURE;
        }

        return $exitCode;
    }

    protected function localiseClass($class)
    {
        return str_replace('CommunityDS\Deputy\Api\Model\\', '', $class);
    }

    protected function outputProperty(SymfonyStyle $io, $phpType, $name)
    {
        if ($this->emptyLine) {
            $io->writeln(' * ');
            $this->emptyLine = false;
        }

        $phpType = $this->localiseClass($phpType);
        if ($phpType == '\DateTime') {
            $phpType = 'DateTime';
        }

        $property = strtolower(substr($name, 0, 1)) . substr($name, 1);
        if (!isset($this->properties[$property])) {
            $this->properties[$property] = 0;
        }
        $this->properties[$property]++;
        $io->writeln(" * @property {$phpType} \${$property}");
    }
}
