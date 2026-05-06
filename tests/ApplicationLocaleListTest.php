<?php

declare(strict_types=1);

namespace MODX\CLI\Tests;

use MODX\CLI\Application;
use MODX\CLI\Translation\TranslationManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\ApplicationTester;

class ApplicationLocaleListTest extends TestCase
{
    protected function setUp(): void
    {
        TranslationManager::reset();
    }

    protected function tearDown(): void
    {
        TranslationManager::reset();
    }

    public function testRootListUsesGermanLocale(): void
    {
        $application = new Application();
        $application->setAutoExit(false);

        $tester = new ApplicationTester($application);
        $exitCode = $tester->run(['--locale' => 'de'], ['decorated' => false]);
        $output = $tester->getDisplay();

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('Verwendung:', $output);
        $this->assertStringContainsString('Optionen:', $output);
        $this->assertStringContainsString('Verf', $output);
        $this->assertStringContainsString('Befehle:', $output);
        $this->assertStringContainsString('MODX CLI aktualisieren', $output);
        $this->assertStringNotContainsString('Usage:', $output);
        $this->assertStringNotContainsString('Available commands:', $output);
    }
}
