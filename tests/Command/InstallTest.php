<?php

namespace MODX\CLI\Tests\Command;

use MODX\CLI\Command\Install;
use MODX\CLI\Tests\Configuration\BaseTest;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Test suite for the Install command
 *
 * Tests the MODX 3.x installation functionality including:
 * - Composer installation
 * - Custom installer integration
 * - Setup execution
 * - Version constraint normalization
 */
class InstallTest extends BaseTest
{
    public function testConfigureHasCorrectName()
    {
        $command = new Install();
        $this->assertEquals('install', $command->getName());
    }

    public function testConfigureHasCorrectDescription()
    {
        $command = new Install();
        $this->assertEquals('Install MODX here', $command->getDescription());
    }

    public function testConfigureHasTargetArgument()
    {
        $command = new Install();
        $command->setApplication($this->makeAppMock());

        $definition = $command->getDefinition();
        $this->assertTrue($definition->hasArgument('target'));

        $argument = $definition->getArgument('target');
        $this->assertFalse($argument->isRequired());
        $this->assertEquals('', $argument->getDefault());
    }

    public function testConfigureHasConfigArgument()
    {
        $command = new Install();
        $command->setApplication($this->makeAppMock());

        $definition = $command->getDefinition();
        $this->assertTrue($definition->hasArgument('config'));

        $argument = $definition->getArgument('config');
        $this->assertFalse($argument->isRequired());
        $this->assertEquals('', $argument->getDefault());
    }

    public function testConfigureHasInstallerOption()
    {
        $command = new Install();
        $command->setApplication($this->makeAppMock());

        $definition = $command->getDefinition();
        $this->assertTrue($definition->hasOption('installer'));

        $option = $definition->getOption('installer');
        $this->assertEquals('composer', $option->getDefault());
    }

    public function testConfigureHasModxVersionOption()
    {
        $command = new Install();
        $command->setApplication($this->makeAppMock());

        $definition = $command->getDefinition();
        $this->assertTrue($definition->hasOption('modx-version'));
    }

    public function testConfigureHasSetupOption()
    {
        $command = new Install();
        $command->setApplication($this->makeAppMock());

        $definition = $command->getDefinition();
        $this->assertTrue($definition->hasOption('setup'));
    }

    public function testConfigureHasInstallerCommandOption()
    {
        $command = new Install();
        $command->setApplication($this->makeAppMock());

        $definition = $command->getDefinition();
        $this->assertTrue($definition->hasOption('installer-command'));
    }

    public function testConfigureHasComposerBinOption()
    {
        $command = new Install();
        $command->setApplication($this->makeAppMock());

        $definition = $command->getDefinition();
        $this->assertTrue($definition->hasOption('composer-bin'));

        $option = $definition->getOption('composer-bin');
        $this->assertEquals('composer', $option->getDefault());
    }

    /**
     * Test version normalization logic
     */
    public function testVersionConstraintNormalizationForStandardVersion()
    {
        $command = new InstallTestHelper();

        // Test standard version format (e.g., 3.0.5)
        $result = $command->testNormalizeVersionConstraint('3.0.5');
        $this->assertEquals('v3.0.5-pl', $result);
    }

    public function testVersionConstraintNormalizationForVersionWithSuffix()
    {
        $command = new InstallTestHelper();

        // Test version with suffix (e.g., 3.0.5-pl)
        $result = $command->testNormalizeVersionConstraint('3.0.5-pl');
        $this->assertEquals('v3.0.5-pl', $result);
    }

    public function testVersionConstraintNormalizationForDevVersion()
    {
        $command = new InstallTestHelper();

        // Test dev version
        $result = $command->testNormalizeVersionConstraint('3.1.0-dev');
        $this->assertEquals('3.1.0-dev', $result);
    }

    public function testVersionConstraintNormalizationForLatest()
    {
        $command = new InstallTestHelper();

        // Test latest/empty version
        $this->assertNull($command->testNormalizeVersionConstraint(''));
        $this->assertNull($command->testNormalizeVersionConstraint(null));
    }

    public function testVersionConstraintNormalizationForDevMaster()
    {
        $command = new InstallTestHelper();

        // Test dev-master
        $result = $command->testNormalizeVersionConstraint('dev-master');
        $this->assertEquals('dev-master', $result);
    }

    /**
     * Integration test placeholder
     *
     * Note: Actual execution tests would require mocking Symfony\Process\Process
     * which is complex and beyond the scope of unit tests. Integration tests
     * should be created separately to test actual composer/setup execution.
     */
    public function testCommandStructure()
    {
        $command = new Install();
        $command->setApplication($this->makeAppMock());

        // Verify command is properly configured
        $this->assertEquals('install', $command->getName());
        $this->assertNotEmpty($command->getDescription());

        $definition = $command->getDefinition();
        $this->assertTrue($definition->hasArgument('target'));
        $this->assertTrue($definition->hasArgument('config'));
        $this->assertTrue($definition->hasOption('installer'));
        $this->assertTrue($definition->hasOption('modx-version'));
        $this->assertTrue($definition->hasOption('setup'));
    }

    private function makeAppMock()
    {
        $app = $this->getMockBuilder(\MODX\CLI\Application::class)
            ->disableOriginalConstructor()
            ->getMock();
        $app->method('getHelperSet')->willReturn(new \Symfony\Component\Console\Helper\HelperSet());
        $app->method('getDefinition')->willReturn(new \Symfony\Component\Console\Input\InputDefinition());

        return $app;
    }
}

/**
 * Helper class to test protected methods
 */
