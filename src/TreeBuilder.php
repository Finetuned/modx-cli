<?php

namespace MODX\CLI;

/**
 * A helper class to build a tree array (multidimensional) from a flat array
 */
class TreeBuilder
{
    protected $items = [];
    protected $parentField = 'parent';
    protected $pkField = 'id';
    protected $childrenField = 'children';
    protected $tree = [];

    /**
     * @param array  $items         The "flat" array to sort.
     * @param string $pkField       The array index used as "primary key".
     * @param string $parentField   The array index used to define the parent.
     * @param string $childrenField The array index used to store the children of an item.
     */
    public function __construct(
        array $items = [],
        string $pkField = 'id',
        string $parentField = 'parent',
        string $childrenField = 'children'
    ) {
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

        return isset($root[$this->childrenField]) ? $root[$this->childrenField] : [];
    }

    /**
     * Process the flat array as a tree
     *
     * @return $this
     */
    public function buildTree(): self
    {
        if (empty($this->items)) {
            $this->tree = [];
            return $this;
        }

        $indexed = [];
        // First sort by some "PK"
        foreach ($this->items as $row) {
            $row[$this->childrenField] = [];
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
                $indexed[$parentKey] = [$this->childrenField => []];
            }

            $indexed[$parentKey][$this->childrenField][$row[$this->pkField]] =& $indexed[$pk];
        }

        // Wrap in a fake "root" so we can sort items if needed
        if ($root !== null) {
            $this->tree = [$root => $indexed[$root]];
        } else {
            $this->tree = [];
        }

        return $this;
    }

    /**
     * Convenient method to sort & retrieve the tree
     *
     * @param string $field The field.
     * @param string $dir   The direction (ASC or DESC).
     *
     * @return array
     */
    public function getSortedTree(string $field = 'menuindex', string $dir = 'ASC'): array
    {
        return $this->sortTree($field, $dir)->getTree();
    }

    /**
     * Sort the tree
     *
     * @param string $field The field.
     * @param string $dir   The direction (ASC or DESC).
     *
     * @return $this
     */
    public function sortTree(string $field = 'menuindex', string $dir = 'ASC'): self
    {
        foreach ($this->tree as &$item) {
            if (isset($item[$this->childrenField]) && !empty($item[$this->childrenField])) {
                $this->sortChildren($item, $field, $dir);
            }
        }

        return $this;
    }

    /**
     * @param array  $item  The item.
     * @param string $field The field.
     * @param string $dir   The direction (ASC or DESC).
     */
    protected function sortChildren(array &$item, string $field = 'menuindex', string $dir = 'ASC'): void
    {
        $sortedChildren = [];
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
