<?php

namespace MODX\CLI\Tests\Command\Misc;

use MODX\CLI\Command\Misc\ListColumns;
use MODX\CLI\Tests\Configuration\BaseTest;
use Symfony\Component\Console\Tester\CommandTester;

class ListColumnsTest extends BaseTest
{
    protected $command;
    protected $commandTester;
    protected $modx;

    protected function setUp(): void
    {
        // Create a mock MODX object
        $this->modx = $this->createMock('MODX\Revolution\modX');
        
        // Create the command
        $this->command = new ListColumns();
        $this->command->modx = $this->modx;
        
        // Create a command tester
        $this->commandTester = new CommandTester($this->command);
    }

    public function testConfigureHasCorrectName()
    {
        $this->assertEquals('misc:list-columns', $this->command->getName());
    }

    public function testConfigureHasCorrectDescription()
    {
        $this->assertEquals('List columns in a database table in MODX', $this->command->getDescription());
    }

    public function testConfigureHasTableArgument()
    {
        $definition = $this->command->getDefinition();
        $this->assertTrue($definition->hasArgument('table'));
        $this->assertTrue($definition->getArgument('table')->isRequired());
    }

    public function testExecuteWithValidTable()
    {
        // Mock getOption for database name
        $this->modx->expects($this->once())
            ->method('getOption')
            ->with('dbname')
            ->willReturn('test_db');
        
        // Mock prepare and statement
        $stmt = $this->createMock('\PDOStatement');
        $stmt->expects($this->once())
            ->method('execute');
        
        $stmt->expects($this->once())
            ->method('fetchAll')
            ->with(\PDO::FETCH_ASSOC)
            ->willReturn([
                [
                    'COLUMN_NAME' => 'id',
                    'COLUMN_TYPE' => 'int(11)',
                    'IS_NULLABLE' => 'NO',
                    'COLUMN_DEFAULT' => null,
                    'COLUMN_KEY' => 'PRI',
                    'EXTRA' => 'auto_increment'
                ],
                [
                    'COLUMN_NAME' => 'name',
                    'COLUMN_TYPE' => 'varchar(255)',
                    'IS_NULLABLE' => 'YES',
                    'COLUMN_DEFAULT' => null,
                    'COLUMN_KEY' => '',
                    'EXTRA' => ''
                ]
            ]);
        
        $this->modx->expects($this->once())
            ->method('prepare')
            ->willReturn($stmt);
        
        // Execute the command
        $this->commandTester->execute([
            'table' => 'test_table'
        ]);
        
        // Verify the output contains table headers
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Column', $output);
        $this->assertStringContainsString('Type', $output);
        $this->assertStringContainsString('Nullable', $output);
        $this->assertStringContainsString('Default', $output);
        $this->assertStringContainsString('Key', $output);
        $this->assertStringContainsString('Extra', $output);
        
        // Verify column data is in output
        $this->assertStringContainsString('id', $output);
        $this->assertStringContainsString('int(11)', $output);
        $this->assertStringContainsString('PRI', $output);
        $this->assertStringContainsString('auto_increment', $output);
        $this->assertStringContainsString('name', $output);
        $this->assertStringContainsString('varchar(255)', $output);
        
        $this->assertEquals(0, $this->commandTester->getStatusCode());
    }

    public function testExecuteWithNonExistentTable()
    {
        // Mock getOption for database name
        $this->modx->expects($this->once())
            ->method('getOption')
            ->with('dbname')
            ->willReturn('test_db');
        
        // Mock prepare and statement to return empty results
        $stmt = $this->createMock('\PDOStatement');
        $stmt->expects($this->once())
            ->method('execute');
        
        $stmt->expects($this->once())
            ->method('fetchAll')
            ->with(\PDO::FETCH_ASSOC)
            ->willReturn([]);
        
        $this->modx->expects($this->once())
            ->method('prepare')
            ->willReturn($stmt);
        
        // Execute the command
        $this->commandTester->execute([
            'table' => 'nonexistent_table'
        ]);
        
        // Verify error message
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString("Table 'nonexistent_table' not found or has no columns", $output);
        $this->assertEquals(1, $this->commandTester->getStatusCode());
    }

    public function testExecuteShowsAllColumnProperties()
    {
        // Mock getOption
        $this->modx->method('getOption')->willReturn('test_db');
        
        // Mock statement with column having default value
        $stmt = $this->createMock('\PDOStatement');
        $stmt->method('execute');
        $stmt->method('fetchAll')->willReturn([
            [
                'COLUMN_NAME' => 'status',
                'COLUMN_TYPE' => 'tinyint(1)',
                'IS_NULLABLE' => 'NO',
                'COLUMN_DEFAULT' => '1',
                'COLUMN_KEY' => '',
                'EXTRA' => ''
            ]
        ]);
        
        $this->modx->method('prepare')->willReturn($stmt);
        
        // Execute the command
        $this->commandTester->execute(['table' => 'test_table']);
        
        // Verify all properties are shown
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('status', $output);
        $this->assertStringContainsString('tinyint(1)', $output);
        $this->assertStringContainsString('NO', $output);
        $this->assertStringContainsString('1', $output);
        
        $this->assertEquals(0, $this->commandTester->getStatusCode());
    }

    public function testExecuteWithMultipleColumns()
    {
        // Mock getOption
        $this->modx->method('getOption')->willReturn('test_db');
        
        // Mock statement with multiple columns
        $stmt = $this->createMock('\PDOStatement');
        $stmt->method('execute');
        $stmt->method('fetchAll')->willReturn([
            [
                'COLUMN_NAME' => 'id',
                'COLUMN_TYPE' => 'int(11)',
                'IS_NULLABLE' => 'NO',
                'COLUMN_DEFAULT' => null,
                'COLUMN_KEY' => 'PRI',
                'EXTRA' => 'auto_increment'
            ],
            [
                'COLUMN_NAME' => 'created_at',
                'COLUMN_TYPE' => 'datetime',
                'IS_NULLABLE' => 'YES',
                'COLUMN_DEFAULT' => 'CURRENT_TIMESTAMP',
                'COLUMN_KEY' => '',
                'EXTRA' => ''
            ],
            [
                'COLUMN_NAME' => 'email',
                'COLUMN_TYPE' => 'varchar(100)',
                'IS_NULLABLE' => 'NO',
                'COLUMN_DEFAULT' => null,
                'COLUMN_KEY' => 'UNI',
                'EXTRA' => ''
            ]
        ]);
        
        $this->modx->method('prepare')->willReturn($stmt);
        
        // Execute the command
        $this->commandTester->execute(['table' => 'users']);
        
        // Verify all columns are shown
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('id', $output);
        $this->assertStringContainsString('created_at', $output);
        $this->assertStringContainsString('email', $output);
        $this->assertStringContainsString('CURRENT_TIMESTAMP', $output);
        $this->assertStringContainsString('UNI', $output);
        
        $this->assertEquals(0, $this->commandTester->getStatusCode());
    }

    public function testExecuteWithValidTableJsonOutput()
    {
        $this->modx->expects($this->once())
            ->method('getOption')
            ->with('dbname')
            ->willReturn('test_db');

        $stmt = $this->createMock('\PDOStatement');
        $stmt->expects($this->once())
            ->method('execute');
        $stmt->expects($this->once())
            ->method('fetchAll')
            ->with(\PDO::FETCH_ASSOC)
            ->willReturn([
                [
                    'COLUMN_NAME' => 'id',
                    'COLUMN_TYPE' => 'int(11)',
                    'IS_NULLABLE' => 'NO',
                    'COLUMN_DEFAULT' => null,
                    'COLUMN_KEY' => 'PRI',
                    'EXTRA' => 'auto_increment'
                ]
            ]);

        $this->modx->expects($this->once())
            ->method('prepare')
            ->willReturn($stmt);

        $this->commandTester->execute([
            'table' => 'test_table',
            '--json' => true
        ]);

        $decoded = json_decode($this->commandTester->getDisplay(), true);
        $this->assertTrue($decoded['success']);
        $this->assertEquals('test_table', $decoded['table']);
        $this->assertCount(1, $decoded['columns']);
        $this->assertEquals('id', $decoded['columns'][0]['COLUMN_NAME']);
        $this->assertEquals(0, $this->commandTester->getStatusCode());
    }
}
