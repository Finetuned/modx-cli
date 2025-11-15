<?php

namespace MODX\CLI\Command\Plugin;

use MODX\CLI\Command\ListProcessor;

/**
 * A command to get a list of disabled plugins in MODX
 */
class DisabledPlugin extends ListProcessor
{
    protected $processor = 'Element\Plugin\GetList';
    protected $headers = array(
        'id', 'name', 'description', 'category'
    );

    protected $name = 'plugin:disabled';
    protected $description = 'Get a list of disabled plugins in MODX';
    protected $defaultsProperties = array(
        'disabled' => 1
    );

    protected function beforeRun(array &$properties = array(), array &$options = array())
    {
        // Ensure the disabled filter is properly passed to the processor
        $properties['disabled'] = 1;
    }

    protected function processResponse(array $response = array())
    {
        // Filter results to only show disabled plugins
        if (isset($response['results']) && is_array($response['results'])) {
            $response['results'] = array_filter($response['results'], function($plugin) {
                return isset($plugin['disabled']) && $plugin['disabled'] == 1;
            });
            // Re-index array to avoid gaps in keys
            $response['results'] = array_values($response['results']);
            
            // Update total count if present
            if (isset($response['total'])) {
                $response['total'] = count($response['results']);
            }
        }

        return parent::processResponse($response);
    }

    protected function parseValue($value, $column)
    {
        if ($column === 'category') {
            return $this->renderObject('modCategory', $value, 'category');
        }

        return parent::parseValue($value, $column);
    }
}
