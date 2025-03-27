<?php namespace MODX\CLI\Command\Extra;

use MODX\CLI\Command\BaseCmd;
use Symfony\Component\Console\Helper\Table;

/**
 * A command to get a list of extras in MODX
 */
class Extras extends BaseCmd
{
    const MODX = true;

    protected $name = 'extra:list';
    protected $description = 'Get a list of extras in MODX';

    protected function process()
    {
        // Get all namespaces
        $namespaces = $this->modx->getCollection('modNamespace');
        
        if (empty($namespaces)) {
            $this->info('No namespaces found');
            return 0;
        }
        
        $extras = array();
        
        /** @var \MODX\Revolution\modNamespace $namespace */
        foreach ($namespaces as $namespace) {
            $name = $namespace->get('name');
            
            // Skip core namespaces
            if ($name === 'core') {
                continue;
            }
            
            // Get the package
            $package = $this->modx->getObject('transport.modTransportPackage', array(
                'package_name' => $name
            ));
            
            $extras[] = array(
                'name' => $name,
                'path' => $namespace->get('path'),
                'version' => $package ? $package->get('version') : 'Unknown',
                'installed' => $package ? ($package->get('installed') ? date('Y-m-d H:i:s', strtotime($package->get('installed'))) : 'No') : 'No',
            );
        }
        
        if (empty($extras)) {
            $this->info('No extras found');
            return 0;
        }
        
        // Sort extras by name
        usort($extras, function($a, $b) {
            return strcmp($a['name'], $b['name']);
        });
        
        $table = new Table($this->output);
        $table->setHeaders(array('Name', 'Path', 'Version', 'Installed'));
        
        foreach ($extras as $extra) {
            $table->addRow(array(
                $extra['name'],
                $extra['path'],
                $extra['version'],
                $extra['installed'],
            ));
        }
        
        $table->render();
        
        return 0;
    }
}
