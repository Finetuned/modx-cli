<?php

namespace MODX\CLI\Tests\Command\Session;

use MODX\CLI\Command\Session\GetList;
use MODX\CLI\Tests\Configuration\BaseTest;
use Symfony\Component\Console\Tester\CommandTester;

class GetListTest extends BaseTest
{
    protected $command;
    protected $commandTester;
    protected $modx;

    protected function setUp(): void
    {
        // Create a mock MODX object
        $this->modx = $this->createMock('MODX\Revolution\modX');

        // Create the command
        $this->command = new GetList();
        $this->command->modx = $this->modx;

        // Create a command tester
        $this->commandTester = new CommandTester($this->command);
    }

    public function testConfigureHasCorrectName()
    {
        $this->assertEquals('session:list', $this->command->getName());
    }

    public function testConfigureHasCorrectDescription()
    {
        $this->assertEquals('Get a list of sessions in MODX', $this->command->getDescription());
    }

    public function testExecuteWithSuccessfulResponse()
    {
        $session = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['get'])
            ->getMock();
        $session->method('get')->willReturnMap([
            ['id', 'abc123'],
            ['access', '1698768000'],
            ['data', 'serialized'],
        ]);

        $this->modx->expects($this->once())
            ->method('getCount')
            ->with('MODX\\Revolution\\modSession', [])
            ->willReturn(1);

        $this->modx->expects($this->once())
            ->method('getCollection')
            ->with('MODX\\Revolution\\modSession', [], $this->anything())
            ->willReturn([$session]);

        // Execute the command
        $this->commandTester->execute([]);

        // Verify the output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('abc123', $output);
        $this->assertStringContainsString('2023-10-31 16:00:00', $output);
    }

    public function testExecuteWithEmptyResults()
    {
        $this->modx->expects($this->once())
            ->method('getCount')
            ->with('MODX\\Revolution\\modSession', [])
            ->willReturn(0);

        $this->modx->expects($this->once())
            ->method('getCollection')
            ->with('MODX\\Revolution\\modSession', [], $this->anything())
            ->willReturn([]);

        // Execute the command
        $this->commandTester->execute([]);

        // Command should execute successfully even with no results
        $this->assertEquals(0, $this->commandTester->getStatusCode());
    }

    public function testExecuteWithJsonOption()
    {
        $session = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['get'])
            ->getMock();
        $session->method('get')->willReturnMap([
            ['id', 'abc123'],
            ['access', '1698768000'],
            ['data', 'serialized'],
        ]);

        $this->modx->expects($this->once())
            ->method('getCount')
            ->with('MODX\\Revolution\\modSession', [])
            ->willReturn(1);

        $this->modx->expects($this->once())
            ->method('getCollection')
            ->with('MODX\\Revolution\\modSession', [], $this->anything())
            ->willReturn([$session]);

        // Execute the command with --json option
        $this->commandTester->execute(['--json' => true]);

        // Verify JSON output
        $output = $this->commandTester->getDisplay();
        $data = json_decode($output, true);
        $this->assertIsArray($data);
        $this->assertEquals(1, $data['total']);
        $this->assertCount(1, $data['results']);
        $this->assertEquals('abc123', $data['results'][0]['id']);
    }

    public function testParseValueFormatsTimestamps()
    {
        // Use reflection to call protected parseValue method
        $reflection = new \ReflectionClass($this->command);
        $method = $reflection->getMethod('parseValue');
        $method->setAccessible(true);

        // Test timestamp formatting for 'access' column
        $timestamp = '1698768000';
        $result = $method->invoke($this->command, $timestamp, 'access');
        $this->assertEquals('2023-10-31 16:00:00', $result);

        // Test non-timestamp column
        $result = $method->invoke($this->command, 'serialized', 'data');
        $this->assertEquals('serialized', $result);
    }

    public function testParseValueHandlesEmptyTimestamps()
    {
        // Use reflection to call protected parseValue method
        $reflection = new \ReflectionClass($this->command);
        $method = $reflection->getMethod('parseValue');
        $method->setAccessible(true);

        // Test empty timestamp for 'access' column
        $result = $method->invoke($this->command, '', 'access');
        $this->assertEquals('', $result);

        // Test string timestamp for 'access' column
        $result = $method->invoke($this->command, '2026-01-09 15:10:00', 'access');
        $this->assertEquals('2026-01-09 15:10:00', $result);

        // Test non-timestamp column with null
        $result = $method->invoke($this->command, null, 'data');
        $this->assertNull($result);
    }
}
