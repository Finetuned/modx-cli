<?php

namespace MODX\CLI\Tests\Command\System\Log;

use MODX\CLI\Command\System\Log\Listen;
use MODX\CLI\Tests\Configuration\BaseTest;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
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
        $query = $this->makeQueryMock();
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
        $query = $this->makeQueryMock();
        $this->modx->method('newQuery')
            ->with(\MODX\Revolution\modManagerLog::class)
            ->willReturn($query);
        $this->modx->method('getCollection')
            ->with(\MODX\Revolution\modManagerLog::class, $query)
            ->willReturn([]);

        $output = $this->setCommandIO([]);

        $method = new \ReflectionMethod($this->command, 'displayLastLogEntries');
        $method->setAccessible(true);
        $method->invoke($this->command, 5);

        $this->assertStringContainsString('No log entries found', $output->fetch());
    }

    public function testDisplayLastLogEntriesWithNoLogsJsonOutput()
    {
        $query = $this->makeQueryMock();
        $this->modx->method('newQuery')
            ->with(\MODX\Revolution\modManagerLog::class)
            ->willReturn($query);
        $this->modx->method('getCollection')
            ->with(\MODX\Revolution\modManagerLog::class, $query)
            ->willReturn([]);

        $output = $this->setCommandIO(['--json' => true]);

        $method = new \ReflectionMethod($this->command, 'displayLastLogEntries');
        $method->setAccessible(true);
        $method->invoke($this->command, 5);

        $decoded = json_decode($output->fetch(), true);
        $this->assertEquals(0, $decoded['total']);
        $this->assertEquals([], $decoded['results']);
    }

    private function setCommandIO(array $inputOptions): BufferedOutput
    {
        $input = new ArrayInput($inputOptions, $this->command->getDefinition());
        $output = new BufferedOutput();

        $inputProperty = new \ReflectionProperty($this->command, 'input');
        $inputProperty->setAccessible(true);
        $inputProperty->setValue($this->command, $input);

        $outputProperty = new \ReflectionProperty($this->command, 'output');
        $outputProperty->setAccessible(true);
        $outputProperty->setValue($this->command, $output);

        return $output;
    }

    private function makeQueryMock(): \stdClass
    {
        return $this->getMockBuilder(\stdClass::class)
            ->addMethods(['sortby', 'limit'])
            ->getMock();
    }
}
