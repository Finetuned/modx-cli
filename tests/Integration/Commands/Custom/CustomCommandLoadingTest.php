<?php

namespace MODX\CLI\Tests\Integration\Commands\Custom;

use MODX\CLI\Tests\Integration\BaseIntegrationTest;

/**
 * Integration tests for custom command loading and registration
 *
 * Tests the custom command system that loads commands from YAML configuration
 * and registers them via the bootstrap process.
 */
class CustomCommandLoadingTest extends BaseIntegrationTest
{
    /**
     * Test that custom commands configuration file exists and is valid YAML
     */
    public function testCustomCommandConfigurationExists()
    {
        $configFile = __DIR__ . '/../../../../custom-commands/config.yml';

        $this->assertFileExists($configFile, 'Custom commands config.yml should exist');

        // Verify it's valid YAML by parsing it
        if (class_exists('Symfony\Component\Yaml\Yaml')) {
            $config = \Symfony\Component\Yaml\Yaml::parseFile($configFile);

            $this->assertIsArray($config);
            $this->assertArrayHasKey('custom_commands', $config);
            $this->assertIsArray($config['custom_commands']);
        }
    }

    /**
     * Test that custom commands are properly registered with the CLI
     */
    public function testCustomCommandsAreRegistered()
    {
        // Execute list command to get all available commands
        $process = $this->executeCommandSuccessfully(['list']);
        $output = $process->getOutput();

        // Verify package upgrade custom commands appear in the list
        $this->assertStringContainsString('package:list-upgrades', $output);
        $this->assertStringContainsString('package:list-remote', $output);
        $this->assertStringContainsString('package:download', $output);
        $this->assertStringContainsString('package:upgrade-all', $output);
    }

    /**
     * Test custom command execution with MODX instance available
     */
    public function testCustomCommandExecutionBasic()
    {
        // Execute package:list-upgrades with JSON format
        $process = $this->executeCommand(['package:list-upgrades', '--format=json']);

        // Command should execute (may return empty results if no upgrades)
        $this->assertEquals(0, $process->getExitCode(), 'Command should execute successfully');

        $output = $process->getOutput();

        // Output should be valid JSON or empty array
        $decoded = json_decode($output, true);
        $this->assertTrue(
            is_array($decoded) || $output === '' || strpos($output, 'No downloaded package upgrades found') !== false,
            'Output should be valid JSON array or empty result message'
        );
    }

    /**
     * Test custom command with options (filter and format)
     */
    public function testCustomCommandWithOptions()
    {
        // Execute with filter and JSON format
        $process = $this->executeCommand(['package:list-upgrades', '--filter=test', '--format=json']);

        $this->assertEquals(0, $process->getExitCode(), 'Command should execute successfully');

        $output = $process->getOutput();

        // Verify JSON output
        $decoded = json_decode($output, true);
        $this->assertTrue(
            is_array($decoded) || $output === '' || strpos($output, 'No downloaded package upgrades found') !== false,
            'Filtered output should be valid JSON or empty'
        );
    }

    /**
     * Test custom command with table format output
     */
    public function testCustomCommandTableFormat()
    {
        // Execute with default table format
        $process = $this->executeCommand(['package:list-upgrades', '--format=table']);

        $this->assertEquals(0, $process->getExitCode(), 'Command should execute successfully');

        $output = $process->getOutput();

        // Output should contain table headers or "No downloaded package upgrades found"
        $this->assertTrue(
            strpos($output, 'Package') !== false ||
            strpos($output, 'No downloaded package upgrades found') !== false,
            'Table format should show headers or empty message'
        );
    }

    /**
     * Test custom command help displays properly
     */
    public function testCustomCommandHelp()
    {
        // Execute help for custom command
        $process = $this->executeCommand(['package:list-upgrades', '--help']);

        $this->assertEquals(0, $process->getExitCode(), 'Help command should execute successfully');

        $output = $process->getOutput();

        // Verify help output contains command description
        $this->assertStringContainsString('package:list-upgrades', $output);
        $this->assertTrue(
            strpos($output, 'upgrade') !== false ||
            strpos($output, 'List') !== false,
            'Help should contain command description'
        );
    }
}
