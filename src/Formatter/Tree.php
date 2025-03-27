<?php

namespace MODX\CLI\Formatter;

/**
 * A formatter to display data as a tree
 */
class Tree
{
    /**
     * @var string
     */
    protected $prefix = '';

    /**
     * @var string
     */
    protected $childPrefix = '├── ';

    /**
     * @var string
     */
    protected $lastChildPrefix = '└── ';

    /**
     * @var string
     */
    protected $indent = '│   ';

    /**
     * @var string
     */
    protected $lastIndent = '    ';

    /**
     * Format data as a tree
     *
     * @param array $data
     * @param string $prefix
     *
     * @return string
     */
    public function format(array $data, $prefix = '')
    {
        $this->prefix = $prefix;
        $output = '';

        $keys = array_keys($data);
        $last = end($keys);

        foreach ($data as $key => $value) {
            $isLast = ($key === $last);
            $linePrefix = $isLast ? $this->lastChildPrefix : $this->childPrefix;
            $childIndent = $isLast ? $this->lastIndent : $this->indent;

            if (is_array($value)) {
                $output .= $this->prefix . $linePrefix . $key . PHP_EOL;
                $formatter = new self();
                $output .= $formatter->format($value, $this->prefix . $childIndent);
            } else {
                $output .= $this->prefix . $linePrefix . $key . ': ' . $value . PHP_EOL;
            }
        }

        return $output;
    }
}
