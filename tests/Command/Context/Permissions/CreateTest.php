<?php

namespace MODX\CLI\Tests\Command\Context\Permissions;

use MODX\CLI\Command\Context\Permissions\Create;
use MODX\CLI\Tests\Configuration\BaseTest;
use Symfony\Component\Console\Tester\CommandTester;

class CreateTest extends BaseTest
{
    protected $command;
    protected $commandTester;
    protected $modx;

    protected function setUp(): void
    {
        // Create a mock MODX object
        $this->modx = $this->createMock('MODX\Revolution\modX');
        
        // Create the command
        $this->command = new Create();
        $this->command->modx = $this->modx;
        
        // Create a command tester
        $this->commandTester = new CommandTester($this->command);
    }

    public function testConfigureHasCorrectProcessorPath()
    {
        $processor = $this->getProtectedProperty($this->command, 'processor');
        $this->assertEquals('Security\\Access\\UserGroup\\Context\\Create', $processor);
    }

    public function testConfigureHasCorrectName()
    {
        $this->assertEquals('context:permissions:create', $this->command->getName());
    }

    public function testConfigureHasCorrectDescription()
    {
        $this->assertEquals('Create a context access permission', $this->command->getDescription());
    }

    public function testExecuteWithSuccessfulResponse()
    {
        $processorResponse = $this->getMockBuilder('MODX\\Revolution\\Processors\\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $processorResponse->method('getResponse')
            ->willReturn(json_encode(['success' => true]));
        $processorResponse->method('isError')->willReturn(false);

        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->with(
                'Security\\Access\\UserGroup\\Context\\Create',
                $this->callback(function($properties) {
                    return isset($properties['target'])
                        && $properties['target'] === 'web'
                        && isset($properties['principal'])
                        && $properties['principal'] === '2'
                        && isset($properties['policy'])
                        && $properties['policy'] === '1'
                        && isset($properties['authority'])
                        && $properties['authority'] === 9999;
                }),
                $this->anything()
            )
            ->willReturn($processorResponse);

        $this->commandTester->execute([
            'context' => 'web',
            'usergroup' => '2',
            'policy' => '1',
            '--authority' => '9999'
        ]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Context permission created successfully', $output);
    }

    public function testExecuteWithFailedResponse()
    {
        $processorResponse = $this->getMockBuilder('MODX\\Revolution\\Processors\\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $processorResponse->method('getResponse')
            ->willReturn(json_encode([
                'success' => false,
                'message' => 'Error creating access permission'
            ]));
        $processorResponse->method('isError')->willReturn(true);

        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->willReturn($processorResponse);

        $this->commandTester->execute([
            'context' => 'web',
            'usergroup' => '2',
            'policy' => '1',
            '--authority' => '9999'
        ]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Failed to create context permission', $output);
        $this->assertStringContainsString('Error creating access permission', $output);
    }
}
