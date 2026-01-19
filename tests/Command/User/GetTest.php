<?php

namespace MODX\CLI\Tests\Command\User;

use MODX\CLI\Command\User\Get;
use MODX\CLI\Tests\Configuration\BaseTest;
use Symfony\Component\Console\Tester\CommandTester;

class GetTest extends BaseTest
{
    protected $command;
    protected $commandTester;
    protected $modx;

    protected function setUp(): void
    {
        // Create a mock MODX object
        $this->modx = $this->createMock('MODX\Revolution\modX');

        // Create the command
        $this->command = new Get();
        $this->command->modx = $this->modx;

        // Create a command tester
        $this->commandTester = new CommandTester($this->command);
    }

    public function testConfigureHasCorrectProcessorPath()
    {
        $processor = $this->getProtectedProperty($this->command, 'processor');
        $this->assertEquals('Security\\User\\Get', $processor);
    }

    public function testConfigureHasCorrectName()
    {
        $this->assertEquals('user:get', $this->command->getName());
    }

    public function testConfigureHasCorrectDescription()
    {
        $this->assertEquals('Get detailed information about a MODX user', $this->command->getDescription());
    }

    public function testExecuteWithSuccessfulResponseById()
    {
        // Mock the runProcessor method to return a successful response
        $processorResponse = $this->getMockBuilder('MODX\Revolution\Processors\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $processorResponse->method('getResponse')
            ->willReturn(json_encode([
                'success' => true,
                'object' => [
                    'id' => 1,
                    'username' => 'admin',
                    'email' => 'admin@example.com',
                    'fullname' => 'Administrator',
                    'active' => true,
                    'blocked' => false,
                    'sudo' => true,
                    'createdon' => 1640995200,
                    'lastlogin' => 1704067200
                ]
            ]));
        $processorResponse->method('isError')->willReturn(false);

        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->with(
                'Security\\User\\Get',
                $this->callback(function ($properties) {
                    return isset($properties['id']) && $properties['id'] === 1;
                }),
                $this->anything()
            )
            ->willReturn($processorResponse);

        // Execute the command
        $this->commandTester->execute([
            'identifier' => '1'
        ]);

        // Verify the output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('ID:          1', $output);
        $this->assertStringContainsString('Username:    admin', $output);
        $this->assertStringContainsString('Email:       admin@example.com', $output);
        $this->assertStringContainsString('Active:      Yes', $output);
        $this->assertStringContainsString('Sudo:        Yes', $output);
    }

    public function testExecuteWithSuccessfulResponseByUsername()
    {
        // Mock user lookup by username
        $user = $this->createMock('MODX\Revolution\modUser');
        $user->method('get')->with('id')->willReturn(1);

        $this->modx->expects($this->once())
            ->method('getObject')
            ->with(
                'MODX\\Revolution\\modUser',
                ['username' => 'admin']
            )
            ->willReturn($user);

        // Mock the runProcessor method
        $processorResponse = $this->getMockBuilder('MODX\Revolution\Processors\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $processorResponse->method('getResponse')
            ->willReturn(json_encode([
                'success' => true,
                'object' => [
                    'id' => 1,
                    'username' => 'admin',
                    'email' => 'admin@example.com',
                    'active' => true,
                    'blocked' => false
                ]
            ]));
        $processorResponse->method('isError')->willReturn(false);

        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->willReturn($processorResponse);

        // Execute the command
        $this->commandTester->execute([
            'identifier' => 'admin'
        ]);

        // Verify the output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Username:    admin', $output);
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

        // Execute the command
        $this->commandTester->execute([
            'identifier' => 'nonexistent'
        ]);

        // Verify the output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('User not found: nonexistent', $output);
    }

    public function testExecuteWithFailedResponse()
    {
        // Mock the runProcessor method to return a failed response
        $processorResponse = $this->getMockBuilder('MODX\Revolution\Processors\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $processorResponse->method('getResponse')
            ->willReturn(json_encode([
                'success' => false,
                'message' => 'User not found'
            ]));
        $processorResponse->method('isError')->willReturn(true);

        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->willReturn($processorResponse);

        // Execute the command
        $this->commandTester->execute([
            'identifier' => '999'
        ]);

        // Verify the output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Failed to get user', $output);
        $this->assertStringContainsString('User not found', $output);
    }

    public function testExecuteWithJsonOption()
    {
        $processorResponse = $this->getMockBuilder('MODX\Revolution\Processors\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $processorResponse->method('getResponse')
            ->willReturn(json_encode([
                'success' => true,
                'object' => [
                    'id' => 1,
                    'username' => 'admin',
                    'email' => 'admin@example.com'
                ]
            ]));
        $processorResponse->method('isError')->willReturn(false);

        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->willReturn($processorResponse);

        $this->commandTester->execute([
            'identifier' => '1',
            '--json' => true
        ]);

        $output = $this->commandTester->getDisplay();
        $data = json_decode($output, true);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('success', $data);
        $this->assertTrue($data['success']);
    }
}
