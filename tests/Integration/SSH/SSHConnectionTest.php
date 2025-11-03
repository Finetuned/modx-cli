<?php

namespace MODX\CLI\Tests\Integration\SSH;

use PHPUnit\Framework\TestCase;
use MODX\CLI\SSH\ConnectionParser;

/**
 * Integration tests for SSH connection string parsing and validation
 * 
 * Note: These tests do not require a MODX instance as they test
 * SSH connection parsing logic independently.
 */
class SSHConnectionTest extends TestCase
{
    /**
     * Test parsing a full connection string with all components
     */
    public function testParseFullConnectionString()
    {
        $parser = new ConnectionParser('testuser@example.com:2222/var/www/html');
        
        $this->assertEquals('testuser', $parser->getUser());
        $this->assertEquals('example.com', $parser->getHost());
        $this->assertEquals(2222, $parser->getPort());
        $this->assertEquals('/var/www/html', $parser->getPath());
    }

    /**
     * Test parsing connection string with default port
     */
    public function testParseConnectionStringWithDefaultPort()
    {
        $parser = new ConnectionParser('testuser@example.com/var/www/html');
        
        $this->assertEquals('testuser', $parser->getUser());
        $this->assertEquals('example.com', $parser->getHost());
        $this->assertEquals(22, $parser->getPort());
        $this->assertEquals('/var/www/html', $parser->getPath());
    }

    /**
     * Test parsing connection string without user (should use current user)
     */
    public function testParseConnectionStringWithoutUser()
    {
        $parser = new ConnectionParser('example.com:2222/var/www/html');
        
        // User should be the current system user
        $this->assertNotEmpty($parser->getUser());
        $this->assertEquals('example.com', $parser->getHost());
        $this->assertEquals(2222, $parser->getPort());
        $this->assertEquals('/var/www/html', $parser->getPath());
    }

    /**
     * Test parsing minimal connection string (host only)
     */
    public function testParseMinimalConnectionString()
    {
        $parser = new ConnectionParser('example.com');
        
        $this->assertNotEmpty($parser->getUser());
        $this->assertEquals('example.com', $parser->getHost());
        $this->assertEquals(22, $parser->getPort());
        $this->assertNull($parser->getPath());
    }

    /**
     * Test parsing connection string with path starting with tilde
     */
    public function testParseConnectionStringWithTildePath()
    {
        $parser = new ConnectionParser('testuser@example.com:2222~/public_html');
        
        $this->assertEquals('testuser', $parser->getUser());
        $this->assertEquals('example.com', $parser->getHost());
        $this->assertEquals(2222, $parser->getPort());
        $this->assertEquals('~/public_html', $parser->getPath());
    }

    /**
     * Test connection string reconstruction via __toString
     */
    public function testConnectionStringReconstruction()
    {
        $parser = new ConnectionParser('testuser@example.com:2222/var/www/html');
        
        $reconstructed = (string) $parser;
        
        $this->assertStringContainsString('testuser', $reconstructed);
        $this->assertStringContainsString('example.com', $reconstructed);
        $this->assertStringContainsString('2222', $reconstructed);
        $this->assertStringContainsString('/var/www/html', $reconstructed);
    }

    /**
     * Test connection string with default port omits port in reconstruction
     */
    public function testConnectionStringReconstructionOmitsDefaultPort()
    {
        $parser = new ConnectionParser('testuser@example.com/var/www/html');
        
        $reconstructed = (string) $parser;
        
        $this->assertStringNotContainsString(':22', $reconstructed);
        $this->assertStringContainsString('testuser', $reconstructed);
        $this->assertStringContainsString('example.com', $reconstructed);
    }

    /**
     * Test parsing connection string with IPv4 address
     */
    public function testParseConnectionStringWithIPv4()
    {
        $parser = new ConnectionParser('testuser@192.168.1.100:2222/var/www/html');
        
        $this->assertEquals('testuser', $parser->getUser());
        $this->assertEquals('192.168.1.100', $parser->getHost());
        $this->assertEquals(2222, $parser->getPort());
        $this->assertEquals('/var/www/html', $parser->getPath());
    }
}
