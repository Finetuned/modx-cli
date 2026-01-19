<?php

namespace MODX\CLI\Tests\Command\User;

use MODX\CLI\Command\User\Create;
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
        $this->assertEquals('Security\\User\\Create', $processor);
    }

    public function testConfigureHasCorrectName()
    {
        $this->assertEquals('user:create', $this->command->getName());
    }

    public function testConfigureHasCorrectDescription()
    {
        $this->assertEquals('Create a MODX user', $this->command->getDescription());
    }

    public function testExecuteWithSuccessfulResponse()
    {
        // Mock the runProcessor method to return a successful response
        $processorResponse = $this->getMockBuilder('MODX\Revolution\Processors\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $processorResponse->method('getResponse')
            ->willReturn(json_encode([
                'success' => true,
                'object' => [
                    'id' => 5,
                    'username' => 'testuser'
                ]
            ]));
        $processorResponse->method('isError')->willReturn(false);
        
        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->with(
                'Security\\User\\Create',
                $this->callback(function($properties) {
                    return isset($properties['username']) 
                        && $properties['username'] === 'testuser'
                        && isset($properties['email'])
                        && $properties['email'] === 'test@example.com';
                }),
                $this->anything()
            )
            ->willReturn($processorResponse);
        
        // Execute the command
        $this->commandTester->execute([
            'username' => 'testuser',
            '--email' => 'test@example.com',
            '--password' => 'testpass123'
        ]);
        
        // Verify the output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('User created successfully', $output);
        $this->assertStringContainsString('User ID: 5', $output);
    }

    public function testExecuteWithoutEmail()
    {
        // Execute the command without email
        $this->commandTester->execute([
            'username' => 'testuser'
        ]);
        
        // Verify the output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Email is required', $output);
    }

    public function testExecuteWithInvalidEmail()
    {
        // Execute the command with invalid email
        $this->commandTester->execute([
            'username' => 'testuser',
            '--email' => 'invalid-email'
        ]);
        
        // Verify the output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Invalid email format', $output);
    }

    public function testExecuteGeneratesPasswordWhenNotProvided()
    {
        $processorResponse = $this->getMockBuilder('MODX\Revolution\Processors\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $processorResponse->method('getResponse')
            ->willReturn(json_encode(['success' => true, 'object' => ['id' => 5, 'username' => 'testuser']]));
        $processorResponse->method('isError')->willReturn(false);
        
        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->with(
                'Security\\User\\Create',
                $this->callback(function($properties) {
                    return isset($properties['password']) && !empty($properties['password']);
                }),
                $this->anything()
            )
            ->willReturn($processorResponse);
        
        // Execute the command without password
        $this->commandTester->execute([
            'username' => 'testuser',
            '--email' => 'test@example.com'
        ]);
        
        // Verify the output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Generated password:', $output);
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
                'message' => 'Username already exists'
            ]));
        $processorResponse->method('isError')->willReturn(true);
        
        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->willReturn($processorResponse);
        
        // Execute the command
        $this->commandTester->execute([
            'username' => 'admin',
            '--email' => 'admin@example.com',
            '--password' => 'test123'
        ]);
        
        // Verify the output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Failed to create user', $output);
        $this->assertStringContainsString('Username already exists', $output);
    }

    public function testBeforeRunPopulatesAllProperties()
    {
        $processorResponse = $this->getMockBuilder('MODX\Revolution\Processors\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $processorResponse->method('getResponse')
            ->willReturn(json_encode(['success' => true, 'object' => ['id' => 5]]));
        $processorResponse->method('isError')->willReturn(false);
        
        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->with(
                'Security\\User\\Create',
                $this->callback(function($properties) {
                    return isset($properties['username']) 
                        && $properties['username'] === 'testuser'
                        && isset($properties['email'])
                        && $properties['email'] === 'test@example.com'
                        && isset($properties['fullname'])
                        && $properties['fullname'] === 'Test User'
                        && isset($properties['active'])
                        && $properties['active'] === 1
                        && isset($properties['blocked'])
                        && $properties['blocked'] === 0;
                }),
                $this->anything()
            )
            ->willReturn($processorResponse);
        
        $this->commandTester->execute([
            'username' => 'testuser',
            '--email' => 'test@example.com',
            '--password' => 'testpass',
            '--fullname' => 'Test User',
            '--active' => '1',
            '--blocked' => '0'
        ]);
        
        $this->assertEquals(0, $this->commandTester->getStatusCode());
    }

    public function testExecuteWithJsonOption()
    {
        $processorResponse = $this->getMockBuilder('MODX\Revolution\Processors\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $processorResponse->method('getResponse')
            ->willReturn(json_encode([
                'success' => true,
                'object' => ['id' => 5, 'username' => 'testuser']
            ]));
        $processorResponse->method('isError')->willReturn(false);
        
        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->willReturn($processorResponse);
        
        $this->commandTester->execute([
            'username' => 'testuser',
            '--email' => 'test@example.com',
            '--password' => 'test123',
            '--json' => true
        ]);
        
        $output = $this->commandTester->getDisplay();
        $data = json_decode($output, true);
        
        $this->assertIsArray($data);
        $this->assertArrayHasKey('success', $data);
        $this->assertTrue($data['success']);
    }
}
