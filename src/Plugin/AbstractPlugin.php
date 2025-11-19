<?php

namespace MODX\CLI\Plugin;

use MODX\CLI\Application;

/**
 * Abstract Plugin Base Class
 *
 * Provides default implementations for common plugin functionality.
 * Plugin developers can extend this class instead of implementing
 * PluginInterface directly.
 */
abstract class AbstractPlugin implements PluginInterface
{
    /**
     * Plugin configuration
     */
    protected array $config = [];

    /**
     * Whether the plugin is enabled
     */
    protected bool $enabled = true;

    /**
     * Application instance
     */
    protected ?Application $app = null;

    /**
     * {@inheritdoc}
     */
    abstract public function getName(): string;

    /**
     * {@inheritdoc}
     */
    abstract public function getVersion(): string;

    /**
     * {@inheritdoc}
     */
    abstract public function getDescription(): string;

    /**
     * {@inheritdoc}
     */
    public function getAuthor(): string
    {
        return 'Unknown';
    }

    /**
     * {@inheritdoc}
     */
    public function getMinPhpVersion(): string
    {
        return '8.0';
    }

    /**
     * {@inheritdoc}
     */
    public function getMinCliVersion(): string
    {
        return '1.0.0';
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(Application $app): void
    {
        $this->app = $app;

        // Register commands
        foreach ($this->getCommands() as $commandClass) {
            if (class_exists($commandClass)) {
                $app->add(new $commandClass());
            }
        }

        // Register hooks
        $hookManager = $app->getHookManager();
        foreach ($this->getHooks() as $hookName => $handler) {
            $hookManager->register($hookName, $handler);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * {@inheritdoc}
     */
    public function enable(): void
    {
        $this->enabled = true;
    }

    /**
     * {@inheritdoc}
     */
    public function disable(): void
    {
        $this->enabled = false;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * {@inheritdoc}
     */
    public function setConfig(array $config): void
    {
        $this->config = array_merge($this->config, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function getCommands(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getHooks(): array
    {
        return [];
    }

    /**
     * Get a configuration value
     *
     * @param string $key The configuration key
     * @param mixed $default Default value if key not found
     * @return mixed The configuration value
     */
    protected function getConfigValue(string $key, mixed $default = null): mixed
    {
        return $this->config[$key] ?? $default;
    }

    /**
     * Set a configuration value
     *
     * @param string $key The configuration key
     * @param mixed $value The value to set
     * @return void
     */
    protected function setConfigValue(string $key, mixed $value): void
    {
        $this->config[$key] = $value;
    }

    /**
     * Get the application instance
     *
     * @return Application|null
     */
    protected function getApplication(): ?Application
    {
        return $this->app;
    }
}
