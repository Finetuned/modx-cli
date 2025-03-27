<?php namespace MODX\CLI\Tests;

use MODX\CLI\Application;
use PHPUnit\Framework\TestCase;

class ApplicationTest extends TestCase
{
    public function testApplicationInitialization()
    {
        $app = new Application();
        $this->assertInstanceOf(Application::class, $app);
        $this->assertEquals('MODX CLI', $app->getName());
        $this->assertEquals('1.0.0', $app->getVersion());
    }

    public function testGetDefaultCommands()
    {
        $app = new Application();
        $commands = $app->all();
        
        // Check for built-in Symfony commands
        $this->assertArrayHasKey('list', $commands);
        $this->assertArrayHasKey('help', $commands);
        
        // Check for our custom commands
        $this->assertArrayHasKey('version', $commands);
        $this->assertArrayHasKey('system:info', $commands);
        $this->assertArrayHasKey('system:clearcache', $commands);
        $this->assertArrayHasKey('resource:list', $commands);
    }

    public function testGetDefaultInputDefinition()
    {
        $app = new Application();
        $definition = $app->getDefinition();
        
        // Check for our custom options
        $this->assertTrue($definition->hasOption('site'));
        $this->assertEquals('s', $definition->getOption('site')->getShortcut());
    }
}
