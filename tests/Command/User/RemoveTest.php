<?php

namespace MODX\CLI\Tests\Command\User;

use MODX\CLI\Command\User\Remove;
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
        $this->modx = $this->createMock('MODX\\Revolution\\modX');

        // Create the command
        $this->command = new Remove();
        $this->command->modx = $this->modx;

        // Create a command tester
        $this->commandTester = new CommandTester($this->command);
    }

    public function testConfigureHasCorrectProcessorPath()
    {
        $processor = $this->getProtectedProperty($this->command, 'processor');
        $this->assertEquals('Security\\User\\Remove', $processor);
    }

    public function testConfigureHasCorrectName()
    {
        $this->assertEquals('user:remove', $this->command->getName());
    }

    public function testConfigureHasCorrectDescription()
    {
        $this->assertEquals('Remove a MODX user', $this->command->getDescription());
    }

    public function testExecuteWithSuccessfulResponseById()
    {
        // Mock user lookup by ID
        $user = $this->createMock('MODX\\Revolution\\modUser');
        $user->method('get')->willReturnCallback(function (...$args) {
            $key = $args[0] ?? null;
            if ($key === 'id') {
                return 5;
            }
            if ($key === 'username') {
                return 'testuser';
            }
            return null;
        });

        $this->modx->expects($this->once())
            ->method('getObject')
            ->with('MODX\\Revolution\\modUser', 5)
            ->willReturn($user);

        // Mock the runProcessor method to return a successful response
        $processorResponse = $this->getMockBuilder('MODX\\Revolution\\Processors\\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $processorResponse->method('getResponse')
            ->willReturn(json_encode([
                'success' => true
            ]));
        $processorResponse->method('isError')->willReturn(false);

        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->with(
                'Security\\User\\Remove',
                $this->callback(function ($properties) {
                    return isset($properties['id']) && $properties['id'] === 5;
                }),
                $this->anything()
            )
            ->willReturn($processorResponse);

        // Execute the command with --force to skip confirmation
        $this->commandTester->execute([
            'identifier' => '5',
            '--force' => true
        ]);

        // Verify the output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('User removed successfully', $output);
    }

    public function testExecuteWithSuccessfulResponseByUsername()
    {
        // Mock user lookup by username
        $user = $this->createMock('MODX\\Revolution\\modUser');
        $user->method('get')->willReturnCallback(function (...$args) {
            $key = $args[0] ?? null;
            if ($key === 'id') {
                return 5;
            }
            if ($key === 'username') {
                return 'testuser';
            }
            return null;
        });

        $this->modx->expects($this->once())
            ->method('getObject')
            ->with('MODX\\Revolution\\modUser', ['username' => 'testuser'])
            ->willReturn($user);

        // Mock the runProcessor method
        $processorResponse = $this->getMockBuilder('MODX\\Revolution\\Processors\\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $processorResponse->method('getResponse')
            ->willReturn(json_encode(['success' => true]));
        $processorResponse->method('isError')->willReturn(false);

        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->willReturn($processorResponse);

        // Execute the command with --force
        $this->commandTester->execute([
            'identifier' => 'testuser',
            '--force' => true
        ]);

        // Verify the output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('User removed successfully', $output);
    }

    public function testExecuteWithNonExistentUser()
    {
        // Mock getObject to return null (user not found)
        $this->modx->expects($this->once())
            ->method('getObject')
            ->with('MODX\\Revolution\\modUser', ['username' => 'nonexistent'])
            ->willReturn(null);

        // Execute the command
        $this->commandTester->execute([
            'identifier' => 'nonexistent',
            '--force' => true
        ]);

        // Verify the output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('User not found: nonexistent', $output);
    }

    public function testExecuteWithFailedResponse()
    {
        // Mock user lookup
        $user = $this->createMock('MODX\\Revolution\\modUser');
        $user->method('get')->willReturnCallback(function (...$args) {
            $key = $args[0] ?? null;
            if ($key === 'id') {
                return 1;
            }
            if ($key === 'username') {
                return 'admin';
            }
            return null;
        });

        $this->modx->expects($this->once())
            ->method('getObject')
            ->willReturn($user);

        // Mock the runProcessor method to return a failed response
        $processorResponse = $this->getMockBuilder('MODX\\Revolution\\Processors\\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $processorResponse->method('getResponse')
            ->willReturn(json_encode([
                'success' => false,
                'message' => 'Cannot remove system user'
            ]));
        $processorResponse->method('isError')->willReturn(true);

        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->willReturn($processorResponse);

        // Execute the command with --force
        $this->commandTester->execute([
            'identifier' => '1',
            '--force' => true
        ]);

        // Verify the output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Failed to remove user', $output);
        $this->assertStringContainsString('Cannot remove system user', $output);
    }

    public function testForceOptionSkipsConfirmation()
    {
        // Mock user lookup
        $user = $this->createMock('MODX\\Revolution\\modUser');
        $user->method('get')->willReturnCallback(function (...$args) {
            $key = $args[0] ?? null;
            if ($key === 'id') {
                return 5;
            }
            if ($key === 'username') {
                return 'testuser';
            }
            return null;
        });

        $this->modx->expects($this->once())
            ->method('getObject')
            ->willReturn($user);

        $processorResponse = $this->getMockBuilder('MODX\\Revolution\\Processors\\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $processorResponse->method('getResponse')
            ->willReturn(json_encode(['success' => true]));
        $processorResponse->method('isError')->willReturn(false);

        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->willReturn($processorResponse);

        $this->commandTester->execute([
            'identifier' => '5',
            '--force' => true
        ]);

        $this->assertEquals(0, $this->commandTester->getStatusCode());
    }

    public function testExecuteWithJsonOption()
    {
        // Mock user lookup
        $user = $this->createMock('MODX\\Revolution\\modUser');
        $user->method('get')->willReturnCallback(function (...$args) {
            $key = $args[0] ?? null;
            if ($key === 'id') {
                return 5;
            }
            if ($key === 'username') {
                return 'testuser';
            }
            return null;
        });

        $this->modx->expects($this->once())
            ->method('getObject')
            ->willReturn($user);

        $processorResponse = $this->getMockBuilder('MODX\\Revolution\\Processors\\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $processorResponse->method('getResponse')
            ->willReturn(json_encode(['success' => true]));
        $processorResponse->method('isError')->willReturn(false);

        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->willReturn($processorResponse);

        $this->commandTester->execute([
            'identifier' => '5',
            '--force' => true,
            '--json' => true
        ]);

        $output = $this->commandTester->getDisplay();
        $data = json_decode($output, true);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('success', $data);
        $this->assertTrue($data['success']);
    }
}
