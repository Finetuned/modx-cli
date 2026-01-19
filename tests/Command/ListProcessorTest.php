<?php

namespace MODX\CLI\Tests\Command;

use MODX\CLI\Command\ListProcessor;
use MODX\CLI\Tests\Configuration\BaseTest;
use MODX\CLI\Application;
use Symfony\Component\Console\Tester\CommandTester;

class ListProcessorTest extends BaseTest
{
    protected $command;
    protected $commandTester;
    protected $modx;

    protected function setUp(): void
    {
        $this->modx = $this->createMock('MODX\Revolution\modX');

        $this->command = new class extends ListProcessor {
            protected $processor = 'test/processor';
            protected $headers = ['id', 'name', 'description'];
            protected $name = 'test:list';
            protected $description = 'Test list command';
        };

        $this->command->modx = $this->modx;

        // Create a command tester without using the Application class to avoid conflicts
        $this->commandTester = new CommandTester($this->command);
    }

    public function testHasPaginationOptions()
    {
        $definition = $this->command->getDefinition();

        $this->assertTrue($definition->hasOption('limit'));
        $limitOption = $definition->getOption('limit');
        $this->assertEquals('l', $limitOption->getShortcut());
        $this->assertEquals(10, $limitOption->getDefault());

        $this->assertTrue($definition->hasOption('start'));
        $startOption = $definition->getOption('start');
        $this->assertNull($startOption->getShortcut()); // No shortcut to avoid conflict with --ssh
        $this->assertEquals(0, $startOption->getDefault());
    }

    public function testExecuteWithPaginationParameters()
    {
        $processorResponse = $this->getMockBuilder('MODX\Revolution\Processors\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $processorResponse->method('getResponse')
            ->willReturn(json_encode([
                'success' => true,
                'total' => 25,
                'results' => [
                    ['id' => 11, 'name' => 'Item 11', 'description' => 'Description 11'],
                    ['id' => 12, 'name' => 'Item 12', 'description' => 'Description 12'],
                ]
            ]));
        $processorResponse->method('isError')->willReturn(false);

        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->with(
                'test/processor',
                $this->callback(function ($properties) {
                    return isset($properties['limit']) && $properties['limit'] === 5 &&
                           isset($properties['start']) && $properties['start'] === 10;
                }),
                $this->anything()
            )
            ->willReturn($processorResponse);

        $this->commandTester->execute([
            '--limit' => '5',
            '--start' => '10'
        ]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('displaying 2 item(s)', $output);
        $this->assertStringContainsString('of 25', $output);
    }
}
