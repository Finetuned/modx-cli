<?php

namespace MODX\CLI\Tests\Command;

use MODX\CLI\Command\Download;

class TestDownload extends Download
{
    protected $name = 'download:test';
    protected $description = 'Download test command';

    protected function download(string $url, string $target): void
    {
        file_put_contents($target, 'test');
    }
}
