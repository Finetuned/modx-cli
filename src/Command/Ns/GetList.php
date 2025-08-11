<?php

namespace MODX\CLI\Command\Ns;

use MODX\CLI\Command\ListProcessor;

/**
 * A command to get a list of namespaces in MODX
 */
class GetList extends ListProcessor
{
    protected $processor = 'workspace/namespace/getlist';
    protected $headers = array(
        'id', 'name', 'path', 'assets_path'
    );

    protected $name = 'ns:list';
    protected $description = 'Get a list of namespaces in MODX';

    protected function processResponse(array $results = array())
    {
        // Handle case where processor returns data directly without 'results' wrapper
        if (isset($results['results'])) {
            return parent::processResponse($results);
        }
        
        // If no 'results' key, treat the entire response as results
        if (isset($results['success']) && $results['success'] && isset($results['object'])) {
            $namespaces = $results['object'];
            $total = count($namespaces);
            
            if ($this->option('json')) {
                $output = [
                    'total' => $total,
                    'results' => $namespaces
                ];
                $this->output->writeln(json_encode($output, JSON_PRETTY_PRINT));
                return 0;
            }

            $this->renderBody($namespaces);
            if ($this->showPagination) {
                $this->renderPagination($namespaces, $total);
            }
            return 0;
        }
        
        // Fallback to parent processing
        return parent::processResponse($results);
    }
}
