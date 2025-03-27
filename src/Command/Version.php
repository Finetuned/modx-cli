<?php namespace MODX\CLI\Command;

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
        $this->info('MODX CLI version ' . $app->getVersion());
        
        // Try to get MODX version if available
        $modx = $this->getMODX();
        if ($modx) {
            $version = $modx->getVersionData();
            $this->info('MODX version ' . $version['full_version']);
        }
        
        return 0;
    }
}
