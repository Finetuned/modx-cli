<?php

namespace MODX\CLI\Formatter;

/**
 * A formatter to display log entries with colors
 */
class ColoredLog
{
    /**
     * @var array
     */
    protected $levelColors = [
        'ERROR' => 'red',
        'WARN' => 'yellow',
        'INFO' => 'green',
        'DEBUG' => 'blue',
    ];

    /**
     * @var array
     */
    protected $colors = [
        'black' => "\033[0;30m",
        'red' => "\033[0;31m",
        'green' => "\033[0;32m",
        'yellow' => "\033[0;33m",
        'blue' => "\033[0;34m",
        'magenta' => "\033[0;35m",
        'cyan' => "\033[0;36m",
        'white' => "\033[0;37m",
        'reset' => "\033[0m",
    ];

    /**
     * Format a log entry with colors
     *
     * @param array $entry The entry.
     *
     * @return string
     */
    public function format(array $entry): string
    {
        $level = strtoupper($entry['level']);
        $color = $this->getColorForLevel($level);

        $timestamp = isset($entry['timestamp']) ? date('Y-m-d H:i:s', $entry['timestamp']) : date('Y-m-d H:i:s');
        $message = isset($entry['message']) ? $entry['message'] : '';

        return sprintf(
            "%s [%s%s%s] %s",
            $timestamp,
            $this->colors[$color],
            str_pad($level, 5),
            $this->colors['reset'],
            $message
        );
    }

    /**
     * Get the color for a log level
     *
     * @param string $level The level.
     *
     * @return string
     */
    protected function getColorForLevel(string $level): string
    {
        return isset($this->levelColors[$level]) ? $this->levelColors[$level] : 'white';
    }

    /**
     * Format multiple log entries with colors
     *
     * @param array $entries The entries.
     *
     * @return string
     */
    public function formatMultiple(array $entries): string
    {
        $output = '';
        foreach ($entries as $entry) {
            $output .= $this->format($entry) . PHP_EOL;
        }
        return $output;
    }
}
