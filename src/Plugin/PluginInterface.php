<?php

namespace MODX\CLI\Plugin;

use MODX\CLI\Application;

/**
 * Plugin Interface
 *
 * All plugins must implement this interface to be recognized and loaded
 * by the MODX CLI plugin system.
 */
interface PluginInterface
{
    /**
     * Get the plugin name
     *
     * @return string The unique plugin identifier
     */
    public function getName(): string;

    /**
     * Get the plugin version
     *
     * @return string The plugin version (semver format recommended)
     */
    public function getVersion(): string;

    /**
     * Get the plugin description
     *
     * @return string A brief description of what the plugin does
     */
    public function getDescription(): string;

    /**
     * Get the plugin author
     *
     * @return string The plugin author name
     */
    public function getAuthor(): string;

    /**
     * Get minimum required PHP version
     *
     * @return string Minimum PHP version (e.g., '8.0')
     */
    public function getMinPhpVersion(): string;

    /**
     * Get minimum required MODX CLI version
     *
     * @return string Minimum CLI version (e.g., '1.0.0')
     */
    public function getMinCliVersion(): string;

    /**
     * Initialize the plugin
     *
     * Called when the plugin is loaded. This is where you should:
     * - Register hooks
     * - Register commands
     * - Initialize services
     *
     * @param Application $app The application instance.
     * @return void
     */
    public function initialize(Application $app): void;

    /**
     * Check if the plugin is enabled
     *
     * @return boolean True if enabled, false otherwise
     */
    public function isEnabled(): bool;

    /**
     * Enable the plugin
     *
     * @return void
     */
    public function enable(): void;

    /**
     * Disable the plugin
     *
     * @return void
     */
    public function disable(): void;

    /**
     * Get plugin configuration
     *
     * Returns an array of configuration values for the plugin.
     * These can be overridden by user configuration.
     *
     * @return array<string, mixed> Plugin configuration
     */
    public function getConfig(): array;

    /**
     * Set plugin configuration
     *
     * @param array<string, mixed> $config Configuration values.
     * @return void
     */
    public function setConfig(array $config): void;

    /**
     * Get the commands provided by this plugin
     *
     * @return array<string> Array of fully qualified command class names
     */
    public function getCommands(): array;

    /**
     * Get the hooks registered by this plugin
     *
     * Returns an array mapping hook names to callable handlers.
     * Hook names follow the pattern: 'event.action' (e.g., 'command.before', 'command.after')
     *
     * @return array<string, callable> Hook registrations
     */
    public function getHooks(): array;
}
