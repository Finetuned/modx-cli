<?php namespace MODX\CLI\Tests\SSH;

use MODX\CLI\SSH\ConnectionParser;
use PHPUnit\Framework\TestCase;

/**
 * Test ConnectionParser functionality
 */
class ConnectionParserTest extends TestCase
{
    // ============================================
    // Basic Connection String Parsing Tests
    // ============================================

    public function testParseSimpleHostname()
    {
        $parser = new ConnectionParser('example.com');
        
        $this->assertEquals('example.com', $parser->getHost());
        $this->assertEquals(22, $parser->getPort());
        $this->assertNull($parser->getPath());
    }

    public function testParseWithUser()
    {
        $parser = new ConnectionParser('john@example.com');
        
        $this->assertEquals('john', $parser->getUser());
        $this->assertEquals('example.com', $parser->getHost());
        $this->assertEquals(22, $parser->getPort());
    }

    public function testParseWithCustomPort()
    {
        $parser = new ConnectionParser('john@example.com:2222');
        
        $this->assertEquals('john', $parser->getUser());
        $this->assertEquals('example.com', $parser->getHost());
        $this->assertEquals(2222, $parser->getPort());
    }

    public function testParseWithPath()
    {
        $parser = new ConnectionParser('john@example.com:/var/www/html');
        
        $this->assertEquals('john', $parser->getUser());
        $this->assertEquals('example.com', $parser->getHost());
        $this->assertEquals('/var/www/html', $parser->getPath());
    }

    public function testParseCompleteConnectionString()
    {
        $parser = new ConnectionParser('john@example.com:2222/var/www/html');
        
        $this->assertEquals('john', $parser->getUser());
        $this->assertEquals('example.com', $parser->getHost());
        $this->assertEquals(2222, $parser->getPort());
        $this->assertEquals('/var/www/html', $parser->getPath());
    }

    // ============================================
    // Edge Cases and Special Formats
    // ============================================

    public function testDefaultUserWhenNotSpecified()
    {
        $parser = new ConnectionParser('example.com');
        
        // User should be set to current system user
        $this->assertNotNull($parser->getUser());
        $this->assertIsString($parser->getUser());
    }

    public function testDefaultPortWhenNotSpecified()
    {
        $parser = new ConnectionParser('john@example.com');
        
        $this->assertEquals(22, $parser->getPort());
    }

    public function testPathWithTilde()
    {
        $parser = new ConnectionParser('john@example.com:~/modx');
        
        $this->assertEquals('~/modx', $parser->getPath());
    }

    public function testIPv4Address()
    {
        $parser = new ConnectionParser('john@192.168.1.100');
        
        $this->assertEquals('john', $parser->getUser());
        $this->assertEquals('192.168.1.100', $parser->getHost());
    }

    public function testHostnameWithDots()
    {
        $parser = new ConnectionParser('john@sub.domain.example.com');
        
        $this->assertEquals('sub.domain.example.com', $parser->getHost());
    }

    public function testPathWithMultipleDirectories()
    {
        $parser = new ConnectionParser('john@example.com:/var/www/html/modx/core');
        
        $this->assertEquals('/var/www/html/modx/core', $parser->getPath());
    }

    public function testEmptyPath()
    {
        $parser = new ConnectionParser('john@example.com:');
        
        $this->assertNull($parser->getPath());
    }

    // ============================================
    // Getter Methods Tests
    // ============================================

    public function testGetOriginalReturnsOriginalString()
    {
        $original = 'john@example.com:2222/var/www';
        $parser = new ConnectionParser($original);
        
        $this->assertEquals($original, $parser->getOriginal());
    }

    public function testToStringReconstructsConnectionString()
    {
        $parser = new ConnectionParser('john@example.com:2222/var/www');
        
        $result = (string) $parser;
        
        $this->assertStringContainsString('john@example.com', $result);
        $this->assertStringContainsString('2222', $result);
        $this->assertStringContainsString('/var/www', $result);
    }

    public function testToStringWithDefaultPort()
    {
        $parser = new ConnectionParser('john@example.com');
        
        $result = (string) $parser;
        
        // Port 22 should not be included in string representation
        $this->assertStringNotContainsString(':22', $result);
    }

    public function testGetUserReturnsCorrectUser()
    {
        $parser = new ConnectionParser('john@example.com');
        
        $this->assertEquals('john', $parser->getUser());
    }

    public function testGetHostReturnsCorrectHost()
    {
        $parser = new ConnectionParser('john@example.com');
        
        $this->assertEquals('example.com', $parser->getHost());
    }

    public function testGetPortReturnsCorrectPort()
    {
        $parser = new ConnectionParser('john@example.com:3000');
        
        $this->assertEquals(3000, $parser->getPort());
    }

    public function testGetPathReturnsCorrectPath()
    {
        $parser = new ConnectionParser('john@example.com:/var/www');
        
        $this->assertEquals('/var/www', $parser->getPath());
    }

    // ============================================
    // Data Provider Tests for Multiple Formats
    // ============================================

    /**
     * @dataProvider connectionStringProvider
     */
    public function testVariousConnectionStringFormats($connectionString, $expectedUser, $expectedHost, $expectedPort, $expectedPath)
    {
        $parser = new ConnectionParser($connectionString);
        
        if ($expectedUser !== null) {
            $this->assertEquals($expectedUser, $parser->getUser());
        }
        $this->assertEquals($expectedHost, $parser->getHost());
        $this->assertEquals($expectedPort, $parser->getPort());
        
        if ($expectedPath !== null) {
            $this->assertEquals($expectedPath, $parser->getPath());
        } else {
            $this->assertNull($parser->getPath());
        }
    }

    public static function connectionStringProvider()
    {
        return [
            // [connectionString, user, host, port, path]
            ['example.com', null, 'example.com', 22, null],
            ['user@example.com', 'user', 'example.com', 22, null],
            ['user@example.com:3000', 'user', 'example.com', 3000, null],
            ['user@example.com:/path', 'user', 'example.com', 22, '/path'],
            ['user@example.com:3000/path', 'user', 'example.com', 3000, '/path'],
            ['192.168.1.1', null, '192.168.1.1', 22, null],
            ['user@192.168.1.1:2222', 'user', '192.168.1.1', 2222, null],
            ['sub.domain.com', null, 'sub.domain.com', 22, null],
            ['user@sub.domain.com:~/app', 'user', 'sub.domain.com', 22, '~/app'],
        ];
    }

    // ============================================
    // SSH Config Alias Tests
    // ============================================

    public function testIsAliasDetectsSSHConfigAlias()
    {
        $tempConfig = tempnam(sys_get_temp_dir(), 'modx_ssh_config_');
        $configContent = "Host myalias\n    HostName example.com\n";
        file_put_contents($tempConfig, $configContent);

        $parser = new ConnectionParser('myalias', $tempConfig);

        $this->assertTrue($parser->isAlias());

        unlink($tempConfig);
    }

    public function testIsAliasReturnsFalseForRegularHostname()
    {
        $parser = new ConnectionParser('user@example.com');
        
        // Regular hostname with @ should not be detected as alias
        $this->assertFalse($parser->isAlias());
    }

    // ============================================
    // Special Cases and Edge Cases
    // ============================================

    public function testHostnameWithHyphen()
    {
        $parser = new ConnectionParser('my-server.com');
        
        $this->assertEquals('my-server.com', $parser->getHost());
    }

    public function testHostnameWithUnderscore()
    {
        $parser = new ConnectionParser('my_server.com');
        
        $this->assertEquals('my_server.com', $parser->getHost());
    }

    public function testLowercaseAndUppercaseHostnames()
    {
        $parser = new ConnectionParser('EXAMPLE.COM');
        
        $this->assertEquals('EXAMPLE.COM', $parser->getHost());
    }

    public function testPortAsString()
    {
        $parser = new ConnectionParser('user@example.com:8080');
        
        $this->assertIsInt($parser->getPort());
        $this->assertEquals(8080, $parser->getPort());
    }

    public function testPathWithoutLeadingSlash()
    {
        $parser = new ConnectionParser('user@example.com:home/user/app');
        
        $this->assertEquals('home/user/app', $parser->getPath());
    }
}
