<?php

namespace MODX\CLI\Tests\Command\Config;

class FakeExcludedCommands
{
    private $items = [];

    public function __construct(array $items = [])
    {
        if (array_keys($items) !== range(0, count($items) - 1)) {
            $this->items = array_keys(array_filter($items));
        } else {
            $this->items = $items;
        }
    }

    public function getAll()
    {
        return array_values($this->items);
    }

    public function set($class, $value = null)
    {
        if (!in_array($class, $this->items, true)) {
            $this->items[] = $class;
        }
    }

    public function remove($class)
    {
        $this->items = array_values(array_filter(
            $this->items,
            fn($item) => $item !== $class
        ));
    }

    public function get($class, $default = null)
    {
        return in_array($class, $this->items, true) ? true : $default;
    }

    public function save()
    {
        // No-op for tests.
    }
}
