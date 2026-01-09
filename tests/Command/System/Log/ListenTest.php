<?php

namespace MODX\CLI\Tests\Command\System\Log;

use MODX\CLI\Command\System\Log\Listen;
use MODX\CLI\Tests\Configuration\BaseTest;
use Symfony\Component\Console\Tester\CommandTester;

class ListenTest extends BaseTest
{
    protected $command;
    protected $commandTester;
    protected $modx;

    protected function setUp(): void
    {
        $this->modx = $this->createMock('MODX\Revolution\modX');
        $this->command = new Listen();
        $this->command->modx = $this->modx;
        $this->commandTester = new CommandTester($this->command);
    }

    public function testConfigureHasCorrectName()
    {
        $this->assertEquals('system:log:listen', $this->command->getName());
    }

    public function testConfigureHasCorrectDescription()
    {
        $this->assertEquals('Listen to the MODX system log', $this->command->getDescription());
    }

    public function testGetLastLogIdReturnsZeroWhenNoLogs()
    {
        $query = new \stdClass();
        $this->modx->method('newQuery')
            ->with(\MODX\Revolution\modManagerLog::class)
            ->willReturn($query);
        $this->modx->method('getObject')
            ->with(\MODX\Revolution\modManagerLog::class, $query)
            ->willReturn(null);

        $method = new \ReflectionMethod($this->command, 'getLastLogId');
        $method->setAccessible(true);
        $result = $method->invoke($this->command);
        $this->assertEquals(0, $result);
    }

    public function testDisplayLastLogEntriesWithNoLogs()
    {
        $query = new \stdClass();
        $this->modx->method('newQuery')
            ->with(\MODX\Revolution\modManagerLog::class)
            ->willReturn($query);
        $this->modx->method('getCollection')
            ->with(\MODX\Revolution\modManagerLog::class, $query)
            ->willReturn([]);

        $method = new \ReflectionMethod($this->command, 'displayLastLogEntries');
        $method->setAccessible(true);
        $method->invoke($this->command, 5);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('No log entries found', $output);
    }
}
