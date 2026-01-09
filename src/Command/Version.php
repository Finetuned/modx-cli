<?php

namespace MODX\CLI\Command;

/**
 * A command to display the CLI version
 */
class Version extends BaseCmd
{
    protected $name = 'version';
    protected $description = 'Display the CLI version';

    protected function process()
    {
        $app = $this->getApplication();
        $cliVersion = $app ? $app->getVersion() : null;
        $modxVersion = null;

        // Try to get MODX version if available
        $modx = $this->getMODX();
        if ($modx) {
            $version = $modx->getVersionData();
            $modxVersion = $version['full_version'];
        }

        if ($this->option('json')) {
            $this->output->writeln(json_encode([
                'success' => true,
                'cli_version' => $cliVersion,
                'modx_version' => $modxVersion,
            ], JSON_PRETTY_PRINT));
        } else {
            $this->info('MODX CLI version ' . $cliVersion);
            if ($modxVersion) {
                $this->info('MODX version ' . $modxVersion);
            }
        }

        return 0;
    }
}
