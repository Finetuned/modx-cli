<?php

namespace MODX\CLI;

/**
 * A helper class to build a tree array (multidimensional) from a flat array
 */
class TreeBuilder
{
    protected $items = array();
    protected $parentField = 'parent';
    protected $pkField = 'id';
    protected $childrenField = 'children';
    protected $tree = array();

    /**
     * @param array  $items - The "flat" array to sort
     * @param string $pkField - The array index used as "primary key"
     * @param string $parentField - The array index used to define the parent
     * @param string $childrenField - The array index used to store the children of an item
     */
    public function __construct(array $items = array(), $pkField = 'id', $parentField = 'parent', $childrenField = 'children')
    {
        $this->items = $items;
        $this->pkField = $pkField;
        $this->parentField = $parentField;
        $this->childrenField = $childrenField;

        $this->buildTree();
    }

    /**
     * Retrieve the built tree
     *
     * @return array
     */
    public function getTree()
    {
        if (empty($this->tree)) {
            return [];
        }
        
        $root = array_shift($this->tree);

        return isset($root['children']) ? $root['children'] : [];
    }

    /**
     * Process the flat array as a tree
     *
     * @return $this
     */
    public function buildTree()
    {
        if (empty($this->items)) {
            $this->tree = array();
            return $this;
        }
        
        $indexed = array();
        // First sort by some "PK"
        foreach ($this->items as $row) {
            $row[$this->childrenField] = array();
            $indexed[$row[$this->pkField]] = $row;
        }

        // Then assign children to their respective parents
        $root = null;
        foreach ($indexed as $pk => $row) {
            $parentKey = $row[$this->parentField];
            
            // Track root items (those with no parent or empty parent)
            if (!$row[$this->parentField] || empty($row[$this->parentField])) {
                $root = $parentKey;
            }
            
            // Initialize parent's children array if needed
            if (!isset($indexed[$parentKey])) {
                $indexed[$parentKey] = array($this->childrenField => array());
            }
            
            $indexed[$parentKey][$this->childrenField][$row[$this->pkField]] =& $indexed[$pk];
        }

        // Wrap in a fake "root" so we can sort items if needed
        if ($root !== null) {
            $this->tree = array($root => $indexed[$root]);
        } else {
            $this->tree = array();
        }

        return $this;
    }

    /**
     * Convenient method to sort & retrieve the tree
     *
     * @param $field $node
     * @param string $dir
     *
     * @return array
     */
    public function getSortedTree($field = 'menuindex', $dir = 'ASC')
    {
        return $this->sortTree($field, $dir)->getTree();
    }

    /**
     * Sort the tree
     *
     * @param string $field
     * @param string $dir
     *
     * @return $this
     */
    public function sortTree($field = 'menuindex', $dir = 'ASC')
    {
        foreach ($this->tree as &$item) {
            if (isset($item[$this->childrenField]) && !empty($item[$this->childrenField])) {
                $this->sortChildren($item, $field, $dir);
            }
        }

        return $this;
    }

    /**
     * @param array $item
     * @param string $field
     * @param string $dir
     */
    protected function sortChildren(array &$item, $field = 'menuindex', $dir = 'ASC')
    {
        $sortedChildren = array();
        foreach ($item[$this->childrenField] as &$child) {
            // First sort child's children, if any
            if (isset($child[$this->childrenField]) && !empty($child[$this->childrenField])) {
                $this->sortChildren($child, $field, $dir);
            }
            // Then store this child with a sortable key/index
            $sortedChildren[$child[$field]] = $child;
        }

        // Sort children
        switch (strtolower($dir)) {
            case 'desc':
                krsort($sortedChildren);
                break;
            default:
                ksort($sortedChildren);
        }

        $item[$this->childrenField] = $sortedChildren;
    }
}
