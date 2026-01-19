<?php

namespace MODX\CLI\Tests\Command\User;

use MODX\CLI\Command\User\Update;
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
        $this->assertEquals('Security\\User\\Update', $processor);
    }

    public function testConfigureHasCorrectName()
    {
        $this->assertEquals('user:update', $this->command->getName());
    }

    public function testConfigureHasCorrectDescription()
    {
        $this->assertEquals('Update a MODX user', $this->command->getDescription());
    }

    public function testExecuteWithSuccessfulResponseById()
    {
        $this->stubUserLookupById(1);

        // Mock Update processor
        $updateResponse = $this->getMockBuilder('MODX\\Revolution\\Processors\\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $updateResponse->method('getResponse')
            ->willReturn(json_encode([
                'success' => true,
                'object' => [
                    'id' => 1,
                    'username' => 'admin'
                ]
            ]));
        $updateResponse->method('isError')->willReturn(false);

        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->willReturn($updateResponse);

        // Execute the command
        $this->commandTester->execute([
            'identifier' => '1',
            '--email' => 'newemail@example.com'
        ]);

        // Verify the output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('User updated successfully', $output);
    }

    public function testExecuteWithSuccessfulResponseByUsername()
    {
        // Mock user lookup by username
        $user = $this->createMock('MODX\\Revolution\\modUser');
        $user->method('get')->willReturnCallback(function ($key) {
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
            ->with(
                'MODX\\Revolution\\modUser',
                ['username' => 'admin']
            )
            ->willReturn($user);

        // Mock Update processor
        $updateResponse = $this->getMockBuilder('MODX\\Revolution\\Processors\\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $updateResponse->method('getResponse')
            ->willReturn(json_encode([
                'success' => true,
                'object' => [
                    'id' => 1,
                    'username' => 'admin'
                ]
            ]));
        $updateResponse->method('isError')->willReturn(false);

        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->willReturn($updateResponse);

        $this->commandTester->execute([
            'identifier' => 'admin',
            '--fullname' => 'Updated Name'
        ]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('User updated successfully', $output);
    }

    public function testExecuteWithNonExistentUser()
    {
        // Mock getObject to return null (user not found)
        $this->modx->expects($this->once())
            ->method('getObject')
            ->with(
                'MODX\\Revolution\\modUser',
                ['username' => 'nonexistent']
            )
            ->willReturn(null);

        $this->commandTester->execute([
            'identifier' => 'nonexistent',
            '--email' => 'test@example.com'
        ]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('User not found: nonexistent', $output);
    }

    public function testExecuteWithMultipleOptions()
    {
        $this->stubUserLookupById(1);

        $updateResponse = $this->getMockBuilder('MODX\\Revolution\\Processors\\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $updateResponse->method('getResponse')
            ->willReturn(json_encode(['success' => true, 'object' => ['id' => 1, 'username' => 'testuser']]));
        $updateResponse->method('isError')->willReturn(false);

        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->with(
                'Security\\User\\Update',
                $this->callback(function ($properties) {
                    return isset($properties['id']) && $properties['id'] === 1 &&
                           isset($properties['username']) && $properties['username'] === 'newusername' &&
                           isset($properties['email']) && $properties['email'] === 'new@example.com' &&
                           isset($properties['fullname']) && $properties['fullname'] === 'New Name';
                }),
                $this->anything()
            )
            ->willReturn($updateResponse);

        $this->commandTester->execute([
            'identifier' => '1',
            '--username' => 'newusername',
            '--email' => 'new@example.com',
            '--fullname' => 'New Name'
        ]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('User updated successfully', $output);
    }

    public function testExecuteWithFailedResponse()
    {
        $this->stubUserLookupById(1);

        // Mock failed Update processor
        $updateResponse = $this->getMockBuilder('MODX\\Revolution\\Processors\\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $updateResponse->method('getResponse')
            ->willReturn(json_encode([
                'success' => false,
                'message' => 'Username already exists'
            ]));
        $updateResponse->method('isError')->willReturn(true);

        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->willReturn($updateResponse);

        // Execute the command
        $this->commandTester->execute([
            'identifier' => '1',
            '--username' => 'admin'
        ]);

        // Verify the output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Failed to update user', $output);
        $this->assertStringContainsString('Username already exists', $output);
    }

    public function testUpdateWithSingleOption()
    {
        $this->stubUserLookupById(1);

        // Mock Update processor
        $updateResponse = $this->getMockBuilder('MODX\\Revolution\\Processors\\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $updateResponse->method('getResponse')
            ->willReturn(json_encode(['success' => true, 'object' => ['id' => 1]]));
        $updateResponse->method('isError')->willReturn(false);

        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->willReturn($updateResponse);

        $this->commandTester->execute([
            'identifier' => '1',
            '--active' => '0'
        ]);

        $this->assertEquals(0, $this->commandTester->getStatusCode());
    }

    public function testExecuteWithJsonOption()
    {
        $this->stubUserLookupById(1);

        $updateResponse = $this->getMockBuilder('MODX\\Revolution\\Processors\\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $updateResponse->method('getResponse')
            ->willReturn(json_encode([
                'success' => true,
                'object' => ['id' => 1, 'username' => 'admin']
            ]));
        $updateResponse->method('isError')->willReturn(false);

        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->willReturn($updateResponse);

        $this->commandTester->execute([
            'identifier' => '1',
            '--email' => 'new@example.com',
            '--json' => true
        ]);

        $output = $this->commandTester->getDisplay();
        $data = json_decode($output, true);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('success', $data);
        $this->assertTrue($data['success']);
    }

    public function testBeforeRunSetsPasswordNotifyMethod()
    {
        $this->stubUserLookupById(1);

        $updateResponse = $this->getMockBuilder('MODX\\Revolution\\Processors\\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $updateResponse->method('getResponse')
            ->willReturn(json_encode(['success' => true, 'object' => ['id' => 1]]));
        $updateResponse->method('isError')->willReturn(false);

        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->with(
                'Security\\User\\Update',
                $this->callback(function ($properties) {
                    return isset($properties['passwordnotifymethod']) &&
                           $properties['passwordnotifymethod'] === 'none';
                }),
                $this->anything()
            )
            ->willReturn($updateResponse);

        $this->commandTester->execute([
            'identifier' => '1',
            '--email' => 'test@example.com'
        ]);

        $this->assertEquals(0, $this->commandTester->getStatusCode());
    }

    private function stubUserLookupById(int $id, array $values = []): void
    {
        $user = $this->createMock('MODX\\Revolution\\modUser');
        $user->method('get')->willReturnCallback(function ($key) use ($id, $values) {
            if ($key === 'id') {
                return $id;
            }
            if ($key === 'username') {
                return $values['username'] ?? 'admin';
            }
            if ($key === 'active') {
                return $values['active'] ?? 1;
            }
            return $values[$key] ?? null;
        });

        $this->modx->expects($this->once())
            ->method('getObject')
            ->with('MODX\\Revolution\\modUser', $id)
            ->willReturn($user);
    }
}
