<?php

namespace MODX\CLI\Tests\Command\Package\Provider;

use MODX\CLI\Command\Package\Provider\Info;
use MODX\CLI\Tests\Configuration\BaseTest;
use Symfony\Component\Console\Tester\CommandTester;

class InfoTest extends BaseTest
{
    protected $command;
    protected $commandTester;
    protected $modx;

    protected function setUp(): void
    {
        // Create a mock MODX object
        $this->modx = $this->createMock('MODX\Revolution\modX');

        // Create the command
        $this->command = new Info();
        $this->command->modx = $this->modx;

        // Create a command tester
        $this->commandTester = new CommandTester($this->command);
    }

    public function testConfigureHasCorrectProcessorPath()
    {
        $processor = $this->getProtectedProperty($this->command, 'processor');
        $this->assertEquals('Workspace\\Providers\\GetList', $processor);
    }

    public function testConfigureHasCorrectName()
    {
        $this->assertEquals('package:provider:info', $this->command->getName());
    }

    public function testConfigureHasCorrectDescription()
    {
        $this->assertEquals('Get information about a package provider in MODX', $this->command->getDescription());
    }

    public function testConfigureHasArgumentAndFormatOption()
    {
        $definition = $this->command->getDefinition();
        $this->assertTrue($definition->hasArgument('id'));
        $this->assertTrue($definition->getArgument('id')->isRequired());

        $this->assertTrue($definition->hasOption('format'));
        $this->assertTrue($definition->getOption('format')->isValueRequired());
        $this->assertEquals('f', $definition->getOption('format')->getShortcut());
        $this->assertEquals('table', $definition->getOption('format')->getDefault());
    }

    public function testExecuteWithJsonFormat()
    {
        $response = $this->createProcessorResponse([
            'success' => true,
            'results' => [
                [
                    'id' => 5,
                    'name' => 'modx.com',
                    'description' => 'Main provider'
                ]
            ]
        ]);

        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->with('Workspace\\Providers\\GetList', $this->callback(function ($properties) {
                return $properties['id'] === '5';
            }))
            ->willReturn($response);

        $this->commandTester->execute([
            'id' => '5',
            '--format' => 'json'
        ]);

        $output = $this->commandTester->getDisplay();
        $decoded = json_decode($output, true);
        $this->assertNotNull($decoded);
        $this->assertEquals(5, $decoded['id']);
        $this->assertEquals('modx.com', $decoded['name']);
    }

    public function testExecuteWithProviderNotFound()
    {
        $response = $this->createProcessorResponse([
            'success' => true,
            'results' => []
        ]);

        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->willReturn($response);

        $this->commandTester->execute([
            'id' => '999'
        ]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Provider not found', $output);
        $this->assertEquals(1, $this->commandTester->getStatusCode());
    }

    private function createProcessorResponse(array $payload, bool $isError = false)
    {
        $response = $this->getMockBuilder('MODX\\Revolution\\Processors\\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $response->method('getResponse')
            ->willReturn(json_encode($payload));
        $response->method('isError')
            ->willReturn($isError);

        return $response;
    }
}
