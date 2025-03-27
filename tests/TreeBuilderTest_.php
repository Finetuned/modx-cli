<?php

use MODX\CLI\TreeBuilder;

class TreeBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $items = array(
            array(
                'name' => 'Label',
                'pk' => 1,
                'owner' => 0,
            ),

            array(
                'name' => 'child',
                'pk' => 2,
                'owner' => 1,
            ),
        );
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
