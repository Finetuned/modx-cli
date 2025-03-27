<?php namespace MODX\CLI\Command;

use Symfony\Component\Console\Helper\Table;

/**
 * Command to help display an object as a table (mostly fot get* processors)
 */
abstract class GetProcessor extends ProcessorCmd
{
    protected $headers = array(
        'id', 'name', 'description'
    );

    protected function processResponse(array $results = array())
    {
        $object = $results['object'];

        $table = new Table($this->output);
        $table->setHeaders($this->headers);
        $table->addRow($this->processRow($object));

        $table->render();
    }
}
