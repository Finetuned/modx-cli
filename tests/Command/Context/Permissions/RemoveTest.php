<?php

namespace MODX\CLI\Tests\Command\Context\Permissions;

use MODX\CLI\Command\Context\Permissions\Remove;
use MODX\CLI\Tests\Configuration\BaseTest;
use Symfony\Component\Console\Tester\CommandTester;

class RemoveTest extends BaseTest
{
    protected $command;
    protected $commandTester;
    protected $modx;

    protected function setUp(): void
    {
        // Create a mock MODX object
        $this->modx = $this->createMock('MODX\Revolution\modX');
        
        // Create the command
        $this->command = new Remove();
        $this->command->modx = $this->modx;
        
        // Create a command tester
        $this->commandTester = new CommandTester($this->command);
        $this->commandTester->setInputs(['yes']);
    }

    public function testConfigureHasCorrectProcessorPath()
    {
        $processor = $this->getProtectedProperty($this->command, 'processor');
        $this->assertEquals('Security\\Access\\UserGroup\\Context\\Remove', $processor);
    }

    public function testConfigureHasCorrectName()
    {
        $this->assertEquals('context:permissions:remove', $this->command->getName());
    }

    public function testConfigureHasCorrectDescription()
    {
        $this->assertEquals('Remove a context access permission', $this->command->getDescription());
    }

    public function testExecuteWithSuccessfulResponse()
    {
        $acl = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['get'])
            ->getMock();
        $acl->method('get')->willReturnMap([
            ['target', 'web'],
        ]);

        $this->modx->expects($this->once())
            ->method('getObject')
            ->with('MODX\\Revolution\\modAccessContext', '10')
            ->willReturn($acl);

        $processorResponse = $this->getMockBuilder('MODX\\Revolution\\Processors\\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $processorResponse->method('getResponse')
            ->willReturn(json_encode(['success' => true]));
        $processorResponse->method('isError')->willReturn(false);

        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->with(
                'Security\\Access\\UserGroup\\Context\\Remove',
                $this->callback(function($properties) {
                    return isset($properties['id']) && $properties['id'] === '10';
                }),
                $this->anything()
            )
            ->willReturn($processorResponse);

        $this->commandTester->execute([
            'context' => 'web',
            'id' => '10',
            '--force' => true
        ]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Context access permission removed successfully', $output);
    }

    public function testExecuteWithFailedResponse()
    {
        $acl = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['get'])
            ->getMock();
        $acl->method('get')->willReturnMap([
            ['target', 'web'],
        ]);

        $this->modx->expects($this->once())
            ->method('getObject')
            ->with('MODX\\Revolution\\modAccessContext', '10')
            ->willReturn($acl);

        $processorResponse = $this->getMockBuilder('MODX\\Revolution\\Processors\\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $processorResponse->method('getResponse')
            ->willReturn(json_encode([
                'success' => false,
                'message' => 'Error removing access permission'
            ]));
        $processorResponse->method('isError')->willReturn(true);

        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->willReturn($processorResponse);

        $this->commandTester->execute([
            'context' => 'web',
            'id' => '10',
            '--force' => true
        ]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Failed to remove context access permission', $output);
        $this->assertStringContainsString('Error removing access permission', $output);
    }

    public function testExecuteWithNonExistentEntry()
    {
        $this->modx->expects($this->once())
            ->method('getObject')
            ->with('MODX\\Revolution\\modAccessContext', '10')
            ->willReturn(null);

        $this->commandTester->execute([
            'context' => 'web',
            'id' => '10',
            '--force' => true
        ]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Access control entry with ID 10 not found', $output);
    }

    public function testExecuteWithMismatchedContext()
    {
        $acl = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['get'])
            ->getMock();
        $acl->method('get')->willReturnMap([
            ['target', 'mgr'],
        ]);

        $this->modx->expects($this->once())
            ->method('getObject')
            ->with('MODX\\Revolution\\modAccessContext', '10')
            ->willReturn($acl);

        $this->commandTester->execute([
            'context' => 'web',
            'id' => '10',
            '--force' => true
        ]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString("Access control entry 10 is not for context 'web'", $output);
    }
}
