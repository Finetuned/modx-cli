<?php namespace MODX\CLI\Tests\Command\User;

use MODX\CLI\Command\User\ResetPassword;
use MODX\CLI\Tests\Configuration\BaseTest;
use MODX\CLI\Application;
use Symfony\Component\Console\Tester\CommandTester;

class ResetPasswordTest extends BaseTest
{
    protected $command;
    protected $commandTester;
    protected $modx;

    protected function setUp(): void
    {
        // Create a mock MODX object
        $this->modx = $this->createMock('MODX\Revolution\modX');
        
        // Create the command
        $this->command = new ResetPassword();
        $this->command->modx = $this->modx;
        
        // Create a command tester without using the Application class to avoid conflicts
        $this->commandTester = new CommandTester($this->command);
    }

    public function testConfigureHasCorrectProcessorPath()
    {
        $processor = $this->getProtectedProperty($this->command, 'processor');
        $this->assertEquals('Security\User\Update', $processor);
    }

    public function testConfigureHasCorrectName()
    {
        $this->assertEquals('user:resetpassword', $this->command->getName());
    }

    public function testConfigureHasCorrectDescription()
    {
        $this->assertEquals('Reset a user\'s password in MODX', $this->command->getDescription());
    }

    public function testExecuteWithSuccessfulResponse()
    {
        // Mock user object
        $user = $this->getMockBuilder('stdClass')
            ->addMethods(['get', 'getOne'])
            ->getMock();
        $user->method('get')->willReturnMap([
            ['username', 'testuser']
        ]);
        $user->method('getOne')->with('Profile')->willReturn(null);
        
        // Mock getObject to return user
        $this->modx->expects($this->once())
            ->method('getObject')
            ->with(\MODX\Revolution\modUser::class, '123', $this->anything())
            ->willReturn($user);
        
        // Mock the runProcessor method to return a successful response
        $processorResponse = $this->getMockBuilder('MODX\Revolution\Processors\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $processorResponse->method('getResponse')
            ->willReturn(json_encode([
                'success' => true,
                'object' => ['id' => 123]
            ]));
        $processorResponse->method('isError')->willReturn(false);
        
        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->with(
                'Security\User\Update',
                $this->callback(function($properties) {
                    return isset($properties['id']) && $properties['id'] === '123' &&
                           isset($properties['password']) && !empty($properties['password']) &&
                           isset($properties['passwordnotifymethod']) && $properties['passwordnotifymethod'] === 'none';
                }),
                $this->anything()
            )
            ->willReturn($processorResponse);
        
        // Execute the command with --password option
        $this->commandTester->execute([
            'id' => '123',
            '--password' => 'newpassword123'
        ]);
        
        // Verify the output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Password reset successfully', $output);
        $this->assertStringContainsString('newpassword123', $output);
    }

    public function testExecuteWithNonExistentUser()
    {
        // Mock getObject to return null (user doesn't exist)
        $this->modx->expects($this->once())
            ->method('getObject')
            ->with(\MODX\Revolution\modUser::class, '999', $this->anything())
            ->willReturn(null);
        
        // runProcessor should not be called since the user doesn't exist
        $this->modx->expects($this->never())
            ->method('runProcessor');
        
        // Execute the command
        $this->commandTester->execute([
            'id' => '999',
            '--password' => 'newpassword123'
        ]);
        
        // Verify the output shows error message
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('User with ID 999 not found', $output);
    }

    public function testExecuteWithFailedResponse()
    {
        // Mock user object
        $user = $this->getMockBuilder('stdClass')
            ->addMethods(['get', 'getOne'])
            ->getMock();
        $user->method('get')->willReturnMap([
            ['username', 'testuser']
        ]);
        $user->method('getOne')->with('Profile')->willReturn(null);
        
        // Mock getObject to return user
        $this->modx->expects($this->once())
            ->method('getObject')
            ->with(\MODX\Revolution\modUser::class, '123', $this->anything())
            ->willReturn($user);
        
        // Mock the runProcessor method to return a failed response
        $processorResponse = $this->getMockBuilder('MODX\Revolution\Processors\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $processorResponse->method('getResponse')
            ->willReturn(json_encode([
                'success' => false,
                'message' => 'Error resetting password'
            ]));
        $processorResponse->method('isError')->willReturn(true);
        
        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->willReturn($processorResponse);
        
        // Execute the command
        $this->commandTester->execute([
            'id' => '123',
            '--password' => 'newpassword123'
        ]);
        
        // Verify the output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Failed to reset password', $output);
        $this->assertStringContainsString('Error resetting password', $output);
    }

    public function testExecuteWithGenerateOption()
    {
        // Mock user object
        $user = $this->getMockBuilder('stdClass')
            ->addMethods(['get', 'getOne'])
            ->getMock();
        $user->method('get')->willReturnMap([
            ['username', 'testuser']
        ]);
        $user->method('getOne')->with('Profile')->willReturn(null);
        
        // Mock getObject to return user
        $this->modx->expects($this->once())
            ->method('getObject')
            ->with(\MODX\Revolution\modUser::class, '123', $this->anything())
            ->willReturn($user);
        
        // Mock the runProcessor method to return a successful response
        $processorResponse = $this->getMockBuilder('MODX\Revolution\Processors\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $processorResponse->method('getResponse')
            ->willReturn(json_encode([
                'success' => true,
                'object' => ['id' => 123]
            ]));
        $processorResponse->method('isError')->willReturn(false);
        
        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->with(
                'Security\User\Update',
                $this->callback(function($properties) {
                    // Verify a password was generated (should be 12 characters)
                    return isset($properties['password']) && 
                           strlen($properties['password']) === 12 &&
                           isset($properties['passwordnotifymethod']) && 
                           $properties['passwordnotifymethod'] === 'none';
                }),
                $this->anything()
            )
            ->willReturn($processorResponse);
        
        // Execute the command with --generate option
        $this->commandTester->execute([
            'id' => '123',
            '--generate' => true
        ]);
        
        // Verify the output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Password reset successfully', $output);
        $this->assertStringContainsString('New password:', $output);
    }
}
