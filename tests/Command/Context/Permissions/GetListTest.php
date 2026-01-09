<?php

namespace MODX\CLI\Tests\Command\Context\Permissions;

use MODX\CLI\Command\Context\Permissions\GetList;
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

    public function testConfigureHasCorrectProcessorPath()
    {
        $processor = $this->getProtectedProperty($this->command, 'processor');
        $this->assertEquals('Security\\Access\\GetList', $processor);
    }

    public function testConfigureHasCorrectName()
    {
        $this->assertEquals('context:permissions', $this->command->getName());
    }

    public function testConfigureHasCorrectDescription()
    {
        $this->assertEquals('List context access permissions for a context', $this->command->getDescription());
    }

    public function testConfigureHasCorrectHeaders()
    {
        $headers = $this->getProtectedProperty($this->command, 'headers');
        $this->assertContains('usergroup', $headers);
        $this->assertContains('policy_name', $headers);
        $this->assertContains('authority', $headers);
    }

    public function testExecuteWithSuccessfulResponse()
    {
        $processorResponse = $this->getMockBuilder('MODX\\Revolution\\Processors\\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $processorResponse->method('getResponse')
            ->willReturn(json_encode([
                'success' => true,
                'total' => 1,
                'results' => [
                    [
                        'principal' => 2,
                        'principal_name' => 'Administrators',
                        'policy_name' => 'Context',
                        'authority' => 9999
                    ]
                ]
            ]));
        $processorResponse->method('isError')->willReturn(false);

        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->with(
                'Security\\Access\\GetList',
                $this->callback(function($properties) {
                    return isset($properties['type'])
                        && $properties['type'] === 'MODX\\Revolution\\modAccessContext'
                        && isset($properties['target'])
                        && $properties['target'] === 'web';
                }),
                $this->anything()
            )
            ->willReturn($processorResponse);

        $this->commandTester->execute([
            'context' => 'web'
        ]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Administrators', $output);
        $this->assertStringContainsString('Context', $output);
    }

    public function testExecuteWithJsonOption()
    {
        $processorResponse = $this->getMockBuilder('MODX\\Revolution\\Processors\\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $processorResponse->method('getResponse')
            ->willReturn(json_encode([
                'success' => true,
                'total' => 1,
                'results' => [
                    [
                        'principal' => 2,
                        'principal_name' => 'Administrators',
                        'policy_name' => 'Context',
                        'authority' => 9999
                    ]
                ]
            ]));
        $processorResponse->method('isError')->willReturn(false);

        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->willReturn($processorResponse);

        $this->commandTester->execute([
            'context' => 'web',
            '--json' => true
        ]);

        $output = $this->commandTester->getDisplay();
        $data = json_decode($output, true);
        $this->assertIsArray($data);
        $this->assertEquals(1, $data['total']);
        $this->assertCount(1, $data['results']);
        $this->assertEquals('Administrators', $data['results'][0]['principal_name']);
    }
}
