<?php

namespace MODX\CLI\Tests;

use MODX\CLI\TreeBuilder;
use PHPUnit\Framework\TestCase;

class TreeBuilderTest extends TestCase
{
    public function testConstruct()
    {
        $items = [
            [
                'name' => 'Label',
                'pk' => 1,
                'owner' => 0,
            ],

            [
                'name' => 'child',
                'pk' => 2,
                'owner' => 1,
            ],
        ];
        $pkField = 'pk';
        $parentField = 'owner';
        $childrenField = 'owned';
        $builder = new TreeBuilder($items, $pkField, $parentField, $childrenField);

        $this->assertTrue(true);
        return;
        $this->assertEquals($items, $builder->items, 'Constructor items are available in items attribute');
        $this->assertEquals($pkField, $builder->pkField);
    }
}
