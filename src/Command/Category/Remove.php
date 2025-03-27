<?php namespace MODX\CLI\Command\Category;

use MODX\CLI\Command\ProcessorCmd;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to remove a MODX category
 */
class Remove extends ProcessorCmd
{
    protected $processor = 'element/category/remove';
    protected $required = array('id');

    protected $name = 'category:remove';
    protected $description = 'Remove a MODX category';

    protected function getArguments()
    {
        return array(
            array(
                'id',
                InputArgument::REQUIRED,
                'The ID of the category to remove'
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
                'Force removal without confirmation'
            ),
        ));
    }

    protected function beforeRun(array &$properties = array(), array &$options = array())
    {
        $id = $this->argument('id');
        
        // Get the category to display information
        $category = $this->modx->getObject('modCategory', $id);
        if (!$category) {
            $this->error("Category with ID {$id} not found");
            return false;
        }
        
        $categoryName = $category->get('category');
        
        // Confirm removal unless --force is used
        if (!$this->option('force')) {
            if (!$this->confirm("Are you sure you want to remove category '{$categoryName}' (ID: {$id})?")) {
                $this->info('Operation aborted');
                return false;
            }
        }
    }

    protected function processResponse(array $response = array())
    {
        if (isset($response['success']) && $response['success']) {
            $this->info('Category removed successfully');
        } else {
            $this->error('Failed to remove category');
            
            if (isset($response['message'])) {
                $this->error($response['message']);
            }
        }
    }
}
