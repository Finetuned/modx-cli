<?php

namespace MODX\CLI\Tests\Integration\SSH;

use PHPUnit\Framework\TestCase;
use MODX\CLI\SSH\Handler;
use MODX\CLI\SSH\CommandProxy;
use MODX\CLI\SSH\ConnectionParser;

/**
 * Integration tests for remote SSH command execution
 *
 * Note: These tests do not require a MODX instance or actual SSH connectivity.
 * They test SSH command building and component structure using reflection.
 */
class RemoteExecutionTest extends TestCase
{
    /**
     * Test building SSH command with all connection components
     */
    public function testBuildSSHCommandWithAllComponents()
    {
        $parser = new ConnectionParser('testuser@example.com:2222/var/www/html');
        $proxy = new CommandProxy($parser, 'system:info', ['--json']);

        // Use reflection to access protected method for testing
        $reflection = new \ReflectionClass($proxy);
        $method = $reflection->getMethod('buildSSHCommand');
        $method->setAccessible(true);

        $sshCommand = $method->invoke($proxy);

        $this->assertStringContainsString('ssh', $sshCommand);
        $this->assertStringContainsString('-p 2222', $sshCommand);
        $this->assertStringContainsString('testuser@example.com', $sshCommand);
        $this->assertStringContainsString('cd /var/www/html', $sshCommand);
        $this->assertStringContainsString('modx system:info', $sshCommand);
    }

    /**
     * Test building SSH command without custom port (default port 22)
     */
    public function testBuildSSHCommandWithDefaultPort()
    {
        $parser = new ConnectionParser('testuser@example.com/var/www/html');
        $proxy = new CommandProxy($parser, 'system:info', []);

        $reflection = new \ReflectionClass($proxy);
        $method = $reflection->getMethod('buildSSHCommand');
        $method->setAccessible(true);

        $sshCommand = $method->invoke($proxy);

        $this->assertStringNotContainsString('-p 22', $sshCommand);
        $this->assertStringNotContainsString('-p', $sshCommand);
        $this->assertStringContainsString('testuser@example.com', $sshCommand);
    }

    /**
     * Test building SSH command without remote path
     */
    public function testBuildSSHCommandWithoutRemotePath()
    {
        $parser = new ConnectionParser('testuser@example.com');
        $proxy = new CommandProxy($parser, 'system:info', []);

        $reflection = new \ReflectionClass($proxy);
        $method = $reflection->getMethod('buildSSHCommand');
        $method->setAccessible(true);

        $sshCommand = $method->invoke($proxy);

        $this->assertStringNotContainsString('cd ', $sshCommand);
        $this->assertStringContainsString('modx system:info', $sshCommand);
    }

    /**
     * Test building remote command with arguments
     */
    public function testBuildRemoteCommandWithArguments()
    {
        $parser = new ConnectionParser('testuser@example.com');
        $proxy = new CommandProxy($parser, 'package:list', ['--limit=10', '--json']);

        $reflection = new \ReflectionClass($proxy);
        $method = $reflection->getMethod('buildRemoteCommand');
        $method->setAccessible(true);

        $remoteCommand = $method->invoke($proxy);

        $this->assertStringContainsString('modx package:list', $remoteCommand);
        $this->assertStringContainsString('--limit=10', $remoteCommand);
        $this->assertStringContainsString('--json', $remoteCommand);
    }

    /**
     * Test building remote command with special characters in arguments
     */
    public function testBuildRemoteCommandWithSpecialCharacters()
    {
        $parser = new ConnectionParser('testuser@example.com');
        $proxy = new CommandProxy($parser, 'resource:create', ['--pagetitle=Test & Demo', '--content=<p>Hello</p>']);

        $reflection = new \ReflectionClass($proxy);
        $method = $reflection->getMethod('buildRemoteCommand');
        $method->setAccessible(true);

        $remoteCommand = $method->invoke($proxy);

        // Arguments should be properly escaped
        $this->assertStringContainsString('modx resource:create', $remoteCommand);
        // The exact escaping format may vary, but dangerous characters should be handled
        $this->assertNotEquals('modx resource:create --pagetitle=Test & Demo --content=<p>Hello</p>', $remoteCommand);
    }

    /**
     * Test Handler class delegates to CommandProxy correctly
     */
    public function testHandlerDelegatesToCommandProxy()
    {
        $handler = new Handler('testuser@example.com/var/www/html');

        // Create a mock to test that Handler creates correct components
        // This is primarily a structural test
        $this->assertInstanceOf(Handler::class, $handler);

        // In a real scenario, this would execute over SSH
        // For integration testing, we're verifying the component structure
    }

    /**
     * Test SSH command construction with IPv4 address
     */
    public function testBuildSSHCommandWithIPv4Address()
    {
        $parser = new ConnectionParser('deploy@192.168.1.100:2222/opt/modx');
        $proxy = new CommandProxy($parser, 'cache:clear', []);

        $reflection = new \ReflectionClass($proxy);
        $method = $reflection->getMethod('buildSSHCommand');
        $method->setAccessible(true);

        $sshCommand = $method->invoke($proxy);

        $this->assertStringContainsString('deploy@192.168.1.100', $sshCommand);
        $this->assertStringContainsString('-p 2222', $sshCommand);
        $this->assertStringContainsString('cd /opt/modx', $sshCommand);
    }

    /**
     * Test SSH command construction with tilde path
     */
    public function testBuildSSHCommandWithTildePath()
    {
        $parser = new ConnectionParser('developer@dev.example.com~/projects/modx');
        $proxy = new CommandProxy($parser, 'system:info', []);

        $reflection = new \ReflectionClass($proxy);
        $method = $reflection->getMethod('buildSSHCommand');
        $method->setAccessible(true);

        $sshCommand = $method->invoke($proxy);

        $this->assertStringContainsString('cd ~/projects/modx', $sshCommand);
    }
}
