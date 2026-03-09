<?php

declare(strict_types=1);

namespace MODX\CLI\Tests\Command\I18n;

use MODX\CLI\Command\I18n\Validate;
use MODX\CLI\Translation\TranslationManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Tester\CommandTester;

class ValidateTest extends TestCase
{
    private Validate $command;
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $this->command = new Validate();
        // --locale is a global application option; add it here to enable standalone testing
        $this->command->addOption('locale', null, InputOption::VALUE_REQUIRED, 'Locale filter');
        $this->commandTester = new CommandTester($this->command);
    }

    protected function tearDown(): void
    {
        TranslationManager::reset();
    }

    public function testCommandName(): void
    {
        $this->assertSame('i18n:validate', $this->command->getName());
    }

    public function testExecuteAllLocalesExitsZeroWhenComplete(): void
    {
        $exitCode = $this->commandTester->execute([]);

        $output = $this->commandTester->getDisplay();

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('Validating translations', $output);
        $this->assertStringContainsString('en', $output);
    }

    public function testExecuteWithLocaleFilter(): void
    {
        $exitCode = $this->commandTester->execute(['--locale' => 'en']);

        $output = $this->commandTester->getDisplay();

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('en', $output);
    }

    public function testExecuteWithJsonFlag(): void
    {
        $exitCode = $this->commandTester->execute(['--json' => true]);

        $decoded = json_decode($this->commandTester->getDisplay(), true);

        $this->assertIsArray($decoded);
        $this->assertArrayHasKey('en', $decoded);
        // en should have no issues
        $this->assertSame([], $decoded['en']);
    }
}
