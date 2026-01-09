<?php

namespace MODX\CLI\Tests\Command\Context\Permissions;

use MODX\CLI\Command\Context\Permissions\Update;
use MODX\CLI\Tests\Configuration\BaseTest;
use Symfony\Component\Console\Tester\CommandTester;

class UpdateTest extends BaseTest
{
    protected $command;
    protected $commandTester;
    protected $modx;

    protected function setUp(): void
    {
        // Create a mock MODX object
        $this->modx = $this->createMock('MODX\Revolution\modX');
        
        // Create the command
        $this->command = new Update();
        $this->command->modx = $this->modx;
        
        // Create a command tester
        $this->commandTester = new CommandTester($this->command);
    }

    public function testConfigureHasCorrectProcessorPath()
    {
        $processor = $this->getProtectedProperty($this->command, 'processor');
        $this->assertEquals('Security\\Access\\UserGroup\\Context\\Update', $processor);
    }

    public function testConfigureHasCorrectName()
    {
        $this->assertEquals('context:permissions:update', $this->command->getName());
    }

    public function testConfigureHasCorrectDescription()
    {
        $this->assertEquals('Update a context permission', $this->command->getDescription());
    }

    public function testExecuteWithSuccessfulResponse()
    {
        $acl = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['get'])
            ->getMock();
        $acl->method('get')->willReturnMap([
            ['target', 'web'],
            ['principal', 2],
            ['policy', 1],
            ['authority', 9999],
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
                'Security\\Access\\UserGroup\\Context\\Update',
                $this->callback(function($properties) {
                    return isset($properties['id'])
                        && $properties['id'] === '10'
                        && isset($properties['target'])
                        && $properties['target'] === 'web'
                        && isset($properties['principal'])
                        && $properties['principal'] === 2
                        && isset($properties['policy'])
                        && $properties['policy'] === 1
                        && isset($properties['authority'])
                        && $properties['authority'] === 1234;
                }),
                $this->anything()
            )
            ->willReturn($processorResponse);

        $this->commandTester->execute([
            'context' => 'web',
            'id' => '10',
            '--authority' => '1234'
        ]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Context permission updated successfully', $output);
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
            '--authority' => '1234'
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
            ['principal', 2],
            ['policy', 1],
            ['authority', 9999],
        ]);

        $this->modx->expects($this->once())
            ->method('getObject')
            ->with('MODX\\Revolution\\modAccessContext', '10')
            ->willReturn($acl);

        $this->commandTester->execute([
            'context' => 'web',
            'id' => '10'
        ]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString("Access control entry 10 is not for context 'web'", $output);
    }
}
