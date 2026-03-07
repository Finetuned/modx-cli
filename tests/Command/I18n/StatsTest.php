<?php

declare(strict_types=1);

namespace MODX\CLI\Tests\Command\I18n;

use MODX\CLI\Command\I18n\Stats;
use MODX\CLI\Translation\TranslationManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class StatsTest extends TestCase
{
    private Stats $command;
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $this->command = new Stats();
        $this->commandTester = new CommandTester($this->command);
    }

    protected function tearDown(): void
    {
        TranslationManager::reset();
    }

    public function testCommandName(): void
    {
        $this->assertSame('i18n:stats', $this->command->getName());
    }

    public function testExecuteShowsTable(): void
    {
        $exitCode = $this->commandTester->execute([]);

        $output = $this->commandTester->getDisplay();

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('Translation Coverage Statistics', $output);
        $this->assertStringContainsString('en', $output);
    }

    public function testExecuteWithJsonFlag(): void
    {
        $exitCode = $this->commandTester->execute(['--json' => true]);

        $this->assertSame(0, $exitCode);

        $decoded = json_decode($this->commandTester->getDisplay(), true);
        $this->assertIsArray($decoded);
        $this->assertNotEmpty($decoded);

        // Each entry should have locale, domain, total, translated, missing, coverage
        $first = $decoded[0];
        $this->assertArrayHasKey('locale', $first);
        $this->assertArrayHasKey('domain', $first);
        $this->assertArrayHasKey('total', $first);
        $this->assertArrayHasKey('translated', $first);
        $this->assertArrayHasKey('missing', $first);
        $this->assertArrayHasKey('coverage', $first);
    }

    public function testExecuteWithDomainFilter(): void
    {
        $exitCode = $this->commandTester->execute(['--domain' => 'errors']);

        $output = $this->commandTester->getDisplay();

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('errors', $output);
        // Other domains should not appear in the table
        $this->assertStringNotContainsString('messages', $output);
        $this->assertStringNotContainsString('commands', $output);
    }

    public function testExecuteWithUnknownDomainProducesNoRows(): void
    {
        $exitCode = $this->commandTester->execute(['--domain' => 'nonexistent']);

        $output = $this->commandTester->getDisplay();

        $this->assertSame(0, $exitCode);
        // Table should be rendered but empty — no locale rows
        $this->assertStringContainsString('Translation Coverage Statistics', $output);
    }
}
