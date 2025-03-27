<?php namespace MODX\CLI\Command\Template;

use MODX\CLI\Command\ProcessorCmd;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to get a MODX template
 */
class Get extends ProcessorCmd
{
    protected $processor = 'element/template/get';
    protected $required = array('id');

    protected $name = 'template:get';
    protected $description = 'Get a MODX template';

    protected function getArguments()
    {
        return array(
            array(
                'id',
                InputArgument::REQUIRED,
                'The ID of the template to get'
            ),
        );
    }

    protected function getOptions()
    {
        return array_merge(parent::getOptions(), array(
            array(
                'format',
                'f',
                InputOption::VALUE_REQUIRED,
                'Output format (table, json)',
                'table'
            ),
        ));
    }

    protected function processResponse(array $response = array())
    {
        if (!isset($response['object'])) {
            if ($this->option('json') || $this->option('format') === 'json') {
                $this->output->writeln(json_encode([
                    'success' => false,
                    'message' => 'Template not found'
                ], JSON_PRETTY_PRINT));
                return;
            }
            $this->error('Template not found');
            return;
        }
        
        $template = $response['object'];
        
        // Check for both --json flag and --format=json
        if ($this->option('json') || $this->option('format') === 'json') {
            $this->output->writeln(json_encode($template, JSON_PRETTY_PRINT));
            return;
        }
        
        // Default to table format
        $table = new Table($this->output);
        $table->setHeaders(array('Property', 'Value'));
        
        // Add basic properties
        $properties = array(
            'id', 'templatename', 'description', 'category', 'locked', 'static', 'static_file', 'icon'
        );
        
        foreach ($properties as $property) {
            if (isset($template[$property])) {
                $value = $template[$property];
                
                // Format boolean values
                if ($property === 'locked' || $property === 'static') {
                    $value = $value ? 'Yes' : 'No';
                }
                
                // Format category
                if ($property === 'category' && !empty($value)) {
                    $category = $this->modx->getObject('modCategory', $value);
                    if ($category) {
                        $value .= ' (' . $category->get('category') . ')';
                    }
                }
                
                $table->addRow(array($property, $value));
            }
        }
        
        $table->render();
        
        // Display template content separately
        if (isset($template['content']) && !empty($template['content'])) {
            $this->output->writeln("\n<info>Template Content:</info>");
            $this->output->writeln($template['content']);
        }
    }
}
