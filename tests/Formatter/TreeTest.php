<?php

namespace MODX\CLI\Tests\Formatter;

use MODX\CLI\Formatter\Tree;
use PHPUnit\Framework\TestCase;

class TreeTest extends TestCase
{
    protected $formatter;

    protected function setUp(): void
    {
        $this->formatter = new Tree();
    }

    public function testFormatSimpleArray()
    {
        $data = [
            'item1' => 'value1',
            'item2' => 'value2',
            'item3' => 'value3',
        ];

        $output = $this->formatter->format($data);

        $this->assertStringContainsString('├── item1: value1', $output);
        $this->assertStringContainsString('├── item2: value2', $output);
        $this->assertStringContainsString('└── item3: value3', $output);
    }

    public function testFormatNestedArray()
    {
        $data = [
            'parent' => [
                'child1' => 'value1',
                'child2' => 'value2',
            ],
        ];

        $output = $this->formatter->format($data);

        $this->assertStringContainsString('└── parent', $output);
        $this->assertStringContainsString('├── child1: value1', $output);
        $this->assertStringContainsString('└── child2: value2', $output);
    }

    public function testFormatDeeplyNestedArray()
    {
        $data = [
            'level1' => [
                'level2' => [
                    'level3' => 'deep value',
                ],
            ],
        ];

        $output = $this->formatter->format($data);

        $this->assertStringContainsString('└── level1', $output);
        $this->assertStringContainsString('└── level2', $output);
        $this->assertStringContainsString('└── level3: deep value', $output);
    }

    public function testFormatMultipleRootItems()
    {
        $data = [
            'root1' => [
                'child1' => 'value1',
            ],
            'root2' => [
                'child2' => 'value2',
            ],
        ];

        $output = $this->formatter->format($data);

        $this->assertStringContainsString('├── root1', $output);
        $this->assertStringContainsString('└── root2', $output);
    }

    public function testFormatUsesCorrectPrefixForLastItem()
    {
        $data = [
            'item1' => 'value1',
            'item2' => 'value2',
        ];

        $output = $this->formatter->format($data);

        // First item should use ├──
        $this->assertStringContainsString('├── item1', $output);
        // Last item should use └──
        $this->assertStringContainsString('└── item2', $output);
    }

    public function testFormatUsesCorrectIndentation()
    {
        $data = [
            'parent1' => [
                'child1' => 'value1',
                'child2' => 'value2',
            ],
            'parent2' => [
                'child3' => 'value3',
            ],
        ];

        $output = $this->formatter->format($data);

        // Check that children are properly indented
        $this->assertStringContainsString('│   ├── child1', $output);
        $this->assertStringContainsString('│   └── child2', $output);
        $this->assertStringContainsString('    └── child3', $output);
    }

    public function testFormatWithEmptyArray()
    {
        $output = $this->formatter->format([]);

        $this->assertEquals('', $output);
    }

    public function testFormatWithCustomPrefix()
    {
        $data = [
            'item1' => 'value1',
            'item2' => 'value2',
        ];

        $output = $this->formatter->format($data, '  ');

        $this->assertStringContainsString('  ├── item1', $output);
        $this->assertStringContainsString('  └── item2', $output);
    }

    public function testFormatMixedScalarAndArrayValues()
    {
        $data = [
            'scalar' => 'simple value',
            'array' => [
                'nested' => 'nested value',
            ],
            'another_scalar' => 'another simple value',
        ];

        $output = $this->formatter->format($data);

        $this->assertStringContainsString('├── scalar: simple value', $output);
        $this->assertStringContainsString('├── array', $output);
        $this->assertStringContainsString('│   └── nested: nested value', $output);
        $this->assertStringContainsString('└── another_scalar: another simple value', $output);
    }

    public function testFormatPreservesKeyOrder()
    {
        $data = [
            'first' => 'value1',
            'second' => 'value2',
            'third' => 'value3',
        ];

        $output = $this->formatter->format($data);
        $lines = explode(PHP_EOL, trim($output));

        $this->assertStringContainsString('first', $lines[0]);
        $this->assertStringContainsString('second', $lines[1]);
        $this->assertStringContainsString('third', $lines[2]);
    }

    public function testFormatWithNumericKeys()
    {
        $data = [
            0 => 'value0',
            1 => 'value1',
            2 => 'value2',
        ];

        $output = $this->formatter->format($data);

        $this->assertStringContainsString('├── 0: value0', $output);
        $this->assertStringContainsString('├── 1: value1', $output);
        $this->assertStringContainsString('└── 2: value2', $output);
    }

    public function testFormatWithSpecialCharactersInKeys()
    {
        $data = [
            'key-with-dashes' => 'value1',
            'key_with_underscores' => 'value2',
            'key.with.dots' => 'value3',
        ];

        $output = $this->formatter->format($data);

        $this->assertStringContainsString('key-with-dashes: value1', $output);
        $this->assertStringContainsString('key_with_underscores: value2', $output);
        $this->assertStringContainsString('key.with.dots: value3', $output);
    }

    public function testFormatWithSpecialCharactersInValues()
    {
        $data = [
            'item1' => 'value with <html> & "quotes"',
            'item2' => 'value with newline\ncharacter',
        ];

        $output = $this->formatter->format($data);

        $this->assertStringContainsString('value with <html> & "quotes"', $output);
        $this->assertStringContainsString('value with newline\ncharacter', $output);
    }

    public function testFormatWithEmptyStringValues()
    {
        $data = [
            'empty' => '',
            'notEmpty' => 'value',
        ];

        $output = $this->formatter->format($data);

        $this->assertStringContainsString('├── empty: ', $output);
        $this->assertStringContainsString('└── notEmpty: value', $output);
    }

    public function testFormatWithZeroValues()
    {
        $data = [
            'zero_int' => 0,
            'zero_string' => '0',
        ];

        $output = $this->formatter->format($data);

        $this->assertStringContainsString('├── zero_int: 0', $output);
        $this->assertStringContainsString('└── zero_string: 0', $output);
    }

    public function testFormatWithBooleanValues()
    {
        $data = [
            'true_value' => true,
            'false_value' => false,
        ];

        $output = $this->formatter->format($data);

        $this->assertStringContainsString('├── true_value: 1', $output);
        $this->assertStringContainsString('└── false_value: ', $output);
    }

    public function testFormatComplexHierarchy()
    {
        $data = [
            'config' => [
                'database' => [
                    'host' => 'localhost',
                    'port' => 3306,
                ],
                'cache' => [
                    'enabled' => true,
                    'ttl' => 3600,
                ],
            ],
            'features' => [
                'logging' => 'enabled',
                'debug' => 'disabled',
            ],
        ];

        $output = $this->formatter->format($data);

        $this->assertStringContainsString('├── config', $output);
        $this->assertStringContainsString('│   ├── database', $output);
        $this->assertStringContainsString('│   │   ├── host: localhost', $output);
        $this->assertStringContainsString('│   │   └── port: 3306', $output);
        $this->assertStringContainsString('└── features', $output);
    }

    public function testFormatOutputEndsWithNewline()
    {
        $data = [
            'item' => 'value',
        ];

        $output = $this->formatter->format($data);

        $this->assertStringEndsWith(PHP_EOL, $output);
    }

    public function testFormatMultipleItemsEachEndWithNewline()
    {
        $data = [
            'item1' => 'value1',
            'item2' => 'value2',
        ];

        $output = $this->formatter->format($data);

        // Count newlines - should have one per item
        $newlineCount = substr_count($output, PHP_EOL);
        $this->assertEquals(2, $newlineCount);
    }
}
