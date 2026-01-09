<?php

namespace MODX\CLI\Tests\Command\Config;

class FakeConfigStore
{
    private $items = [];

    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    public function get($key, $default = null)
    {
        return $this->items[$key] ?? $default;
    }

    public function set($key, $value = null)
    {
        $this->items[$key] = $value;
    }

    public function remove($key)
    {
        unset($this->items[$key]);
    }

    public function save()
    {
        // No-op for tests.
    }

    public function getAll()
    {
        return $this->items;
    }
}
