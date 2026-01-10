<?php

namespace MODX\CLI\Command\Source;

use MODX\CLI\Command\ProcessorCmd;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to create a MODX media source
 */
class Create extends ProcessorCmd
{
    protected $processor = 'Source\Create';

    protected $name = 'source:create';
    protected $description = 'Create a MODX media source';

    protected function getArguments()
    {
        return array(
            array(
                'name',
                InputArgument::REQUIRED,
                'The name of the media source'
            ),
        );
    }

    protected function getOptions()
    {
        return array_merge(parent::getOptions(), array(
            array(
                'description',
                null,
                InputOption::VALUE_REQUIRED,
                'The description of the media source',
                ''
            ),
            array(
                'class_key',
                null,
                InputOption::VALUE_REQUIRED,
                'The class key of the media source',
                'MODX\\Revolution\\Sources\\modFileMediaSource'
            ),
            array(
                'source-properties',
                null,
                InputOption::VALUE_REQUIRED,
                'The properties of the media source (JSON format)',
                ''
            ),
        ));
    }

    protected function beforeRun(array &$properties = array(), array &$options = array())
    {
        // Add the name to the properties
        $properties['name'] = $this->argument('name');

        // Add options to the properties
        $optionKeys = array('description', 'class_key');

        foreach ($optionKeys as $key) {
            if ($this->option($key) !== null) {
                $properties[$key] = $this->option($key);
            }
        }

        // Handle source-properties separately (maps to 'properties' in MODX)
        if ($this->option('source-properties') !== null) {
            $raw = $this->option('source-properties');
            $decoded = null;

            if (is_string($raw) && $raw !== '') {
                $decoded = json_decode($raw, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $decoded = null;
                }
            }

            if (is_array($decoded)) {
                $properties['properties'] = $decoded;
            } elseif (is_string($raw) && $raw !== '') {
                if ($this->modx && $this->modx->parser) {
                    $properties['properties'] = $this->modx->parser->parsePropertyString($raw);
                } else {
                    $properties['properties'] = $this->parsePropertyString($raw);
                }
            } else {
                $properties['properties'] = $raw;
            }
        }
    }

    protected function parsePropertyString(string $raw): array
    {
        $pairs = preg_split('/[;&]/', $raw, -1, PREG_SPLIT_NO_EMPTY);
        $properties = [];

        foreach ($pairs as $pair) {
            $parts = explode('=', $pair, 2);
            $key = trim($parts[0]);
            $value = isset($parts[1]) ? trim($parts[1]) : '';
            if ($key !== '') {
                $properties[$key] = $value;
            }
        }

        return $properties;
    }

    protected function processResponse(array $response = array())
    {
        if ($this->option('json')) {
            return parent::processResponse($response);
        }
        
        if (isset($response['success']) && $response['success']) {
            $this->info('Media source created successfully');

            if (isset($response['object']) && isset($response['object']['id'])) {
                $this->info('Source ID: ' . $response['object']['id']);
            }
            return 0;
        } else {
            $this->error('Failed to create media source');

            if (isset($response['message'])) {
                $this->error($response['message']);
            }
            return 1;
        }
    }
}
