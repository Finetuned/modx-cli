<?php

namespace MODX\CLI\Command;

use Symfony\Component\Console\Helper\Table;

/**
 * Command to help display an object as a table (mostly fot get* processors)
 */
abstract class GetProcessor extends ProcessorCmd
{
    protected $headers = [
        'id', 'name', 'description'
    ];

    /**
     * Handle the processor response.
     *
     * @param array $results The processor response.
     * @return integer
     */
    protected function processResponse(array $results = [])
    {
        if ($this->option('json')) {
            return parent::processResponse($results);
        }

        if (!isset($results['object'])) {
            $this->error('No object found');
            return 1;
        }

        $object = $results['object'];

        $table = new Table($this->output);
        $table->setHeaders($this->headers);
        $table->addRow($this->processRow($object));

        $table->render();
        return 0;
    }
}
