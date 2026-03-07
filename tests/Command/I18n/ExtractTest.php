<?php

declare(strict_types=1);

namespace MODX\CLI\Tests\Command\I18n;

use MODX\CLI\Command\I18n\Extract;
use MODX\CLI\Translation\TranslationManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class ExtractTest extends TestCase
{
    private Extract $command;
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $this->command = new Extract();
        $this->commandTester = new CommandTester($this->command);
    }

    protected function tearDown(): void
    {
        TranslationManager::reset();
    }

    public function testCommandName(): void
    {
        $this->assertSame('i18n:extract', $this->command->getName());
    }

    public function testExecuteFindsTransCalls(): void
    {
        $exitCode = $this->commandTester->execute([]);

        $this->assertSame(0, $exitCode);
        // Command should produce some output (report or "all in sync")
        $output = $this->commandTester->getDisplay();
        $this->assertNotEmpty($output);
    }

    public function testExecuteWithJsonFlag(): void
    {
        $exitCode = $this->commandTester->execute(['--json' => true]);

        $this->assertSame(0, $exitCode);

        $decoded = json_decode($this->commandTester->getDisplay(), true);

        $this->assertIsArray($decoded);
        // Each domain key should have 'new' and 'orphaned' sub-arrays
        foreach ($decoded as $domain => $diff) {
            $this->assertArrayHasKey('new', $diff, "Domain '$domain' missing 'new' key");
            $this->assertArrayHasKey('orphaned', $diff, "Domain '$domain' missing 'orphaned' key");
            $this->assertIsArray($diff['new']);
            $this->assertIsArray($diff['orphaned']);
        }
    }

    public function testExecuteWithDomainFilter(): void
    {
        $exitCode = $this->commandTester->execute(['--domain' => 'commands', '--json' => true]);

        $this->assertSame(0, $exitCode);

        $decoded = json_decode($this->commandTester->getDisplay(), true);

        $this->assertIsArray($decoded);
        $this->assertArrayHasKey('commands', $decoded);
        // Other domains should not appear
        $this->assertArrayNotHasKey('errors', $decoded);
        $this->assertArrayNotHasKey('messages', $decoded);
    }
}
