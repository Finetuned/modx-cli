<?php namespace MODX\CLI\Command\Misc;

use MODX\CLI\Command\BaseCmd;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;

/**
 * A command to list columns in a table in MODX
 */
class ListColumns extends BaseCmd
{
    const MODX = true;

    protected $name = 'misc:list-columns';
    protected $description = 'List columns in a table in MODX';

    protected function getArguments()
    {
        return array(
            array(
                'table',
                InputArgument::REQUIRED,
                'The name of the table'
            ),
        );
    }

    protected function process()
    {
        $tableName = $this->argument('table');
        
        // Get the database connection
        $dbname = $this->modx->getOption('dbname');
        
        // Get the columns
        $sql = "SELECT COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT, COLUMN_KEY, EXTRA
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_SCHEMA = '{$dbname}' AND TABLE_NAME = '{$tableName}'
                ORDER BY ORDINAL_POSITION";
        
        $stmt = $this->modx->prepare($sql);
        $stmt->execute();
        
        $columns = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        if (empty($columns)) {
            $this->error("Table '{$tableName}' not found or has no columns");
            return 1;
        }
        
        $table = new Table($this->output);
        $table->setHeaders(array('Column', 'Type', 'Nullable', 'Default', 'Key', 'Extra'));
        
        foreach ($columns as $column) {
            $table->addRow(array(
                $column['COLUMN_NAME'],
                $column['COLUMN_TYPE'],
                $column['IS_NULLABLE'],
                $column['COLUMN_DEFAULT'],
                $column['COLUMN_KEY'],
                $column['EXTRA'],
            ));
        }
        
        $table->render();
        
        return 0;
    }
}
