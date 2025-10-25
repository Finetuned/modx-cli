<?php

namespace MODX\CLI\Command\Package;

use MODX\CLI\Command\ProcessorCmd;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to install a package in MODX
 */
class Install extends ProcessorCmd
{
    protected $processor = 'Workspace\Packages\Install';
    protected $required = array('signature');

    protected $name = 'package:install';
    protected $description = 'Install a package in MODX';

    protected function getArguments()
    {
        return array(
            array(
                'signature',
                InputArgument::REQUIRED,
                'The signature of the package to install'
            ),
        );
    }

    protected function getOptions()
    {
        return array_merge(parent::getOptions(), array(
            array(
                'force',
                'f',
                InputOption::VALUE_NONE,
                'Force installation without confirmation'
            ),
        ));
    }

    protected function beforeRun(array &$properties = array(), array &$options = array())
    {
        $signature = $this->argument('signature');

        // Get the package to display information
        $package = $this->modx->getObject('transport.modTransportPackage', array('signature' => $signature));
        if (!$package) {
            $this->error("Package with signature '{$signature}' not found");
            return false;
        }

        // Check if the package is already installed
        if ($package->get('installed') !== null) {
            $this->error("Package '{$signature}' is already installed");
            return false;
        }

        // Confirm installation unless --force is used
        if (!$this->option('force')) {
            if (!$this->confirm("Are you sure you want to install package '{$signature}'?")) {
                $this->info('Operation aborted');
                return false;
            }
        }
    }

    protected function processResponse(array $response = array())
    {
        if (isset($response['success']) && $response['success']) {
            $this->info('Package installed successfully');
        } else {
            $this->error('Failed to install package');

            if (isset($response['message'])) {
                $this->error($response['message']);
            }
        }
    }
}
