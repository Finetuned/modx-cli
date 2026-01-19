<?php

namespace MODX\CLI\Tests\Alias;

use MODX\CLI\Alias\Resolver;
use MODX\CLI\Configuration\Yaml\YamlConfig;
use PHPUnit\Framework\TestCase;

/**
 * Test Alias Resolver functionality
 */
class ResolverTest extends TestCase
{
    // ============================================
    // Alias Detection Tests
    // ============================================

    public function testIsAliasDetectsAtPrefix()
    {
        $config = $this->createMock(YamlConfig::class);
        $resolver = new Resolver($config);

        $this->assertTrue($resolver->isAlias('@prod'));
        $this->assertTrue($resolver->isAlias('@staging'));
        $this->assertTrue($resolver->isAlias('@dev'));
    }

    public function testIsAliasReturnsFalseWithoutPrefix()
    {
        $config = $this->createMock(YamlConfig::class);
        $resolver = new Resolver($config);

        $this->assertFalse($resolver->isAlias('prod'));
        $this->assertFalse($resolver->isAlias('staging'));
    }

    public function testIsAliasReturnsFalseForEmptyString()
    {
        $config = $this->createMock(YamlConfig::class);
        $resolver = new Resolver($config);

        $this->assertFalse($resolver->isAlias(''));
    }

    // ============================================
    // Alias Resolution Tests
    // ============================================

    public function testResolveAliasReturnsDefinition()
    {
        $config = $this->createMock(YamlConfig::class);
        $config->method('getAlias')
            ->with('prod')
            ->willReturn(['ssh' => 'user@production.com:/var/www']);

        $resolver = new Resolver($config);
        $result = $resolver->resolveAlias('@prod');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('ssh', $result);
        $this->assertEquals('user@production.com:/var/www', $result['ssh']);
    }

    public function testResolveAliasStripsAtPrefix()
    {
        $config = $this->createMock(YamlConfig::class);
        $config->expects($this->once())
            ->method('getAlias')
            ->with('prod');  // Should be called without @ prefix

        $resolver = new Resolver($config);

        try {
            $resolver->resolveAlias('@prod');
        } catch (\Exception $e) {
            // Expected when alias not found
        }
    }

    public function testResolveAliasThrowsExceptionForUnknownAlias()
    {
        $config = $this->createMock(YamlConfig::class);
        $config->method('getAlias')
            ->with('unknown')
            ->willReturn(null);

        $resolver = new Resolver($config);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Alias '@unknown' not found");

        $resolver->resolveAlias('@unknown');
    }

    // ============================================
    // Alias Group Tests
    // ============================================

    public function testIsAliasGroupDetectsGroups()
    {
        $config = $this->createMock(YamlConfig::class);
        $resolver = new Resolver($config);

        // Array without 'ssh' key is a group
        $group = ['@prod', '@staging', '@dev'];
        $this->assertTrue($resolver->isAliasGroup($group));
    }

    public function testIsAliasGroupReturnsFalseForSingleAlias()
    {
        $config = $this->createMock(YamlConfig::class);
        $resolver = new Resolver($config);

        // Array with 'ssh' key is a single alias
        $singleAlias = ['ssh' => 'user@example.com'];
        $this->assertFalse($resolver->isAliasGroup($singleAlias));
    }

    public function testGetAliasGroupMembersReturnsMembers()
    {
        $config = $this->createMock(YamlConfig::class);
        $resolver = new Resolver($config);

        $group = ['@prod', '@staging', '@dev'];
        $members = $resolver->getAliasGroupMembers($group);

        $this->assertIsArray($members);
        $this->assertCount(3, $members);
        $this->assertEquals(['@prod', '@staging', '@dev'], $members);
    }

    public function testGetAliasGroupMembersHandlesEmptyGroup()
    {
        $config = $this->createMock(YamlConfig::class);
        $resolver = new Resolver($config);

        $emptyGroup = [];
        $members = $resolver->getAliasGroupMembers($emptyGroup);

        $this->assertIsArray($members);
        $this->assertEmpty($members);
    }

    // ============================================
    // Constructor Tests
    // ============================================

    public function testConstructorAcceptsYamlConfig()
    {
        $config = $this->createMock(YamlConfig::class);
        $resolver = new Resolver($config);

        $this->assertInstanceOf(Resolver::class, $resolver);
    }

    // ============================================
    // Integration-like Tests
    // ============================================

    public function testResolveMultipleAliases()
    {
        $config = $this->createMock(YamlConfig::class);
        $config->method('getAlias')
            ->willReturnMap([
                ['prod', ['ssh' => 'user@prod.com']],
                ['staging', ['ssh' => 'user@staging.com']],
            ]);

        $resolver = new Resolver($config);

        $prod = $resolver->resolveAlias('@prod');
        $this->assertEquals('user@prod.com', $prod['ssh']);

        $staging = $resolver->resolveAlias('@staging');
        $this->assertEquals('user@staging.com', $staging['ssh']);
    }
}
