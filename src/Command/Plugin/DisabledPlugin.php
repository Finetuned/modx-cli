<?php

namespace MODX\CLI\Command\Plugin;

use MODX\CLI\Command\ListProcessor;

/**
 * A command to get a list of disabled plugins in MODX
 */
class DisabledPlugin extends ListProcessor
{
    protected $processor = 'Element\Plugin\GetList';
    protected $headers = [
        'id', 'name', 'description', 'category'
    ];

    protected $name = 'plugin:disabled';
    protected $description = 'Get a list of disabled plugins in MODX';
    protected $defaultsProperties = [
        'disabled' => 1
    ];

    /**
     * Prepare properties before running the processor.
     *
     * @param array $properties The processor properties.
     * @param array $options    The processor options.
     * @return void
     */
    protected function beforeRun(array &$properties = [], array &$options = [])
    {
        // Ensure the disabled filter is properly passed to the processor
        $properties['disabled'] = 1;
    }

    /**
     * Handle the processor response.
     *
     * @param array $response The processor response.
     * @return integer
     */
    protected function processResponse(array $response = [])
    {
        // Filter results to only show disabled plugins
        if (isset($response['results']) && is_array($response['results'])) {
            $response['results'] = array_filter($response['results'], function ($plugin) {
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

    /**
     * Format raw values for output.
     *
     * @param mixed  $value  The raw column value.
     * @param string $column The column name.
     * @return mixed
     */
    protected function parseValue(mixed $value, string $column)
    {
        if ($column === 'category') {
            return $this->renderObject('modCategory', $value, 'category');
        }

        return parent::parseValue($value, $column);
    }
}
