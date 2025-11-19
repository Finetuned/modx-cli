<?php

namespace MODX\CLI\Output;

use Symfony\Component\Console\Output\ConsoleSectionOutput;

/**
 * Section Output Handler
 *
 * Provides independent output sections for hierarchical or parallel output.
 * Sections can be updated independently without affecting other output.
 */
class SectionOutput
{
    /**
     * Console section output
     */
    protected ConsoleSectionOutput $section;

    /**
     * Section content lines
     *
     * @var array<int, string>
     */
    protected array $lines = [];

    /**
     * Constructor
     *
     * @param ConsoleSectionOutput $section The console section output
     */
    public function __construct(ConsoleSectionOutput $section)
    {
        $this->section = $section;
    }

    /**
     * Write content to the section
     *
     * @param string $content The content to write
     * @param bool $newline Whether to add a newline
     * @return void
     */
    public function write(string $content, bool $newline = true): void
    {
        if ($newline) {
            $this->section->writeln($content);
            $this->lines[] = $content;
        } else {
            $this->section->write($content);
        }
    }

    /**
     * Overwrite the entire section content
     *
     * @param string|array<int, string> $content The new content
     * @return void
     */
    public function overwrite(string|array $content): void
    {
        $this->section->clear();
        $this->lines = [];

        if (is_array($content)) {
            foreach ($content as $line) {
                $this->write($line);
            }
        } else {
            $this->write($content);
        }
    }

    /**
     * Clear the section
     *
     * @return void
     */
    public function clear(): void
    {
        $this->section->clear();
        $this->lines = [];
    }

    /**
     * Get the section lines
     *
     * @return array<int, string>
     */
    public function getLines(): array
    {
        return $this->lines;
    }

    /**
     * Get the underlying console section
     *
     * @return ConsoleSectionOutput
     */
    public function getSection(): ConsoleSectionOutput
    {
        return $this->section;
    }
}
