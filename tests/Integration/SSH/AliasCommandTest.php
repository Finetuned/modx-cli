<?php

namespace MODX\CLI\Tests\Integration\SSH;

use PHPUnit\Framework\TestCase;
use MODX\CLI\Alias\Resolver;
use MODX\CLI\Configuration\Yaml\YamlConfig;

/**
 * Integration tests for YAML alias resolution and command execution
 * 
 * Note: These tests do not require a MODX instance as they test
 * alias resolution logic independently using temporary config files.
 */
class AliasCommandTest extends TestCase
{
    /**
     * @var string Path to test alias configuration
     */
    protected string $testAliasConfigPath;

    /**
     * Setup test environment with alias configuration
     */
    protected function setUp(): void
    {
        // YamlConfig looks for modx-cli.yml in current working directory
        // Create it with test aliases (prefixed with @ as YamlConfig expects)
        $this->testAliasConfigPath = getcwd() . '/modx-cli.yml';
        
        $testConfig = <<<YAML
"@prod":
  ssh: "deploy@production.example.com:22/var/www/html"
"@staging":
  ssh: "deploy@staging.example.com/var/www/staging"
"@dev":
  ssh: "developer@dev.example.com:2222~/projects/modx"
"@all":
  - prod
  - staging
  - dev
"@servers":
  - prod
  - staging
YAML;
        
        file_put_contents($this->testAliasConfigPath, $testConfig);
    }

    /**
     * Clean up test alias configuration
     */
    protected function tearDown(): void
    {
        if (file_exists($this->testAliasConfigPath)) {
            unlink($this->testAliasConfigPath);
        }
        
        parent::tearDown();
    }

    /**
     * Test detecting if a command is an alias
     */
    public function testIsAliasDetection()
    {
        $config = new YamlConfig();
        $resolver = new Resolver($config);
        
        $this->assertTrue($resolver->isAlias('@prod'));
        $this->assertTrue($resolver->isAlias('@staging'));
        $this->assertTrue($resolver->isAlias('@all'));
        $this->assertFalse($resolver->isAlias('prod'));
        $this->assertFalse($resolver->isAlias('system:info'));
        $this->assertFalse($resolver->isAlias('user@host.com'));
    }

    /**
     * Test resolving a single alias to its SSH connection string
     */
    public function testResolveSingleAlias()
    {
        $config = new YamlConfig();
        $resolver = new Resolver($config);
        
        $prodAlias = $resolver->resolveAlias('@prod');
        
        $this->assertIsArray($prodAlias);
        $this->assertArrayHasKey('ssh', $prodAlias);
        $this->assertEquals('deploy@production.example.com:22/var/www/html', $prodAlias['ssh']);
    }

    /**
     * Test resolving multiple aliases
     */
    public function testResolveMultipleAliases()
    {
        $config = new YamlConfig();
        $resolver = new Resolver($config);
        
        $prodAlias = $resolver->resolveAlias('@prod');
        $stagingAlias = $resolver->resolveAlias('@staging');
        $devAlias = $resolver->resolveAlias('@dev');
        
        $this->assertEquals('deploy@production.example.com:22/var/www/html', $prodAlias['ssh']);
        $this->assertEquals('deploy@staging.example.com/var/www/staging', $stagingAlias['ssh']);
        $this->assertEquals('developer@dev.example.com:2222~/projects/modx', $devAlias['ssh']);
    }

    /**
     * Test detecting if an alias is a group
     */
    public function testIsAliasGroupDetection()
    {
        $config = new YamlConfig();
        $resolver = new Resolver($config);
        
        $prodAlias = $resolver->resolveAlias('@prod');
        $allAlias = $resolver->resolveAlias('@all');
        
        $this->assertFalse($resolver->isAliasGroup($prodAlias));
        $this->assertTrue($resolver->isAliasGroup($allAlias));
    }

    /**
     * Test retrieving members of an alias group
     */
    public function testGetAliasGroupMembers()
    {
        $config = new YamlConfig();
        $resolver = new Resolver($config);
        
        $allAlias = $resolver->resolveAlias('@all');
        $members = $resolver->getAliasGroupMembers($allAlias);
        
        $this->assertIsArray($members);
        $this->assertCount(3, $members);
        $this->assertContains('prod', $members);
        $this->assertContains('staging', $members);
        $this->assertContains('dev', $members);
    }

    /**
     * Test error handling for non-existent alias
     */
    public function testResolveNonExistentAlias()
    {
        $config = new YamlConfig();
        $resolver = new Resolver($config);
        
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Alias '@nonexistent' not found");
        
        $resolver->resolveAlias('@nonexistent');
    }
}
