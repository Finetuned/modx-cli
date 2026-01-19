<?php

namespace MODX\CLI\Tests\Command\Package\Provider;

use MODX\CLI\Command\Package\Provider\Add;
use MODX\CLI\Tests\Configuration\BaseTest;
use Symfony\Component\Console\Tester\CommandTester;

class AddTest extends BaseTest
{
    protected $command;
    protected $commandTester;
    protected $modx;

    protected function setUp(): void
    {
        // Create a mock MODX object
        $this->modx = $this->createMock('MODX\Revolution\modX');

        // Create the command
        $this->command = new Add();
        $this->command->modx = $this->modx;

        // Create a command tester
        $this->commandTester = new CommandTester($this->command);
    }

    public function testConfigureHasCorrectProcessorPath()
    {
        $processor = $this->getProtectedProperty($this->command, 'processor');
        $this->assertEquals('Workspace\\Providers\\Create', $processor);
    }

    public function testConfigureHasCorrectName()
    {
        $this->assertEquals('package:provider:add', $this->command->getName());
    }

    public function testConfigureHasCorrectDescription()
    {
        $this->assertEquals('Add a package provider in MODX', $this->command->getDescription());
    }

    public function testConfigureHasArgumentsAndOptions()
    {
        $definition = $this->command->getDefinition();
        $this->assertTrue($definition->hasArgument('name'));
        $this->assertTrue($definition->getArgument('name')->isRequired());
        $this->assertTrue($definition->hasArgument('service_url'));
        $this->assertTrue($definition->getArgument('service_url')->isRequired());

        $this->assertTrue($definition->hasOption('username'));
        $this->assertTrue($definition->getOption('username')->isValueRequired());
        $this->assertTrue($definition->hasOption('password'));
        $this->assertTrue($definition->getOption('password')->isValueRequired());
        $this->assertTrue($definition->hasOption('description'));
        $this->assertTrue($definition->getOption('description')->isValueRequired());
    }

    public function testExecuteWithSuccessfulResponse()
    {
        $response = $this->createProcessorResponse([
            'success' => true,
            'object' => [
                'id' => 10
            ]
        ]);

        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->with(
                'Workspace\\Providers\\Create',
                $this->callback(function ($properties) {
                    return $properties['name'] === 'Provider Name'
                        && $properties['service_url'] === 'https://example.com'
                        && $properties['username'] === 'user'
                        && $properties['password'] === 'pass'
                        && $properties['description'] === 'Test provider';
                }),
                $this->anything()
            )
            ->willReturn($response);

        $this->commandTester->execute([
            'name' => 'Provider Name',
            'service_url' => 'https://example.com',
            '--username' => 'user',
            '--password' => 'pass',
            '--description' => 'Test provider'
        ]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Provider added successfully', $output);
        $this->assertStringContainsString('Provider ID: 10', $output);
    }

    public function testExecuteWithJsonOption()
    {
        $response = $this->createProcessorResponse([
            'success' => true,
            'object' => [
                'id' => 10
            ]
        ]);

        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->willReturn($response);

        $this->commandTester->execute([
            'name' => 'Provider Name',
            'service_url' => 'https://example.com',
            '--json' => true
        ]);

        $output = $this->commandTester->getDisplay();
        $decoded = json_decode($output, true);
        $this->assertNotNull($decoded);
        $this->assertTrue($decoded['success']);
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
