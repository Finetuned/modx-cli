<?php

namespace MODX\CLI\Tests\Command\Package\Upgrade;

use PHPUnit\Framework\TestCase;

class CLIIntegrationTest extends TestCase
{
    private $cliPath;

    protected function setUp(): void
    {
        $this->cliPath = __DIR__ . '/../../../../';
    }

    public function testIntegratedCommandsWorkInRealCLI()
    {
        // Test that the actual CLI can execute the integrated commands without argument conflicts
        $commands = [
            'package:list-upgrades',
            'package:list-remote',
            'package:download',
            'package:upgrade-all'
        ];

        foreach ($commands as $command) {
            $output = shell_exec("cd {$this->cliPath} && php bin/modx {$command} --help 2>&1");
            
            // Should not contain "argument already exists" error
            $this->assertStringNotContainsString('argument with name "command" already exists', $output, 
                "Command '{$command}' should not have argument conflicts");
            $this->assertStringNotContainsString('An argument with name "command" already exists', $output,
                "Command '{$command}' should not have argument conflicts");
            
            // Should contain the command description (proving it loaded successfully)
            $this->assertStringContainsString('Description:', $output,
                "Command '{$command}' should load successfully and show help");
            $this->assertStringContainsString('Usage:', $output,
                "Command '{$command}' should show usage information");
        }
    }

    public function testOldConflictingCommandsDoNotExist()
    {
        // Test that the old conflicting command names are no longer available
        $oldCommands = [
            'package:upgrade:list',
            'package:upgrade:list-remote',
            'package:upgrade:download',
            'package:upgrade:all'
        ];

        foreach ($oldCommands as $command) {
            $output = shell_exec("cd {$this->cliPath} && php bin/modx {$command} --help 2>&1");
            
            // Should indicate command not found (either specific error or namespace not found)
            $commandNotFound = strpos($output, 'Command "' . $command . '" is not defined') !== false ||
                              strpos($output, 'There are no commands defined in the "package:upgrade" namespace') !== false;
            
            $this->assertTrue($commandNotFound, 
                "Old conflicting command '{$command}' should not exist. Output: {$output}");
        }
    }

    public function testIntegratedCommandsAppearInPackageNamespace()
    {
        // Test that all package commands appear together in the list
        $output = shell_exec("cd {$this->cliPath} && php bin/modx list | grep 'package:' 2>&1");
        
        // Should contain all integrated commands
        $this->assertStringContainsString('package:list-upgrades', $output);
        $this->assertStringContainsString('package:list-remote', $output);
        $this->assertStringContainsString('package:download', $output);
        $this->assertStringContainsString('package:upgrade-all', $output);
        
        // Should also contain existing package commands (proving integration)
        $this->assertStringContainsString('package:list', $output);
        $this->assertStringContainsString('package:install', $output);
        $this->assertStringContainsString('package:upgradeable', $output);
    }

    public function testCommandNamingConsistency()
    {
        // Test that our new integrated commands follow the flat naming pattern
        $output = shell_exec("cd {$this->cliPath} && php bin/modx list | grep 'package:' 2>&1");
        
        if ($output) {
            $lines = explode("\n", trim($output));
        } else {
            $lines = [];
        }
        
        $integratedCommands = ['package:list-upgrades', 'package:list-remote', 'package:download', 'package:upgrade-all'];
        
        foreach ($lines as $line) {
            if (strpos($line, 'package:') !== false) {
                // Extract command name (first word)
                $parts = preg_split('/\s+/', trim($line));
                $commandName = $parts[0];
                
                // Should start with package:
                $this->assertStringStartsWith('package:', $commandName);
                
                // Our integrated commands should not have nested namespaces
                if (in_array($commandName, $integratedCommands)) {
                    $colonCount = substr_count($commandName, ':');
                    $this->assertEquals(1, $colonCount, 
                        "Integrated command '{$commandName}' should have flat naming (package:command-name)");
                }
            }
        }
        
        // Ensure we found at least some integrated commands
        $foundIntegratedCommands = 0;
        foreach ($lines as $line) {
            foreach ($integratedCommands as $integratedCommand) {
                if (strpos($line, $integratedCommand) !== false) {
                    $foundIntegratedCommands++;
                    break;
                }
            }
        }
        $this->assertGreaterThan(0, $foundIntegratedCommands, 'Should find at least one integrated command');
    }
}
