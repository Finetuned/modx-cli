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
        $message = 'The install command is disabled: requires melting/modx-installer which is not bundled here.';
        if ($this->option('json')) {
            $this->output->writeln(json_encode([
                'success' => false,
                'message' => $message,
            ], JSON_PRETTY_PRINT));
        } else {
            $this->error($message);
        }
        return 1;
    }

    /**
     * @inheritDoc
     */
    protected function getArguments()
    {
        return [
            [
                'source',
                InputArgument::OPTIONAL,
                'Path to MODX source (unused while command is disabled)',
                ''
            ],
            [
                'config',
                InputArgument::OPTIONAL,
                'Path to configuration file (unused while command is disabled)',
                ''
            ],
        ];
    }
}
