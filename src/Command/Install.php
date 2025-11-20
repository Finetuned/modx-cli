<?php

namespace MODX\CLI\Command;

use Symfony\Component\Console\Input\InputArgument;

/**
 * Placeholder install command (dependency not bundled in this build)
 */
class Install extends BaseCmd
{
    protected $name = 'install';
    protected $description = 'Install MODX here';

    /**
     * Inform user that installer dependency is not available in this build.
     */
    protected function process()
    {
        $this->error('The install command is disabled: requires melting/modx-installer which is not bundled here.');
        return 1;
    }

    /**
     * @inheritDoc
     */
    protected function getArguments()
    {
        return array(
            array(
                'source',
                InputArgument::OPTIONAL,
                'Path to MODX source (unused while command is disabled)',
                ''
            ),
            array(
                'config',
                InputArgument::OPTIONAL,
                'Path to configuration file (unused while command is disabled)',
                ''
            ),
        );
    }
}
