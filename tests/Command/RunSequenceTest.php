<?php

namespace MODX\CLI\Tests\Command;

use MODX\CLI\Command\RunSequence;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use MODX\CLI\API\MODX_CLI;

class RunSequenceTest extends TestCase
{
    /**
     * @var RunSequence
     */
    private $command;
    
    /**
     * @var BufferedOutput
     */
    private $output;
    
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $modxCliMock;
    
    /**
     * @var \ReflectionProperty
     */
    private $instanceProperty;
    
    /**
     * @var mixed
     */
    private $originalInstance;
    
    protected function setUp(): void
    {
        $this->command = new RunSequence();
        $this->output = new BufferedOutput();
        
        // Create a mock for MODX_CLI
        $this->modxCliMock = $this->getMockBuilder(MODX_CLI::class)
            ->disableOriginalConstructor()
            ->setMethods(['run_command'])
            ->getMock();
        
        // Configure the mock to return a success result by default
        $this->modxCliMock->method('run_command')
            ->willReturnCallback(function ($command, $args = [], $options = []) {
                // Return a success result by default
                $result = (object) [
                    'return_code' => 0,
                    'stdout' => "Success: $command executed",
                    'stderr' => ''
                ];
                
                // Simulate errors for specific commands
                if (strpos($command, 'error') !== false) {
                    $result->return_code = 1;
                    $result->stdout = '';
                    $result->stderr = "Error: $command failed";
                }
                
                return $result;
            });
        
        // Use reflection to replace the static instance
        $reflection = new \ReflectionClass(MODX_CLI::class);
        $this->instanceProperty = $reflection->getProperty('instance');
        $this->instanceProperty->setAccessible(true);
        
        // Save the original instance
        $this->originalInstance = $this->instanceProperty->getValue(null);
        
        // Set our mock as the instance
        $this->instanceProperty->setValue(null, $this->modxCliMock);
    }
    
    protected function tearDown(): void
    {
        // Restore the original instance
        if (isset($this->originalInstance)) {
            $this->instanceProperty->setValue(null, $this->originalInstance);
        }
    }
    
    public function testRunSequenceWithNoCommandSets()
    {
        $input = new ArrayInput([
            'command' => 'run-sequence'
        ]);
        
        $exitCode = $this->command->run($input, $this->output);
        
        $this->assertEquals(1, $exitCode);
        $this->assertStringContainsString('No command sets provided', $this->output->fetch());
    }
    
    public function testRunSequenceWithSingleCommandSet()
    {
        $commandSets = json_encode([
            'set1' => [
                'commands' => [
                    'resource:list',
                    'chunk:list'
                ],
                'is_asynchronous' => false
            ]
        ]);
        
        $input = new ArrayInput([
            'command' => 'run-sequence',
            '--command_sets' => $commandSets
        ]);
        
        $exitCode = $this->command->run($input, $this->output);
        
        $this->assertEquals(0, $exitCode);
        $output = $this->output->fetch();
        $this->assertStringContainsString('Executing command set: set1', $output);
        $this->assertStringContainsString('Running command: modx resource:list', $output);
        $this->assertStringContainsString('Running command: modx chunk:list', $output);
        $this->assertStringContainsString('All command sets have been executed', $output);
    }
    
    public function testRunSequenceWithMultipleCommandSets()
    {
        $commandSets = json_encode([
            'set1' => [
                'commands' => [
                    'resource:list',
                    'chunk:list'
                ],
                'is_asynchronous' => false
            ],
            'set2' => [
                'commands' => [
                    'template:list',
                    'tv:list'
                ],
                'is_asynchronous' => false
            ]
        ]);
        
        $input = new ArrayInput([
            'command' => 'run-sequence',
            '--command_sets' => $commandSets
        ]);
        
        $exitCode = $this->command->run($input, $this->output);
        
        $this->assertEquals(0, $exitCode);
        $output = $this->output->fetch();
        $this->assertStringContainsString('Executing command set: set1', $output);
        $this->assertStringContainsString('Running command: modx resource:list', $output);
        $this->assertStringContainsString('Running command: modx chunk:list', $output);
        $this->assertStringContainsString('Executing command set: set2', $output);
        $this->assertStringContainsString('Running command: modx template:list', $output);
        $this->assertStringContainsString('Running command: modx tv:list', $output);
        $this->assertStringContainsString('All command sets have been executed', $output);
    }
    
    public function testRunSequenceWithErrorAndContinueAfterError()
    {
        $commandSets = json_encode([
            'set1' => [
                'commands' => [
                    'resource:list',
                    'error:command',
                    'chunk:list'
                ],
                'continue_after_error' => true,
                'is_asynchronous' => false
            ]
        ]);
        
        $input = new ArrayInput([
            'command' => 'run-sequence',
            '--command_sets' => $commandSets
        ]);
        
        $exitCode = $this->command->run($input, $this->output);
        
        $this->assertEquals(0, $exitCode);
        $output = $this->output->fetch();
        $this->assertStringContainsString('Running command: modx resource:list', $output);
        $this->assertStringContainsString('Running command: modx error:command', $output);
        $this->assertStringContainsString('Command failed: modx error:command', $output);
        $this->assertStringContainsString('Running command: modx chunk:list', $output);
        $this->assertStringContainsString('All command sets have been executed', $output);
    }
    
    public function testRunSequenceWithErrorAndStopAfterError()
    {
        $commandSets = json_encode([
            'set1' => [
                'commands' => [
                    'resource:list',
                    'error:command',
                    'chunk:list'
                ],
                'continue_after_error' => false,
                'is_asynchronous' => false
            ]
        ]);
        
        $input = new ArrayInput([
            'command' => 'run-sequence',
            '--command_sets' => $commandSets
        ]);
        
        $exitCode = $this->command->run($input, $this->output);
        
        $this->assertEquals(1, $exitCode);
        $output = $this->output->fetch();
        $this->assertStringContainsString('Running command: modx resource:list', $output);
        $this->assertStringContainsString('Running command: modx error:command', $output);
        $this->assertStringContainsString('Command failed: modx error:command', $output);
        $this->assertStringContainsString('Execution stopped due to error', $output);
        $this->assertStringNotContainsString('Running command: modx chunk:list', $output);
    }
    
    public function testRunSequenceWithCollatesErrors()
    {
        $commandSets = json_encode([
            'set1' => [
                'commands' => [
                    'resource:list',
                    'error:command1',
                    'error:command2'
                ],
                'continue_after_error' => true,
                'collates_errors' => true,
                'returns_results_as_json' => true,
                'is_asynchronous' => false
            ]
        ]);
        
        $input = new ArrayInput([
            'command' => 'run-sequence',
            '--command_sets' => $commandSets
        ]);
        
        $exitCode = $this->command->run($input, $this->output);
        
        $this->assertEquals(0, $exitCode);
        $output = $this->output->fetch();
        
        // Check that the JSON output contains the errors
        $this->assertStringContainsString('"errors":', $output);
        $this->assertStringContainsString('"Error: error:command1 failed"', $output);
        $this->assertStringContainsString('"Error: error:command2 failed"', $output);
    }
    
    public function testRunSequenceWithCollatesDataResponses()
    {
        $commandSets = json_encode([
            'set1' => [
                'commands' => [
                    'resource:list',
                    'chunk:list'
                ],
                'collates_data_responses' => true,
                'returns_results_as_json' => true,
                'is_asynchronous' => false
            ]
        ]);
        
        $input = new ArrayInput([
            'command' => 'run-sequence',
            '--command_sets' => $commandSets
        ]);
        
        $exitCode = $this->command->run($input, $this->output);
        
        $this->assertEquals(0, $exitCode);
        $output = $this->output->fetch();
        
        // Check that the JSON output contains the data responses
        $this->assertStringContainsString('"data_responses":', $output);
        $this->assertStringContainsString('"Success: resource:list executed"', $output);
        $this->assertStringContainsString('"Success: chunk:list executed"', $output);
    }
    
    public function testResourceCRUDOperations()
    {
        $commandSets = json_encode([
            'resource_operations' => [
                'commands' => [
                    'resource:create --pagetitle="Test Resource" --content="Test Content" --published=1',
                    'resource:get --format=json',
                    'resource:update --pagetitle="Updated Resource" --content="Updated Content"',
                    'resource:get --format=json',
                    'resource:remove --force=1'
                ],
                'is_asynchronous' => false,
                'continue_after_error' => false
            ]
        ]);
        
        $input = new ArrayInput([
            'command' => 'run-sequence',
            '--command_sets' => $commandSets
        ]);
        
        $exitCode = $this->command->run($input, $this->output);
        
        $this->assertEquals(0, $exitCode);
        $output = $this->output->fetch();
        $this->assertStringContainsString('Executing command set: resource_operations', $output);
        $this->assertStringContainsString('Running command: modx resource:create', $output);
        $this->assertStringContainsString('Running command: modx resource:get', $output);
        $this->assertStringContainsString('Running command: modx resource:update', $output);
        $this->assertStringContainsString('Running command: modx resource:remove', $output);
        $this->assertStringContainsString('All command sets have been executed', $output);
    }
    
    public function testChunkCRUDOperations()
    {
        $commandSets = json_encode([
            'chunk_operations' => [
                'commands' => [
                    'chunk:create --name="TestChunk" --description="Test Description" --snippet="<p>Test Content</p>"',
                    'chunk:get --name="TestChunk" --format=json',
                    'chunk:update --name="TestChunk" --description="Updated Description" --snippet="<p>Updated Content</p>"',
                    'chunk:get --name="TestChunk" --format=json',
                    'chunk:remove --name="TestChunk" --force=1'
                ],
                'is_asynchronous' => false,
                'continue_after_error' => false
            ]
        ]);
        
        $input = new ArrayInput([
            'command' => 'run-sequence',
            '--command_sets' => $commandSets
        ]);
        
        $exitCode = $this->command->run($input, $this->output);
        
        $this->assertEquals(0, $exitCode);
        $output = $this->output->fetch();
        $this->assertStringContainsString('Executing command set: chunk_operations', $output);
        $this->assertStringContainsString('Running command: modx chunk:create', $output);
        $this->assertStringContainsString('Running command: modx chunk:get', $output);
        $this->assertStringContainsString('Running command: modx chunk:update', $output);
        $this->assertStringContainsString('Running command: modx chunk:remove', $output);
        $this->assertStringContainsString('All command sets have been executed', $output);
    }
    
    public function testSnippetCRUDOperations()
    {
        $commandSets = json_encode([
            'snippet_operations' => [
                'commands' => [
                    'snippet:create --name="TestSnippet" --description="Test Description" --snippet="return \"Test\";"',
                    'snippet:get --name="TestSnippet" --format=json',
                    'snippet:update --name="TestSnippet" --description="Updated Description" --snippet="return \"Updated\";"',
                    'snippet:get --name="TestSnippet" --format=json',
                    'snippet:remove --name="TestSnippet" --force=1'
                ],
                'is_asynchronous' => false,
                'continue_after_error' => false
            ]
        ]);
        
        $input = new ArrayInput([
            'command' => 'run-sequence',
            '--command_sets' => $commandSets
        ]);
        
        $exitCode = $this->command->run($input, $this->output);
        
        $this->assertEquals(0, $exitCode);
        $output = $this->output->fetch();
        $this->assertStringContainsString('Executing command set: snippet_operations', $output);
        $this->assertStringContainsString('Running command: modx snippet:create', $output);
        $this->assertStringContainsString('Running command: modx snippet:get', $output);
        $this->assertStringContainsString('Running command: modx snippet:update', $output);
        $this->assertStringContainsString('Running command: modx snippet:remove', $output);
        $this->assertStringContainsString('All command sets have been executed', $output);
    }
    
    public function testTVCRUDOperations()
    {
        $commandSets = json_encode([
            'tv_operations' => [
                'commands' => [
                    'tv:create --name="TestTV" --type="text" --caption="Test TV"',
                    'tv:get --name="TestTV" --format=json',
                    'tv:update --name="TestTV" --caption="Updated TV"',
                    'tv:get --name="TestTV" --format=json',
                    'tv:remove --name="TestTV" --force=1'
                ],
                'is_asynchronous' => false,
                'continue_after_error' => false
            ]
        ]);
        
        $input = new ArrayInput([
            'command' => 'run-sequence',
            '--command_sets' => $commandSets
        ]);
        
        $exitCode = $this->command->run($input, $this->output);
        
        $this->assertEquals(0, $exitCode);
        $output = $this->output->fetch();
        $this->assertStringContainsString('Executing command set: tv_operations', $output);
        $this->assertStringContainsString('Running command: modx tv:create', $output);
        $this->assertStringContainsString('Running command: modx tv:get', $output);
        $this->assertStringContainsString('Running command: modx tv:update', $output);
        $this->assertStringContainsString('Running command: modx tv:remove', $output);
        $this->assertStringContainsString('All command sets have been executed', $output);
    }
}
