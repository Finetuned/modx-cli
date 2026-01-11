<?php

namespace MODX\CLI\Plugin;

use MODX\CLI\Application;
use MODX\CLI\Configuration\Yaml\YamlConfig;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Plugin Manager
 *
 * Manages the loading, initialization, and lifecycle of plugins.
 */
class PluginManager
{
    /**
     * Loaded plugins
     *
     * @var array<string, PluginInterface>
     */
    protected array $plugins = [];

    /**
     * Plugin directories to scan
     *
     * @var array<int, string>
     */
    protected array $pluginDirectories = [];

    /**
     * Application instance
     */
    protected Application $app;

    /**
     * Logger instance
     */
    protected LoggerInterface $logger;

    /**
     * Configuration manager
     */
    protected YamlConfig $config;

    /**
     * Plugin configuration cache
     *
     * @var array<string, array>
     */
    protected array $pluginConfigs = [];

    /**
     * Constructor
     *
     * @param Application $app Application instance
     * @param LoggerInterface|null $logger Optional logger instance
     */
    public function __construct(Application $app, ?LoggerInterface $logger = null)
    {
        $this->app = $app;
        $this->logger = $logger ?? new NullLogger();
        $this->config = new YamlConfig();

        // Set default plugin directories
        $this->addPluginDirectory(__DIR__ . '/../../plugins');
        $this->addPluginDirectory(getcwd() . '/.modx-cli/plugins');

        // Load plugin configurations
        $this->loadPluginConfigs();
    }

    /**
     * Add a plugin directory to scan
     *
     * @param string $directory The directory path
     * @return void
     */
    public function addPluginDirectory(string $directory): void
    {
        $realPath = realpath($directory);
        if ($realPath && is_dir($realPath) && !in_array($realPath, $this->pluginDirectories, true)) {
            $this->pluginDirectories[] = $realPath;
            $this->logger->debug('Plugin directory added: {dir}', ['dir' => $realPath]);
        }
    }

    /**
     * Discover and load all plugins
     *
     * @return void
     */
    public function loadPlugins(): void
    {
        $this->logger->info('Starting plugin discovery');

        foreach ($this->pluginDirectories as $directory) {
            $this->discoverPluginsInDirectory($directory);
        }

        // Initialize all loaded plugins
        foreach ($this->plugins as $plugin) {
            $this->initializePlugin($plugin);
        }

        $this->logger->info('Plugin discovery completed', [
            'total_plugins' => count($this->plugins),
            'enabled_plugins' => count(array_filter($this->plugins, fn($p) => $p->isEnabled()))
        ]);
    }

    /**
     * Discover plugins in a directory
     *
     * @param string $directory The directory to scan
     * @return void
     */
    protected function discoverPluginsInDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }

        $this->logger->debug('Scanning plugin directory: {dir}', ['dir' => $directory]);

        // Look for plugin.php files
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->getFilename() === 'Plugin.php') {
                $this->loadPluginFromFile($file->getPathname());
            }
        }
    }

    /**
     * Load a plugin from a file
     *
     * @param string $filePath The plugin file path
     * @return void
     */
    protected function loadPluginFromFile(string $filePath): void
    {
        try {
            // Include the plugin file
            require_once $filePath;

            // Extract class name from file
            $className = $this->extractClassNameFromFile($filePath);

            if (!$className || !class_exists($className)) {
                $this->logger->warning('Plugin class not found in file: {file}', ['file' => $filePath]);
                return;
            }

            // Instantiate the plugin
            $plugin = new $className();

            if (!($plugin instanceof PluginInterface)) {
                $this->logger->warning('Plugin does not implement PluginInterface: {class}', ['class' => $className]);
                return;
            }

            // Check version requirements
            if (!$this->checkRequirements($plugin)) {
                return;
            }

            // Apply configuration
            if (isset($this->pluginConfigs[$plugin->getName()])) {
                $plugin->setConfig($this->pluginConfigs[$plugin->getName()]);
            }

            // Register the plugin
            $this->registerPlugin($plugin);

            $this->logger->info('Plugin loaded: {name} v{version}', [
                'name' => $plugin->getName(),
                'version' => $plugin->getVersion(),
                'enabled' => $plugin->isEnabled()
            ]);

        } catch (\Throwable $e) {
            $this->logger->error('Failed to load plugin from file: {file}', [
                'file' => $filePath,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Extract class name from PHP file
     *
     * @param string $filePath The file path
     * @return string|null The fully qualified class name
     */
    protected function extractClassNameFromFile(string $filePath): ?string
    {
        $content = file_get_contents($filePath);
        if ($content === false) {
            return null;
        }

        // Extract namespace
        $namespace = '';
        if (preg_match('/namespace\s+([^;]+);/', $content, $matches)) {
            $namespace = trim($matches[1]);
        }

        // Extract class name
        if (preg_match('/class\s+(\w+)/', $content, $matches)) {
            $className = trim($matches[1]);
            return $namespace ? $namespace . '\\' . $className : $className;
        }

        return null;
    }

    /**
     * Check plugin requirements
     *
     * @param PluginInterface $plugin The plugin instance
     * @return bool True if requirements are met
     */
    protected function checkRequirements(PluginInterface $plugin): bool
    {
        // Check PHP version
        if (version_compare(PHP_VERSION, $plugin->getMinPhpVersion(), '<')) {
            $this->logger->warning('Plugin {name} requires PHP {required}, current: {current}', [
                'name' => $plugin->getName(),
                'required' => $plugin->getMinPhpVersion(),
                'current' => PHP_VERSION
            ]);
            return false;
        }

        // Check CLI version (you may want to define a constant for CLI version)
        $cliVersion = $this->app->getVersion() ?: '0.0.0';
        if (version_compare($cliVersion, $plugin->getMinCliVersion(), '<')) {
            $this->logger->warning('Plugin {name} requires CLI {required}, current: {current}', [
                'name' => $plugin->getName(),
                'required' => $plugin->getMinCliVersion(),
                'current' => $cliVersion
            ]);
            return false;
        }

        return true;
    }

    /**
     * Register a plugin
     *
     * @param PluginInterface $plugin The plugin instance
     * @return void
     */
    public function registerPlugin(PluginInterface $plugin): void
    {
        $name = $plugin->getName();

        if (isset($this->plugins[$name])) {
            $this->logger->warning('Plugin already registered: {name}', ['name' => $name]);
            return;
        }

        $this->plugins[$name] = $plugin;
        $this->logger->debug('Plugin registered: {name}', ['name' => $name]);
    }

    /**
     * Initialize a plugin
     *
     * @param PluginInterface $plugin The plugin instance
     * @return void
     */
    protected function initializePlugin(PluginInterface $plugin): void
    {
        if (!$plugin->isEnabled()) {
            $this->logger->debug('Plugin disabled, skipping initialization: {name}', [
                'name' => $plugin->getName()
            ]);
            return;
        }

        try {
            $this->logger->debug('Initializing plugin: {name}', ['name' => $plugin->getName()]);
            $plugin->initialize($this->app);
            $this->logger->info('Plugin initialized: {name}', ['name' => $plugin->getName()]);
        } catch (\Throwable $e) {
            $this->logger->error('Failed to initialize plugin: {name}', [
                'name' => $plugin->getName(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Get a plugin by name
     *
     * @param string $name The plugin name
     * @return PluginInterface|null The plugin instance or null if not found
     */
    public function getPlugin(string $name): ?PluginInterface
    {
        return $this->plugins[$name] ?? null;
    }

    /**
     * Get all loaded plugins
     *
     * @return array<string, PluginInterface> Array of plugins keyed by name
     */
    public function getPlugins(): array
    {
        return $this->plugins;
    }

    /**
     * Get enabled plugins only
     *
     * @return array<string, PluginInterface> Array of enabled plugins
     */
    public function getEnabledPlugins(): array
    {
        return array_filter($this->plugins, fn($plugin) => $plugin->isEnabled());
    }

    /**
     * Enable a plugin
     *
     * @param string $name The plugin name
     * @return bool True if successful
     */
    public function enablePlugin(string $name): bool
    {
        $plugin = $this->getPlugin($name);
        if (!$plugin) {
            $this->logger->warning('Cannot enable plugin, not found: {name}', ['name' => $name]);
            return false;
        }

        $plugin->enable();
        $this->savePluginConfig($name, ['enabled' => true]);
        $this->logger->info('Plugin enabled: {name}', ['name' => $name]);

        return true;
    }

    /**
     * Disable a plugin
     *
     * @param string $name The plugin name
     * @return bool True if successful
     */
    public function disablePlugin(string $name): bool
    {
        $plugin = $this->getPlugin($name);
        if (!$plugin) {
            $this->logger->warning('Cannot disable plugin, not found: {name}', ['name' => $name]);
            return false;
        }

        $plugin->disable();
        $this->savePluginConfig($name, ['enabled' => false]);
        $this->logger->info('Plugin disabled: {name}', ['name' => $name]);

        return true;
    }

    /**
     * Load plugin configurations from YAML
     *
     * @return void
     */
    protected function loadPluginConfigs(): void
    {
        $configFile = getcwd() . '/.modx-cli/plugins.yaml';
        if (!file_exists($configFile)) {
            return;
        }

        try {
            $this->pluginConfigs = $this->config->load($configFile) ?? [];
            $this->logger->debug('Plugin configurations loaded from: {file}', ['file' => $configFile]);
        } catch (\Throwable $e) {
            $this->logger->error('Failed to load plugin configurations', [
                'file' => $configFile,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Save plugin configuration
     *
     * @param string $pluginName The plugin name
     * @param array $config Configuration to save
     * @return void
     */
    protected function savePluginConfig(string $pluginName, array $config): void
    {
        $this->pluginConfigs[$pluginName] = array_merge(
            $this->pluginConfigs[$pluginName] ?? [],
            $config
        );

        $configFile = getcwd() . '/.modx-cli/plugins.yaml';
        $configDir = dirname($configFile);

        if (!is_dir($configDir)) {
            mkdir($configDir, 0755, true);
        }

        try {
            $this->config->save($configFile, $this->pluginConfigs);
            $this->logger->debug('Plugin configuration saved: {plugin}', ['plugin' => $pluginName]);
        } catch (\Throwable $e) {
            $this->logger->error('Failed to save plugin configuration', [
                'plugin' => $pluginName,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Set logger instance
     *
     * @param LoggerInterface $logger
     * @return void
     */
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }
}
