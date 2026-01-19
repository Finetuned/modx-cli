<?php

namespace MODX\CLI\Command\Misc;

use MODX\CLI\Command\BaseCmd;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;

/**
 * A command to list columns in a database table in MODX
 */
class ListColumns extends BaseCmd
{
    public const MODX = true;

    protected $name = 'misc:list-columns';
    protected $description = 'List columns in a database table in MODX';

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            [
                'table',
                InputArgument::REQUIRED,
                'The name of the table'
            ],
        ];
    }

    /**
     * Execute the command.
     *
     * @return integer
     */
    protected function process()
    {
        $tableName = $this->argument('table');
        $json = (bool) $this->option('json');

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
            $message = "Table '{$tableName}' not found or has no columns";
            if ($json) {
                $this->output->writeln(json_encode([
                    'success' => false,
                    'message' => $message,
                    'table' => $tableName,
                    'columns' => [],
                ], JSON_PRETTY_PRINT));
            } else {
                $this->error($message);
            }
            return 1;
        }

        if ($json) {
            $this->output->writeln(json_encode([
                'success' => true,
                'table' => $tableName,
                'columns' => $columns,
            ], JSON_PRETTY_PRINT));
        } else {
            $table = new Table($this->output);
            $table->setHeaders(['Column', 'Type', 'Nullable', 'Default', 'Key', 'Extra']);

            foreach ($columns as $column) {
                $table->addRow([
                    $column['COLUMN_NAME'],
                    $column['COLUMN_TYPE'],
                    $column['IS_NULLABLE'],
                    $column['COLUMN_DEFAULT'],
                    $column['COLUMN_KEY'],
                    $column['EXTRA'],
                ]);
            }

            $table->render();
        }

        return 0;
    }
}
