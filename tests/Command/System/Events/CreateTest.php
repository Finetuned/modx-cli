<?php

namespace MODX\CLI\Tests\Command\System\Events;

use MODX\CLI\Command\System\Events\Create;
use MODX\CLI\Tests\Configuration\BaseTest;
use Symfony\Component\Console\Tester\CommandTester;

class CreateTest extends BaseTest
{
    protected $command;
    protected $commandTester;
    protected $modx;

    protected function setUp(): void
    {
        $this->modx = $this->createMock('MODX\Revolution\modX');
        $this->command = new Create();
        $this->command->modx = $this->modx;
        $this->commandTester = new CommandTester($this->command);
    }

    public function testConfigureHasCorrectProcessorPath()
    {
        $processor = $this->getProtectedProperty($this->command, 'processor');
        $this->assertEquals('System\\Event\\Create', $processor);
    }

    public function testConfigureHasCorrectName()
    {
        $this->assertEquals('system:event:create', $this->command->getName());
    }

    public function testConfigureHasCorrectDescription()
    {
        $this->assertEquals('Create a system event in MODX', $this->command->getDescription());
    }

    public function testConfigureHasArgumentsAndOptions()
    {
        $definition = $this->command->getDefinition();
        $this->assertTrue($definition->hasArgument('name'));
        $this->assertTrue($definition->getArgument('name')->isRequired());
        $this->assertTrue($definition->hasOption('service'));
        $this->assertTrue($definition->getOption('service')->isValueRequired());
        $this->assertTrue($definition->hasOption('groupname'));
        $this->assertTrue($definition->getOption('groupname')->isValueRequired());
    }

    public function testExecuteWithOptions()
    {
        $response = $this->createProcessorResponse([
            'success' => true,
            'object' => ['name' => 'OnPageNotFound']
        ]);

        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->with(
                'System\\Event\\Create',
                $this->callback(function($properties) {
                    return $properties['name'] === 'OnPageNotFound'
                        && $properties['service'] === '2'
                        && $properties['groupname'] === 'Custom';
                }),
                $this->anything()
            )
            ->willReturn($response);

        $this->commandTester->execute([
            'name' => 'OnPageNotFound',
            '--service' => '2',
            '--groupname' => 'Custom'
        ]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Event created successfully', $output);
        $this->assertStringContainsString('Event name: OnPageNotFound', $output);
    }

    public function testExecuteWithJsonOption()
    {
        $response = $this->createProcessorResponse([
            'success' => true,
            'object' => ['name' => 'OnDocFormSave']
        ]);

        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->willReturn($response);

        $this->commandTester->execute([
            'name' => 'OnDocFormSave',
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
