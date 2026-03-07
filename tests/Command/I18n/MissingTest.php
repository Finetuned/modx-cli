<?php

declare(strict_types=1);

namespace MODX\CLI\Tests\Command\I18n;

use MODX\CLI\Command\I18n\Missing;
use MODX\CLI\Translation\TranslationManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class MissingTest extends TestCase
{
    private Missing $command;
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $this->command = new Missing();
        $this->commandTester = new CommandTester($this->command);
    }

    protected function tearDown(): void
    {
        TranslationManager::reset();
    }

    public function testCommandName(): void
    {
        $this->assertSame('i18n:missing', $this->command->getName());
    }

    public function testExecuteEnLocaleReportsNoMissingKeys(): void
    {
        $exitCode = $this->commandTester->execute(['locale' => 'en']);

        $output = $this->commandTester->getDisplay();

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('No missing keys for locale "en"', $output);
    }

    public function testExecuteUnknownLocaleReportsAllBaseKeysAsMissing(): void
    {
        $exitCode = $this->commandTester->execute(['locale' => 'xx']);

        $output = $this->commandTester->getDisplay();

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('xx', $output);
    }

    public function testExecuteWithDomainFilter(): void
    {
        $exitCode = $this->commandTester->execute([
            'locale'   => 'en',
            '--domain' => 'errors',
        ]);

        $output = $this->commandTester->getDisplay();

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('No missing keys', $output);
    }

    public function testExecuteWithJsonFlag(): void
    {
        $exitCode = $this->commandTester->execute([
            'locale'  => 'en',
            '--json'  => true,
        ]);

        $decoded = json_decode($this->commandTester->getDisplay(), true);

        $this->assertSame(0, $exitCode);
        $this->assertIsArray($decoded);
        $this->assertSame('en', $decoded['locale']);
        $this->assertSame(0, $decoded['total']);
        $this->assertSame([], $decoded['missing']);
    }
}
