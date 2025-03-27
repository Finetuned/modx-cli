<?php

namespace MODX\CLI\Command\Package;

use MODX\CLI\Command\ListProcessor;

/**
 * A command to get a list of upgradeable packages in MODX
 */
class Upgradeable extends ListProcessor
{
    protected $processor = 'workspace/packages/getlist';
    protected $headers = array(
        'signature', 'name', 'version', 'release', 'installed', 'provider'
    );

    protected $name = 'package:upgradeable';
    protected $description = 'Get a list of upgradeable packages in MODX';
    protected $defaultsProperties = array(
        'newest_only' => true
    );

    protected function parseValue($value, $column)
    {
        if ($column === 'installed') {
            return $value ? date('Y-m-d H:i:s', strtotime($value)) : 'Not installed';
        }

        if ($column === 'provider') {
            return $this->renderObject('transport.modTransportProvider', $value, 'name');
        }

        return parent::parseValue($value, $column);
    }

    protected function processResponse(array $results = array())
    {
        $total = $results['total'];
        $results = $results['results'];

        // Filter out packages that are not upgradeable
        $upgradeable = array();
        foreach ($results as $package) {
            if (isset($package['updateable']) && $package['updateable']) {
                $upgradeable[] = $package;
            }
        }

        if (empty($upgradeable)) {
            $this->info('No upgradeable packages found');
            return;
        }

        $this->renderBody($upgradeable);
        if ($this->showPagination) {
            $this->renderPagination($upgradeable, count($upgradeable));
        }
    }
}
