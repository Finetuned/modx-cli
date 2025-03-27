<?php

namespace MODX\CLI\Command;

use Melting\MODX\Installer\Installer;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Install MODX in the current folder
 */
class Install extends BaseCmd
{
    protected $name = 'install';
    protected $description = 'Install MODX here';

    protected function process()
    {
        if (!$this->getApplication()->instances->getDefaultInstance() && $this->getMODX()) {
            return $this->error('Seems like MODX install already installed here...');
        }

        $this->comment('This is experimental, be warned...');

        // Path to Revo source/repository
        $source = $this->argument('source');
        // First, handle only version number given, and already downloaded release into our home folder
//        $storage = getenv('HOME') . '/.modx/releases/';
//        if (file_exists("{$storage}{$source}")) {
//
//        }
        // Then assume we are using a folder or zip archive
        if (substr($source, 0, 1) !== '/' && !$this->getApplication()->instances->getDefaultInstance()) {
            // A relative path might have been given, let's append current working dir
            $source = getcwd() . '/' . $source;
        }
        $config = $this->argument('config');
        if ($config) {
            if (!file_exists($config)) {
                return $this->error("{$config} does not appear to be a valid configuration file");
            }
            $config = $this->readConfigFromfile($config);
        } else {
            // Interactive config builder
            $config = $this->buildConfigInteractively();
        }

        if (empty($config)) {
            return $this->error('The configuration array is empty, stopping here');
        }

        // Handle folders to move in custom locations
        $folders = $this->getCustomFolders($config, $source);

        // Allow customization ?
        $configKey = 'config';

        $installer = new Installer($source, $folders);
        $modx = $installer->install($config, $configKey);
        if ($modx instanceof \modX) {
            return $this->info('MODX Revolution successfully installed');
        }

        return $this->line("Seems like an error occurred while trying to install MODX Revolution : <error>{$modx}</error>");
    }

    /**
     * Read the configuration options from the given file
     *
     * @param string $path - The absolute path to the configuration file
     *
     * @return array
     */
    protected function readConfigFromFile($path)
    {
        // @TODO
        $config = array();

        return $config;
    }

    /**
     * Ask the user for the configuration details, interactively
     *
     * @return array
     */
    protected function buildConfigInteractively()
    {
        // @TODO
        // DB mysql|sqlsrv
        $dbType = 'mysql';
        // DB host (default to localhost)
        $dbHost = 'localhost';
        // DB name (default to current folder basename)
        $db = 'db_name';
        // DB user
        $dbUser = 'user';
        // DB pass
        $dbPass = 'password';
        // DB connexion charset (utf8)
        // DB charset (utf8)
        // DB collation (utf8_general_ci)
        // DB tables prefix (modx_)

        // Admin user
        $admin = 'admin';
        // Admin pass
        $adminPass = 'password';
        // Admin email
        $adminEmail = 'admin@domain.tld';

        // https port (443)
        // http host (localhost)
        $httpHost = 'localhost';

        return array(
            'database_type' => $dbType,
            'database_server' => $dbHost,
            'database' => $db,
            'database_user' => $dbUser,
            'database_password' => $dbPass,
            'database_connection_charset' => 'utf8',
            'database_collation' => 'utf8_general_ci',
            'table_prefix' => '',

            'cmsadmin' => $admin,
            'cmspassword' => $adminPass,
            'cmsadminemail' => $adminEmail,
            'language' => 'en',
            'cache_disabled' => '0',

            'https_port' => 443,
            'http_host' => $httpHost,

            'core_path' => getcwd() . '/core/',
            'context_mgr_path' => getcwd() . '/manager/',
            'assets_path' => getcwd() . '/assets/',
            'assets_url' => '/assets/',
            'context_mgr_url' => '/',
            'context_connectors_path' => getcwd() . '/connectors/',
            'context_connectors_url' => '/connectors/',
            'context_web_path' => getcwd() . '/',
            'context_web_url' => '/',

            'inplace' => '1',
            'unpacked' => '0',
            'remove_setup_directory' => '1',
        );
    }

    /**
     * Read the given configuration array to find if some folders have been moved in other places than their default
     *
     * @param array $config - The configuration data to install Revo
     * @param string $source - The source folder to install Revo from (either a git repository or extracted official archive)
     *
     * @return array - An array of custom folders, if any (else an empty array)
     */
    protected function getCustomFolders(array $config, $source)
    {
        // Folders mapping
        $toCheck = array(
            // folder name in source => config key
            'manager' => 'context_mgr_path',
            'core' => 'core_path',
            'assets' => 'assets_path',
            'connectors' => 'context_connectors_path',
        );
        // Handle folders to move in custom locations
        $folders = array();
        foreach ($toCheck as $folder => $configKey) {
            $inSource = realpath("{$source}{$folder}");
            if (!file_exists($inSource) || !isset($configKey[$configKey])) {
                // Source does not have the folder or not configuration option
                continue;
            }
            // Now check if folder value in the config is different than the source
            $inConfig = realpath($config[$configKey]);
            if ($inConfig !== $inSource) {
                $folders[$folder] = $inConfig;
            }
        }

        return $folders;
    }

    /**
     * @inheritDoc
     */
    protected function getArguments()
    {
        return array(
            array(
                'source',
                InputArgument::REQUIRED,
                'The absolute path to the MODX Revolution source, either a git repository folder or a zip file.'
            ),
            array(
                'config',
                InputArgument::OPTIONAL,
                'The absolute path to a configuration file.'
            ),
        );
    }
}
