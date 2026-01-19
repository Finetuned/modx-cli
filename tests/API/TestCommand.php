<?php

namespace MODX\CLI\Tests\API;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Test command class for testing the registry
 */
class TestCommand extends Command
{
    protected static $defaultName = 'test:command';

    protected function configure(): void
    {
        $this->setDescription('Test command');
        $this->setHelp('This is a test command');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        return 0;
    }
}
