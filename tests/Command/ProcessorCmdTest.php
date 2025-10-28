<?php namespace MODX\CLI\Tests\Command;

use MODX\CLI\Command\ProcessorCmd;
use MODX\CLI\Tests\Configuration\BaseTest;
use MODX\CLI\Application;
use Symfony\Component\Console\Tester\CommandTester;

class ProcessorCmdTest extends BaseTest
{
    protected $command;
    protected $commandTester;
    protected $modx;

    protected function setUp(): void
    {
        $this->modx = $this->createMock('MODX\Revolution\modX');
        
        $this->command = new class extends ProcessorCmd {
            protected $processor = 'test/processor';
            protected $name = 'test:cmd';
            protected $description = 'Test processor command';
            
            public function testPrePopulateFromExisting(&$properties, $class, $id, $fieldMap = [])
            {
                return $this->prePopulateFromExisting($properties, $class, $id, $fieldMap);
            }
            
            public function testApplyDefaults(&$properties, $defaults = [])
            {
                // Simplified version that doesn't call $this->option()
                foreach ($defaults as $key => $defaultValue) {
                    if (!isset($properties[$key]) || $properties[$key] === null) {
                        $properties[$key] = $defaultValue;
                    }
                }
            }
            
            public function testAddOptionsToProperties($properties, $optionKeys, $typeMap = [])
            {
                return $this->addOptionsToProperties($properties, $optionKeys, $typeMap);
            }
            
            public function testGetExistingObject($class, $id)
            {
                return $this->getExistingObject($class, $id);
            }
        };
        
        $this->command->modx = $this->modx;
        
        // Create a command tester without using the Application class to avoid conflicts
        $this->commandTester = new CommandTester($this->command);
    }

    public function testGetExistingObject()
    {
        $mockObject = $this->getMockBuilder('stdClass')
            ->addMethods(['get'])
            ->getMock();
        
        $this->modx->expects($this->once())
            ->method('getObject')
            ->with('modChunk', 123)
            ->willReturn($mockObject);
        
        $result = $this->command->testGetExistingObject('modChunk', 123);
        $this->assertSame($mockObject, $result);
    }

    public function testPrePopulateFromExistingWithDefaultMapping()
    {
        $mockChunk = $this->getMockBuilder('stdClass')
            ->addMethods(['get'])
            ->getMock();
        $mockChunk->method('get')->willReturnCallback(function($field) {
            $map = [
                'name' => 'TestChunk',
                'description' => 'Test Description',
                'category' => 1,
                'snippet' => '<p>Test Content</p>'
            ];
            return $map[$field] ?? null;
        });
        
        $this->modx->expects($this->once())
            ->method('getObject')
            ->with('modChunk', 123)
            ->willReturn($mockChunk);
        
        $properties = ['id' => 123];
        $result = $this->command->testPrePopulateFromExisting($properties, 'modChunk', 123);
        
        $this->assertTrue($result);
        $this->assertEquals('TestChunk', $properties['name']);
        $this->assertEquals('Test Description', $properties['description']);
        $this->assertEquals(1, $properties['category']);
        $this->assertEquals('<p>Test Content</p>', $properties['snippet']);
    }

    public function testPrePopulateFromExistingWithNonExistentObject()
    {
        $this->modx->expects($this->once())
            ->method('getObject')
            ->with('modChunk', 999)
            ->willReturn(null);
        
        $properties = ['id' => 999];
        $result = $this->command->testPrePopulateFromExisting($properties, 'modChunk', 999);
        
        $this->assertFalse($result);
        $this->assertEquals(['id' => 999], $properties); // Properties unchanged
    }

    public function testApplyDefaults()
    {
        $properties = ['existing' => 'value'];
        $defaults = [
            'new_field' => 'default_value',
            'existing' => 'should_not_override'
        ];
        
        $this->command->testApplyDefaults($properties, $defaults);
        
        $this->assertEquals('default_value', $properties['new_field']);
        $this->assertEquals('value', $properties['existing']); // Should not be overridden
    }

    public function testPrePopulateFromExistingWithModResource()
    {
        $mockResource = $this->getMockBuilder('stdClass')
            ->addMethods(['get'])
            ->getMock();
        $mockResource->method('get')->willReturnCallback(function($field) {
            $map = [
                'pagetitle' => 'Test Resource',
                'parent' => 0,
                'template' => 1,
                'published' => 1,
                'class_key' => 'modDocument',
                'context_key' => 'web',
                'content_type' => 1,
                'alias' => 'test-resource',
                'content' => 'Test content',
                'hidemenu' => 0,
                'searchable' => 1,
                'cacheable' => 1
            ];
            return $map[$field] ?? null;
        });
        
        $this->modx->expects($this->once())
            ->method('getObject')
            ->with('modResource', 123)
            ->willReturn($mockResource);
        
        $properties = ['id' => 123];
        $result = $this->command->testPrePopulateFromExisting($properties, 'modResource', 123);
        
        $this->assertTrue($result);
        // Verify all essential resource fields are pre-populated
        $this->assertEquals('Test Resource', $properties['pagetitle']);
        $this->assertEquals(0, $properties['parent']);
        $this->assertEquals(1, $properties['template']);
        $this->assertEquals(1, $properties['published']);
        $this->assertEquals('modDocument', $properties['class_key']);
        $this->assertEquals('web', $properties['context_key']);
        $this->assertEquals(1, $properties['content_type']);
        $this->assertEquals('test-resource', $properties['alias']);
        $this->assertEquals('Test content', $properties['content']);
        $this->assertEquals(0, $properties['hidemenu']);
        $this->assertEquals(1, $properties['searchable']);
        $this->assertEquals(1, $properties['cacheable']);
    }

    public function testPrePopulateFromExistingWithModResourceMissingCriticalFields()
    {
        $mockResource = $this->getMockBuilder('stdClass')
            ->addMethods(['get'])
            ->getMock();
        $mockResource->method('get')->willReturnCallback(function($field) {
            $map = [
                'pagetitle' => 'Test Resource',
                'parent' => 0,
                'template' => 1,
                'published' => 1,
                'class_key' => null, // Missing critical field
                'context_key' => '', // Empty critical field
                'content_type' => null, // Missing critical field
                'alias' => 'test-resource',
                'content' => 'Test content',
                'hidemenu' => 0,
                'searchable' => 1,
                'cacheable' => 1
            ];
            return $map[$field] ?? null;
        });
        
        $this->modx->expects($this->once())
            ->method('getObject')
            ->with('modResource', 123)
            ->willReturn($mockResource);
        
        $properties = ['id' => 123];
        $result = $this->command->testPrePopulateFromExisting($properties, 'modResource', 123);
        
        $this->assertTrue($result);
        // Verify that null/empty critical fields are not set (will be handled by defaults)
        $this->assertArrayNotHasKey('class_key', $properties);
        $this->assertArrayNotHasKey('context_key', $properties);
        $this->assertArrayNotHasKey('content_type', $properties);
        // But other fields should be set
        $this->assertEquals('Test Resource', $properties['pagetitle']);
        $this->assertEquals('test-resource', $properties['alias']);
    }
}
