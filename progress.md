- What works

See context/Task-*.md for work to date

- What's left to build
See Current work focus in context/activeContext.md

- Current status

The project is around 25% complete at this point.

- Known issues

Running vendor/bin/phpunit executes 215 tests

There were 3 errors in the test code. I attempted to correct this by adding use MODX\Revolution\modSystemSetting to tests/Configuration/ComponentText class which does not work.

- 1 MODX\CLI\Tests\Configuration\ComponentTest::testSaveShouldCreateSystemSetting
PHPUnit\Framework\MockObject\ReflectionException: Class "modSystemSetting" does not exist

/Users/julianweaver/dev/modx/modx-cli/tests/Configuration/ComponentTest.php:146

Caused by
ReflectionException: Class "modSystemSetting" does not exist

/Users/julianweaver/dev/modx/modx-cli/tests/Configuration/ComponentTest.php:146

- 2 MODX\CLI\Tests\Configuration\ComponentTest::testSaveShouldFail
PHPUnit\Framework\MockObject\ReflectionException: Class "modSystemSetting" does not exist

/Users/julianweaver/dev/modx/modx-cli/tests/Configuration/ComponentTest.php:177

Caused by
ReflectionException: Class "modSystemSetting" does not exist

/Users/julianweaver/dev/modx/modx-cli/tests/Configuration/ComponentTest.php:177

- 3 MODX\CLI\Tests\Configuration\ComponentTest::testSave
PHPUnit\Framework\MockObject\ReflectionException: Class "modSystemSetting" does not exist

/Users/julianweaver/dev/modx/modx-cli/tests/Configuration/ComponentTest.php:115

Caused by
ReflectionException: Class "modSystemSetting" does not exist

/Users/julianweaver/dev/modx/modx-cli/tests/Configuration/ComponentTest.php:115