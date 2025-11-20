<?php

namespace MODX\CLI\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to download a MODX Revolution release
 */
abstract class Download extends BaseCmd
{
    protected $name = 'download';
    protected $description = 'Download a MODX Revolution release';

    protected function process()
    {
        // Validate version (is it an existing version ?)

        $storage = $this->argument('path');
        if (substr($storage, -1) !== '/') {
            $storage .= '/';
        }
        if (!is_dir($storage)) {
            mkdir($storage, 0777, true);
        }

        $destination = $storage . $this->buildFileName();
        // Check if already stored
        if (file_exists($destination)) {
            $this->info("Version already downloaded and available in {$storage}");
            return 0;
        }

        // Compute URL
        $url = $this->buildURL();

        if (substr($destination, -10) === 'latest.zip') {
            $this->comment('Beware, file name will be latest... think about renaming it after download to appropriate version');
        }

        // Download to storage
        $this->comment("Downloading {$url} to {$destination}");
        $this->download($url, $destination);
        $this->comment("Done");
        return 0;
    }

    /**
     * Download the given file (archive) to the given destination
     *
     * @param string $url
     * @param string $target
     *
     * @return void
     */
    protected function download($url, $target)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $data = curl_exec($ch);
        //$error = curl_error($ch);
        curl_close($ch);

        $file = fopen($target, 'w+');
        fputs($file, $data);
        fclose($file);
    }

    /**
     * Build the expected URL for the archive, to download from
     *
     * @return string
     */
    protected function buildURL()
    {
        $version = $this->argument('version');
        if ($version === 'latest') {
            return 'http://modx.com/download/latest';
        }
        $file = $this->buildFileName();

        return "http://modx.com/download/direct/{$file}";
    }

    /**
     * Compute the expected archive file name
     *
     * @return string
     */
    protected function buildFileName()
    {
        $version = $this->argument('version');
        if ($version === 'latest') {
            // TODO : find a way to retrieve the latest version number released (github tags ?)
            return 'latest.zip';
        } else {
            $version = "modx-{$version}";
        }
        if ($this->option('advanced')) {
            $version .= '-advanced';
        } elseif ($this->option('sdk')) {
            $version .= '-sdk';
        }

        return $version . '.zip';
    }

    /**
     * @inheritDoc
     */
    protected function getArguments()
    {
        return array(
            array(
                'version',
                InputArgument::OPTIONAL,
                'The version you want to download',
                'latest'
            ),
            array(
                'path',
                InputArgument::OPTIONAL,
                'The path to download the file to',
                getenv('HOME') . '/.modx/releases/'
            ),
        );
    }

    /**
     * @inheritDoc
     */
    protected function getOptions()
    {
        return array(
            array(
                'advanced',
                'a',
                InputOption::VALUE_NONE,
                'Whether or not you want an advanced release'
            ),
            array(
                'sdk',
                'k',
                InputOption::VALUE_NONE,
                'Whether or not you want the SDK version'
            ),
        );
    }
}
