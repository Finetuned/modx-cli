<?php

namespace MODX\CLI\Command\Package\Upgrade;

use MODX\CLI\API\MODX_CLI;

/**
 * Bootstrap file to register package upgrade commands with the internal API
 */
class UpgradeBootstrap
{
    /**
     * Register all package upgrade commands
     */
    public static function registerCommands()
    {
        // Register the package:upgrade:list command
        MODX_CLI::add_command('package:upgrade:list', ListLocal::class, [
            'shortdesc' => 'List downloaded package upgrades ready for installation',
            'longdesc' => 'This command scans the core/packages directory for downloaded package upgrades and compares them with installed packages to show which upgrades are available for installation.',
        ]);
    }
}

// Auto-register commands when this file is loaded
UpgradeBootstrap::registerCommands();
