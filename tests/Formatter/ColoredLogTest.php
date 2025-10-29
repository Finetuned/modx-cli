<?php

namespace MODX\CLI\Tests\Formatter;

use MODX\CLI\Formatter\ColoredLog;
use PHPUnit\Framework\TestCase;

class ColoredLogTest extends TestCase
{
    protected $formatter;

    protected function setUp(): void
    {
        $this->formatter = new ColoredLog();
    }

    public function testFormatWithErrorLevel()
    {
        $entry = [
            'level' => 'error',
            'message' => 'Test error message',
            'timestamp' => strtotime('2025-10-29 10:00:00')
        ];

        $output = $this->formatter->format($entry);

        $this->assertStringContainsString('ERROR', $output);
        $this->assertStringContainsString('Test error message', $output);
        $this->assertStringContainsString('2025-10-29 10:00:00', $output);
        $this->assertStringContainsString("\033[0;31m", $output); // Red color
        $this->assertStringContainsString("\033[0m", $output);     // Reset color
    }

    public function testFormatWithWarnLevel()
    {
        $entry = [
            'level' => 'warn',
            'message' => 'Test warning message',
            'timestamp' => strtotime('2025-10-29 10:00:00')
        ];

        $output = $this->formatter->format($entry);

        $this->assertStringContainsString('WARN', $output);
        $this->assertStringContainsString('Test warning message', $output);
        $this->assertStringContainsString("\033[0;33m", $output); // Yellow color
    }

    public function testFormatWithInfoLevel()
    {
        $entry = [
            'level' => 'info',
            'message' => 'Test info message',
            'timestamp' => strtotime('2025-10-29 10:00:00')
        ];

        $output = $this->formatter->format($entry);

        $this->assertStringContainsString('INFO', $output);
        $this->assertStringContainsString('Test info message', $output);
        $this->assertStringContainsString("\033[0;32m", $output); // Green color
    }

    public function testFormatWithDebugLevel()
    {
        $entry = [
            'level' => 'debug',
            'message' => 'Test debug message',
            'timestamp' => strtotime('2025-10-29 10:00:00')
        ];

        $output = $this->formatter->format($entry);

        $this->assertStringContainsString('DEBUG', $output);
        $this->assertStringContainsString('Test debug message', $output);
        $this->assertStringContainsString("\033[0;34m", $output); // Blue color
    }

    public function testFormatWithUnknownLevel()
    {
        $entry = [
            'level' => 'unknown',
            'message' => 'Test unknown level message',
            'timestamp' => strtotime('2025-10-29 10:00:00')
        ];

        $output = $this->formatter->format($entry);

        $this->assertStringContainsString('UNKNOWN', $output);
        $this->assertStringContainsString('Test unknown level message', $output);
        $this->assertStringContainsString("\033[0;37m", $output); // White color (default)
    }

    public function testFormatWithMissingTimestamp()
    {
        $entry = [
            'level' => 'info',
            'message' => 'Test message without timestamp'
        ];

        $output = $this->formatter->format($entry);

        // Should use current timestamp
        $this->assertStringContainsString('INFO', $output);
        $this->assertStringContainsString('Test message without timestamp', $output);
        $this->assertMatchesRegularExpression('/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/', $output);
    }

    public function testFormatWithMissingMessage()
    {
        $entry = [
            'level' => 'info',
            'timestamp' => strtotime('2025-10-29 10:00:00')
        ];

        $output = $this->formatter->format($entry);

        $this->assertStringContainsString('INFO', $output);
        $this->assertStringContainsString('2025-10-29 10:00:00', $output);
        // Message should be empty string
    }

    public function testFormatLevelPadding()
    {
        $entry = [
            'level' => 'info',
            'message' => 'Test',
            'timestamp' => strtotime('2025-10-29 10:00:00')
        ];

        $output = $this->formatter->format($entry);

        // Level should be padded to 5 characters
        $this->assertMatchesRegularExpression('/\[.*INFO .*\]/', $output);
    }

    public function testFormatMultipleEntries()
    {
        $entries = [
            [
                'level' => 'error',
                'message' => 'First error',
                'timestamp' => strtotime('2025-10-29 10:00:00')
            ],
            [
                'level' => 'warn',
                'message' => 'Second warning',
                'timestamp' => strtotime('2025-10-29 10:00:01')
            ],
            [
                'level' => 'info',
                'message' => 'Third info',
                'timestamp' => strtotime('2025-10-29 10:00:02')
            ],
        ];

        $output = $this->formatter->formatMultiple($entries);

        $this->assertStringContainsString('ERROR', $output);
        $this->assertStringContainsString('First error', $output);
        $this->assertStringContainsString('WARN', $output);
        $this->assertStringContainsString('Second warning', $output);
        $this->assertStringContainsString('INFO', $output);
        $this->assertStringContainsString('Third info', $output);

        // Should have 3 lines (each ending with PHP_EOL)
        $lines = explode(PHP_EOL, trim($output));
        $this->assertCount(3, $lines);
    }

    public function testFormatMultipleWithEmptyArray()
    {
        $output = $this->formatter->formatMultiple([]);

        $this->assertEquals('', $output);
    }

    public function testFormatMultiplePreservesOrder()
    {
        $entries = [
            ['level' => 'error', 'message' => 'First', 'timestamp' => time()],
            ['level' => 'warn', 'message' => 'Second', 'timestamp' => time()],
            ['level' => 'info', 'message' => 'Third', 'timestamp' => time()],
        ];

        $output = $this->formatter->formatMultiple($entries);
        $lines = explode(PHP_EOL, trim($output));

        $this->assertStringContainsString('First', $lines[0]);
        $this->assertStringContainsString('Second', $lines[1]);
        $this->assertStringContainsString('Third', $lines[2]);
    }

    public function testFormatWithMixedCaseLevel()
    {
        $entry = [
            'level' => 'WaRn',
            'message' => 'Mixed case warning',
            'timestamp' => strtotime('2025-10-29 10:00:00')
        ];

        $output = $this->formatter->format($entry);

        // Level should be normalized to uppercase
        $this->assertStringContainsString('WARN', $output);
        $this->assertStringContainsString("\033[0;33m", $output); // Yellow color
    }

    public function testFormatIncludesAllColorCodes()
    {
        $entry = [
            'level' => 'error',
            'message' => 'Test message',
            'timestamp' => strtotime('2025-10-29 10:00:00')
        ];

        $output = $this->formatter->format($entry);

        // Should include color code and reset code
        $this->assertStringContainsString("\033[", $output);
        $this->assertStringContainsString("\033[0m", $output);
    }

    public function testFormatWithSpecialCharactersInMessage()
    {
        $entry = [
            'level' => 'info',
            'message' => 'Message with <html> & special "characters"',
            'timestamp' => strtotime('2025-10-29 10:00:00')
        ];

        $output = $this->formatter->format($entry);

        $this->assertStringContainsString('Message with <html> & special "characters"', $output);
    }

    public function testFormatWithLongMessage()
    {
        $longMessage = str_repeat('This is a long message. ', 50);
        $entry = [
            'level' => 'info',
            'message' => $longMessage,
            'timestamp' => strtotime('2025-10-29 10:00:00')
        ];

        $output = $this->formatter->format($entry);

        $this->assertStringContainsString($longMessage, $output);
    }

    public function testFormatWithEmptyMessage()
    {
        $entry = [
            'level' => 'info',
            'message' => '',
            'timestamp' => strtotime('2025-10-29 10:00:00')
        ];

        $output = $this->formatter->format($entry);

        $this->assertStringContainsString('INFO', $output);
        $this->assertStringContainsString('2025-10-29 10:00:00', $output);
    }
}
