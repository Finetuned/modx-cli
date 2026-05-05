<?php

namespace MODX\CLI\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

class ReleaseFlowTest extends TestCase
{
    private string $fixtureRoot;
    private array $tempDirs = [];

    protected function setUp(): void
    {
        $this->fixtureRoot = dirname(__DIR__);
    }

    protected function tearDown(): void
    {
        foreach ($this->tempDirs as $dir) {
            $this->recursiveDelete($dir);
        }
    }

    public function testReleaseScriptCreatesReleaseCommitAndAnnotatedTag(): void
    {
        $repo = $this->createReleaseRepo(
            "## [1.2.3-beta] - 2026-04-08\n\n### Added\n- Release entry.\n"
        );

        $process = $this->runProcess(['bash', 'scripts/release.sh', 'v1.2.3-beta'], $repo);

        $this->assertSame(0, $process->getExitCode(), $process->getErrorOutput());
        $this->assertStringContainsString('Prepared release v1.2.3-beta', $process->getOutput());
        $this->assertSame("1.2.3-beta\n", (string) file_get_contents($repo . '/VERSION'));
        $this->assertSame('Release v1.2.3-beta', trim($this->git($repo, ['log', '-1', '--pretty=%s'])));
        $this->assertSame('v1.2.3-beta', trim($this->git($repo, ['tag', '--points-at', 'HEAD'])));
        $this->assertSame('tag', trim($this->git($repo, ['cat-file', '-t', 'refs/tags/v1.2.3-beta'])));
    }

    public function testReleaseScriptFailsWhenTagAlreadyExists(): void
    {
        $repo = $this->createReleaseRepo(
            "## [1.2.3-beta] - 2026-04-08\n\n### Added\n- Release entry.\n"
        );

        $this->git($repo, ['tag', '-a', 'v1.2.3-beta', '-m', 'Existing tag']);
        $process = $this->runProcess(['bash', 'scripts/release.sh', 'v1.2.3-beta'], $repo, false);

        $this->assertSame(1, $process->getExitCode());
        $this->assertStringContainsString('Tag already exists locally: v1.2.3-beta', $process->getErrorOutput());
    }

    public function testReleaseScriptFailsWhenWorktreeHasUnrelatedChanges(): void
    {
        $repo = $this->createReleaseRepo(
            "## [1.2.3-beta] - 2026-04-08\n\n### Added\n- Release entry.\n"
        );

        file_put_contents($repo . '/notes.txt', "draft\n");
        $process = $this->runProcess(['bash', 'scripts/release.sh', 'v1.2.3-beta'], $repo, false);

        $this->assertSame(1, $process->getExitCode());
        $this->assertStringContainsString(
            'Working tree has changes outside CHANGELOG.md and VERSION',
            $process->getErrorOutput()
        );
    }

    public function testReleaseScriptFailsWhenChangelogHeadingIsMissing(): void
    {
        $repo = $this->createReleaseRepo(
            "## [1.2.2-beta] - 2026-04-07\n\n### Added\n- Previous entry.\n"
        );

        $process = $this->runProcess(['bash', 'scripts/release.sh', 'v1.2.3-beta'], $repo, false);

        $this->assertSame(1, $process->getExitCode());
        $this->assertStringContainsString(
            'CHANGELOG.md must start with a release heading for 1.2.3-beta before running this script',
            $process->getErrorOutput()
        );
    }

    public function testPrePushAllowsNonTagPush(): void
    {
        $repo = $this->createHookRepo('0.9.0-beta', "## [0.9.0-beta] - 2026-02-03\n");
        $headSha = trim($this->git($repo, ['rev-parse', 'HEAD']));
        $input = "refs/heads/main {$headSha} refs/heads/main 0000000000000000000000000000000000000000\n";

        $process = $this->runProcess(['bash', '.githooks/pre-push'], $repo, true, $input);

        $this->assertSame(0, $process->getExitCode(), $process->getErrorOutput());
    }

    public function testPrePushAllowsValidTagPush(): void
    {
        $repo = $this->createHookRepo('1.2.3-beta', "## [1.2.3-beta] - 2026-04-08\n");
        $this->git($repo, ['tag', '-a', 'v1.2.3-beta', '-m', 'Release v1.2.3-beta']);
        $tagSha = trim($this->git($repo, ['rev-parse', 'refs/tags/v1.2.3-beta']));
        $input = "refs/tags/v1.2.3-beta {$tagSha} refs/tags/v1.2.3-beta 0000000000000000000000000000000000000000\n";

        $process = $this->runProcess(['bash', '.githooks/pre-push'], $repo, true, $input);

        $this->assertSame(0, $process->getExitCode(), $process->getErrorOutput());
    }

    public function testPrePushBlocksTagVersionMismatch(): void
    {
        $repo = $this->createHookRepo('0.9.0-beta', "## [1.2.3-beta] - 2026-04-08\n");
        $this->git($repo, ['tag', '-a', 'v1.2.3-beta', '-m', 'Release v1.2.3-beta']);
        $tagSha = trim($this->git($repo, ['rev-parse', 'refs/tags/v1.2.3-beta']));
        $input = "refs/tags/v1.2.3-beta {$tagSha} refs/tags/v1.2.3-beta 0000000000000000000000000000000000000000\n";

        $process = $this->runProcess(['bash', '.githooks/pre-push'], $repo, false, $input);

        $this->assertSame(1, $process->getExitCode());
        $this->assertStringContainsString(
            'Tag v1.2.3-beta does not match VERSION=0.9.0-beta in the tagged commit.',
            $process->getErrorOutput()
        );
        $this->assertStringContainsString('Run ', $process->getErrorOutput());
    }

    public function testPrePushBlocksMissingChangelogEntry(): void
    {
        $repo = $this->createHookRepo('1.2.3-beta', "## [1.2.2-beta] - 2026-04-07\n");
        $this->git($repo, ['tag', '-a', 'v1.2.3-beta', '-m', 'Release v1.2.3-beta']);
        $tagSha = trim($this->git($repo, ['rev-parse', 'refs/tags/v1.2.3-beta']));
        $input = "refs/tags/v1.2.3-beta {$tagSha} refs/tags/v1.2.3-beta 0000000000000000000000000000000000000000\n";

        $process = $this->runProcess(['bash', '.githooks/pre-push'], $repo, false, $input);

        $this->assertSame(1, $process->getExitCode());
        $this->assertStringContainsString(
            'Tag v1.2.3-beta is missing a matching CHANGELOG entry in the tagged commit.',
            $process->getErrorOutput()
        );
    }

    private function createReleaseRepo(string $releaseHeading): string
    {
        $repo = $this->createRepo();
        $this->writeFile($repo . '/VERSION', "0.9.0-beta\n");
        $this->writeFile(
            $repo . '/CHANGELOG.md',
            "# Changelog\n\nAll notable changes to this project will be documented in this file.\n\n" . $releaseHeading
        );
        $this->copyFixture('scripts/release.sh', $repo . '/scripts/release.sh');

        $this->git($repo, ['add', 'VERSION', 'CHANGELOG.md', 'scripts/release.sh']);
        $this->git($repo, ['commit', '-m', 'Initial state']);

        return $repo;
    }

    private function createHookRepo(string $version, string $releaseHeading): string
    {
        $repo = $this->createRepo();
        $this->writeFile($repo . '/VERSION', $version . "\n");
        $this->writeFile(
            $repo . '/CHANGELOG.md',
            "# Changelog\n\nAll notable changes to this project will be documented in this file.\n\n" . $releaseHeading
        );
        $this->copyFixture('.githooks/pre-push', $repo . '/.githooks/pre-push');
        $this->copyFixture('scripts/release.sh', $repo . '/scripts/release.sh');

        $this->git($repo, ['add', 'VERSION', 'CHANGELOG.md', '.githooks/pre-push', 'scripts/release.sh']);
        $this->git($repo, ['commit', '-m', 'Initial state']);

        return $repo;
    }

    private function createRepo(): string
    {
        $repo = sys_get_temp_dir() . '/modx_cli_release_' . uniqid('', true);
        $this->tempDirs[] = $repo;

        mkdir($repo, 0777, true);
        mkdir($repo . '/scripts', 0777, true);
        mkdir($repo . '/.githooks', 0777, true);

        $this->git($repo, ['init']);
        $this->git($repo, ['config', 'user.name', 'Test User']);
        $this->git($repo, ['config', 'user.email', 'test@example.com']);

        return $repo;
    }

    private function copyFixture(string $source, string $target): void
    {
        copy($this->fixtureRoot . '/' . $source, $target);
        chmod($target, 0755);
    }

    private function writeFile(string $path, string $contents): void
    {
        file_put_contents($path, $contents);
    }

    private function git(string $cwd, array $command): string
    {
        $process = $this->runProcess(array_merge(['git'], $command), $cwd);

        return $process->getOutput();
    }

    private function runProcess(array $command, string $cwd, bool $mustSucceed = true, ?string $input = null): Process
    {
        $process = new Process($command, $cwd);
        if ($input !== null) {
            $process->setInput($input);
        }

        if ($mustSucceed) {
            $process->mustRun();
        } else {
            $process->run();
        }

        return $process;
    }

    private function recursiveDelete(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $items = scandir($dir);
        if ($items === false) {
            return;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $dir . '/' . $item;
            if (is_dir($path)) {
                $this->recursiveDelete($path);
                continue;
            }

            unlink($path);
        }

        rmdir($dir);
    }
}
