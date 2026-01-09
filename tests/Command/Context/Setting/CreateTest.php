<?php

namespace MODX\CLI\Tests\Command\Context\Setting;

use MODX\CLI\Command\Context\Setting\Create;
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
        $this->assertEquals('Context\Setting\Create', $processor);
    }

    public function testConfigureHasCorrectName()
    {
        $this->assertEquals('context:setting:create', $this->command->getName());
    }

    public function testConfigureHasCorrectDescription()
    {
        $this->assertEquals('Create a context setting', $this->command->getDescription());
    }

    public function testExecuteWithSuccessfulResponse()
    {
        $processorResponse = $this->getMockBuilder('MODX\\Revolution\\Processors\\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $processorResponse->method('getResponse')
            ->willReturn(json_encode([
                'success' => true,
                'object' => ['key' => 'site_name']
            ]));
        $processorResponse->method('isError')->willReturn(false);

        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->willReturn($processorResponse);

        $this->commandTester->execute([
            'context' => 'web',
            'key' => 'site_name',
            '--value' => 'My Site',
            '--namespace' => 'core'
        ]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Context setting created successfully', $output);
    }

    public function testExecuteWithFailedResponse()
    {
        $processorResponse = $this->getMockBuilder('MODX\\Revolution\\Processors\\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $processorResponse->method('getResponse')
            ->willReturn(json_encode([
                'success' => false,
                'message' => 'Error creating context setting'
            ]));
        $processorResponse->method('isError')->willReturn(true);

        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->willReturn($processorResponse);

        $this->commandTester->execute([
            'context' => 'web',
            'key' => 'site_name',
            '--value' => 'My Site',
            '--namespace' => 'core'
        ]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Failed to create context setting', $output);
        $this->assertStringContainsString('Error creating context setting', $output);
    }

    public function testBeforeRunPopulatesProperties()
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
                'Context\\Setting\\Create',
                $this->callback(function($properties) {
                    return isset($properties['fk'])
                        && $properties['fk'] === 'web'
                        && isset($properties['context_key'])
                        && $properties['context_key'] === 'web'
                        && isset($properties['key'])
                        && $properties['key'] === 'site_name'
                        && isset($properties['value'])
                        && $properties['value'] === 'My Site'
                        && isset($properties['area'])
                        && $properties['area'] === 'general'
                        && isset($properties['namespace'])
                        && $properties['namespace'] === 'core'
                        && isset($properties['xtype'])
                        && $properties['xtype'] === 'textfield'
                        && isset($properties['name'])
                        && $properties['name'] === 'Site Name'
                        && isset($properties['description'])
                        && $properties['description'] === 'Site name for web';
                }),
                $this->anything()
            )
            ->willReturn($processorResponse);

        $this->commandTester->execute([
            'context' => 'web',
            'key' => 'site_name',
            '--value' => 'My Site',
            '--area' => 'general',
            '--namespace' => 'core',
            '--xtype' => 'textfield',
            '--name' => 'Site Name',
            '--description' => 'Site name for web'
        ]);

        $this->assertEquals(0, $this->commandTester->getStatusCode());
    }
}
