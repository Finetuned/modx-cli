<?php

namespace MODX\CLI;

use MODX\CLI\Logging\Logger;
use MODX\CLI\Plugin\HookManager;
use MODX\CLI\Plugin\PluginManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Application as BaseApp;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Terminal;
use Symfony\Component\Finder\Finder;
use MODX\CLI\SSH\Handler;
use MODX\CLI\Alias\Resolver;
use MODX\CLI\Configuration\Yaml\YamlConfig;
use MODX\CLI\API\MODX_CLI;

/**
 * MODX CLI application
 */
class Application extends BaseApp
{
    /**
     * @var Configuration\Instance
     */
    public $instances;
    /**
     * @var Configuration\Extension
     */
    public $extensions;
    /**
     * @var Configuration\Component
     */
    public $components;
    /**
     * @var Configuration\ExcludedCommands
     */
    public $excludedCommands;

    /**
     * @var \MODX\Revolution\modX
     */
    public $modx;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var HookManager
     */
    protected $hookManager;

    /**
     * @var PluginManager
     */
    protected $pluginManager;

    /**
     * @var bool
     */
    protected $logoPrinted = false;

    public function __construct()
    {
        $this->instances = new Configuration\Instance();
        // Change the "context" if executing the command on a specific instance
        $this->handleForcedInstance();
        $this->extensions = new Configuration\Extension();
        $this->excludedCommands = new Configuration\ExcludedCommands();
        $this->components = new Configuration\Component($this);

        // Initialize logger
        $this->initializeLogger();

        // Initialize hook manager
        $this->hookManager = new HookManager($this->logger);

        // Initialize plugin manager
        $this->pluginManager = new PluginManager($this, $this->logger);

        $version = $this->shouldExposeVersionInOutput() ? $this->resolveVersion() : '';
        parent::__construct('MODX CLI', $version);

        // Load plugins after parent construction
        $this->pluginManager->loadPlugins();
    }

    protected function getDefaultInputDefinition(): InputDefinition
    {
        $def = parent::getDefaultInputDefinition();
        $def->addOption(
            new InputOption('--site', '-s', InputOption::VALUE_OPTIONAL, 'An instance name to execute the command to')
        );

        // Add global options from BaseCmd to make them visible in the list command
        $def->addOption(
            new InputOption('--json', null, InputOption::VALUE_NONE, 'Output results in JSON format')
        );
        $def->addOption(
            new InputOption('--ssh', null, InputOption::VALUE_REQUIRED, 'Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]')
        );

        // Logging options
        $def->addOption(
            new InputOption('--log-level', null, InputOption::VALUE_REQUIRED, 'Set log level (debug, info, notice, warning, error, critical, alert, emergency)')
        );
        $def->addOption(
            new InputOption('--log-file', null, InputOption::VALUE_REQUIRED, 'Write logs to specified file')
        );

        return $def;
    }

    /**
     * Resolve CLI version from the VERSION file.
     */
    private function resolveVersion(): string
    {
        $pharPath = \Phar::running(false);
        if ($pharPath) {
            $path = 'phar://' . $pharPath . '/VERSION';
            if (is_file($path)) {
                $version = trim((string) file_get_contents($path));
                if ($version !== '') {
                    return $version;
                }
            }
        }

        $rootPath = dirname(__DIR__) . '/VERSION';
        if (is_file($rootPath)) {
            $version = trim((string) file_get_contents($rootPath));
            if ($version !== '') {
                return $version;
            }
        }

        return '0.0.0';
    }

    private function shouldExposeVersionInOutput(): bool
    {
        $argv = $_SERVER['argv'] ?? [];

        return in_array('--json', $argv, true);
    }

    /**
     * Load/register all available commands
     *
     * @return \Symfony\Component\Console\Command\Command[]
     */
    protected function getDefaultCommands(): array
    {
        // Regular Symfony Console commands
        $commands = parent::getDefaultCommands();
        // Core commands
        $this->loadCommands($commands);
        // Extension commands
        $this->loadExtraCommands($commands);
        // Commands registered in the modX instance we are dealing with
        $this->loadComponentsCommands($commands);
        // Commands registered via the internal API
        $this->loadInternalAPICommands($commands);

        return $commands;
    }

    /**
     * Load commands registered via the internal API
     *
     * @param array $commands
     * @return void
     */
    protected function loadInternalAPICommands(array &$commands = array()): void
    {
        // Add commands from the CommandRegistry
        foreach (MODX_CLI::get_commands() as $command) {
            $commands[] = $command;
        }
    }

    /**
     * Iterate over existing commands to declare them in the application
     *
     * @param array $commands
     * @return void
     */
    protected function loadCommands(array &$commands = array()): void
    {
        $basePath = __DIR__ . '/Command';

        $finder = new Finder();
        $finder->files()
            ->in($basePath)
            ->notContains('abstract class')
            ->name('*.php');

        /** @var \Symfony\Component\Finder\SplFileInfo $file */
        foreach ($finder as $file) {
            /** @var class-string<\MODX\CLI\Command\BaseCmd> $className */
            $className = $this->getCommandClass($file);
            $commands[] = new $className();
        }
    }

    /**
     * Adds the ability to run a command on an instance without being in its folders/path
     *
     * @return void
     */
    protected function handleForcedInstance(): void
    {
        if ($this->instances->current()) {
            return;
        }
        $app = $this;
        $instance = $this->checkInstanceAsArgument($this->getDefaultInstance());

        if ($instance) {
            //echo 'Instance used is '. $instance . "\n";
            $dir = $app->instances->getConfig($instance, 'base_path');
            if ($dir) {
                chdir($dir);
            }
        }
    }

    /**
     * Get the configured default instance, if any
     *
     * @return string|null
     */
    protected function getDefaultInstance(): ?string
    {
        return $this->instances->getConfig('__default__', 'class');
    }

    /**
     * Check if any instance name has been given from the CLI
     *
     * @param string|null $instance
     *
     * @return string|null
     */
    protected function checkInstanceAsArgument(?string $instance): ?string
    {
        if (isset($_SERVER['argv'])) {
            array_filter($_SERVER['argv'], function ($value) use (&$instance) {
                if (strpos($value, '-s') === 0) {
                    $instance = str_replace('-s', '', $value);

                    return false;
                }

                return true;
            });
        }

        return $instance;
    }

    /**
     * Generate a command class name from a file
     *
     * @param \Symfony\Component\Finder\SplFileInfo $file
     *
     * @return string
     */
    protected function getCommandClass(\Symfony\Component\Finder\SplFileInfo &$file): string
    {
        $name = rtrim($file->getRelativePathname(), '.php');
        $name = str_replace('/', '\\', $name);

        return 'MODX\\CLI\\Command\\' . $name;
    }

    /**
     * Allow custom commands to be added (ie. a composer library)
     *
     * @param array $commands
     * @return void
     */
    protected function loadExtraCommands(array &$commands = array()): void
    {
        $toRemove = false;

        foreach ($this->extensions->getAll() as $class) {
            if (!class_exists($class)) {
                // Purge non existing/badly configured commands
                $this->extensions->remove($class);
                $toRemove = true;
                continue;
            }
            $commands[] = new $class();
        }
        if ($toRemove) {
            $this->extensions->save();
        }
    }

    /**
     * Load registered commands within the modX instance
     *
     * @param array $commands
     * @return void
     */
    protected function loadComponentsCommands(array &$commands = array()): void
    {
        if ($this->getMODX()) {
            foreach ($this->components->getAll() as $k => $config) {
                $loaded = $this->getExtraService($config);
                if (!$loaded || !method_exists($loaded, 'getCommands')) {
                    //echo 'Unable to load service class '.$service.' from '. $path ."\n";
                    continue;
                }

                foreach ($loaded->getCommands() as $c) {
                    $commands[] = new $c();
                }
            }
        }
    }

    /**
     * Convenient method to load a service responsible of extra commands loading
     *
     * @param array $data
     *
     * @return object|null
     */
    public function getExtraService(array $data = array()): ?object
    {
        $service = $data['service'];

        $params = array();
        if (array_key_exists('params', $data)) {
            $params = $data['params'];
        }

        return $this->getService($service, $params);
    }

    /**
     * Get the modX instance
     *
     * @return \MODX\Revolution\modX|null The modX instance if any
     */
    public function getMODX(): ?\MODX\Revolution\modX
    {
        if (null === $this->modx) {
            $coreConfig = $this->instances->getCurrentConfig('base_path');
            if ($coreConfig) {
                // A base path has been found
                $coreConfig .= 'config.core.php';
            } else {
                // Get current path
                $coreConfig = $this->getCwd() . 'config.core.php';
            }
            $coreConfig = realpath($coreConfig);
            if ($coreConfig && file_exists($coreConfig)) {
                $this->modx = $this->loadMODX($coreConfig);
            }
        }

        return $this->modx;
    }

    /**
     * Get the current working dir with trailing slash
     *
     * @return string|false
     */
    public function getCwd()
    {
        $path = getcwd();
        if ($path && substr($path, -1) !== '/') {
            $path .= '/';
        }

        return $path;
    }

    /**
     * Instantiate the MODx object from the given configuration file
     *
     * @param string|false $config The path to MODX configuration file
     *
     * @return \MODX\Revolution\modX|false False if modX was not instantiated, or a modX instance
     */
    protected function loadMODX($config)
    {
        if (!defined('MODX_CORE_PATH')) {
            if (!$config || !file_exists($config)) {
                return false;
            }

            require_once $config;
        }
        if (!defined('MODX_CORE_PATH')) {
            return false;
        }
        $loader = MODX_CORE_PATH . 'vendor/autoload.php';
        if (file_exists($loader)) {
            require_once $loader;
        }

        // MODX 3 uses namespaces
        $modxClass = '\\MODX\\Revolution\\modX';
        if (class_exists($modxClass)) {
            $modx = new $modxClass();
            return $this->initialize($modx);
        }

        return false;
    }

    /**
     * Convenient method to initialize modX
     *
     * @param \MODX\Revolution\modX $modx
     *
     * @return \MODX\Revolution\modX
     */
    protected function initialize(\MODX\Revolution\modX $modx): \MODX\Revolution\modX
    {
        $modx->initialize('mgr');
        $modx->getService('error', 'error.modError', '', array());
        //$this->modx->setLogTarget('ECHO');

        // @todo: ability to define a user (or anything else)

        return $modx;
    }

    /**
     * Try to load a service class
     *
     * @param string $name The service name
     * @param array $params Some parameters to construct the service class
     *
     * @return object|null The instantiated service class if found
     */
    public function getService(string $name = '', array $params = array()): ?object
    {
        if (empty($name)) {
            $name = $this->instances->current();
        }
        if (!$name) {
            return null;
        }
        $this->getMODX();
        $lower = strtolower($name);

        $path = $this->modx->getOption(
            "{$lower}.core_path",
            null,
            $this->modx->getOption('core_path') . "components/{$lower}/"
        );
        $classFile = "{$lower}.class.php";
        if (file_exists($path . "model/{$lower}/{$classFile}")) {
            // First check "common" path
            $path .= "model/{$lower}/";
        } elseif (file_exists($path . "services/{$classFile}")) {
            // Then check "our" path
            $path .= 'services/';
        } else {
            // Assume it's a modX base service
            $path = null;
        }

        return $this->modx->getService($lower, $name, $path, $params);
    }

    /**
     * List command classes to be "hidden"
     *
     * @return array
     */
    public function getExcludedCommands(): array
    {
        return $this->excludedCommands->getAll();
    }

    /**
     * Initialize the logger with default settings
     *
     * @return void
     */
    protected function initializeLogger(): void
    {
        // Create logger with default verbosity (normal)
        $this->logger = new Logger(Logger::VERBOSITY_NORMAL);
    }

    /**
     * Configure the logger based on input options
     *
     * @param InputInterface $input An Input instance
     * @param OutputInterface $output An Output instance
     *
     * @return void
     */
    protected function configureLogger(InputInterface $input, OutputInterface $output): void
    {
        if (!$this->logger) {
            $this->initializeLogger();
        }

        // Map Symfony Console verbosity to Logger verbosity
        $verbosity = $output->getVerbosity();
        switch ($verbosity) {
            case OutputInterface::VERBOSITY_QUIET:
                $this->logger->setVerbosity(Logger::VERBOSITY_QUIET);
                break;
            case OutputInterface::VERBOSITY_NORMAL:
                $this->logger->setVerbosity(Logger::VERBOSITY_NORMAL);
                break;
            case OutputInterface::VERBOSITY_VERBOSE:
                $this->logger->setVerbosity(Logger::VERBOSITY_VERBOSE);
                break;
            case OutputInterface::VERBOSITY_VERY_VERBOSE:
                $this->logger->setVerbosity(Logger::VERBOSITY_VERY_VERBOSE);
                break;
            case OutputInterface::VERBOSITY_DEBUG:
                $this->logger->setVerbosity(Logger::VERBOSITY_DEBUG);
                break;
        }

        // Configure log level if specified
        if ($input->hasParameterOption('--log-level')) {
            $logLevel = $input->getParameterOption('--log-level');
            $this->logger->setLogLevel($logLevel);
        }

        // Configure log file if specified
        if ($input->hasParameterOption('--log-file')) {
            $logFile = $input->getParameterOption('--log-file');
            $this->logger->setLogFile($logFile);
        }

        // Set console output for the logger
        $this->logger->setConsoleOutput(true);
        $this->logger->setOutputCallback(function (string $message) use ($output): void {
            $output->writeln($message);
        });

        // Log application start
        $this->logger->debug('MODX CLI application started');
    }

    /**
     * Get the logger instance
     *
     * @return Logger
     */
    public function getLogger(): Logger
    {
        if (!$this->logger) {
            $this->initializeLogger();
        }

        return $this->logger;
    }

    /**
     * Get the hook manager instance
     *
     * @return HookManager
     */
    public function getHookManager(): HookManager
    {
        return $this->hookManager;
    }

    /**
     * Get the plugin manager instance
     *
     * @return PluginManager
     */
    public function getPluginManager(): PluginManager
    {
        return $this->pluginManager;
    }

    /**
     * Runs the current application.
     *
     * @param InputInterface $input An Input instance
     * @param OutputInterface $output An Output instance
     *
     * @return int 0 if everything went fine, or an error code
     */
    public function doRun(InputInterface $input, OutputInterface $output): int
    {
        // Configure logger based on input options
        $this->configureLogger($input, $output);

        $command = $input->getFirstArgument();

        // Check for alias
        if ($command && strpos($command, '@') === 0) {
            return $this->runWithAlias($command, $input, $output);
        }

        // Check for SSH mode
        if ($input->hasParameterOption('--ssh')) {
            return $this->runInSSHMode($input, $output);
        }

        $usePager = $this->shouldUsePager($input, $output);
        $runOutput = $output;
        if ($usePager) {
            $runOutput = new BufferedOutput(
                $output->getVerbosity(),
                $output->isDecorated(),
                $output->getFormatter()
            );
        }

        if ($this->shouldDisplayLogo($input, $runOutput)) {
            $runOutput->writeln($this->renderLogo($this->resolveVersion()));
            $runOutput->writeln('');
            $this->logoPrinted = true;
        }

        // Normal execution
        $exitCode = parent::doRun($input, $runOutput);

        if ($usePager) {
            $this->outputWithPagerIfNeeded($runOutput->fetch(), $output);
        }

        return $exitCode;
    }

    private function shouldDisplayLogo(InputInterface $input, OutputInterface $output): bool
    {
        if ($this->logoPrinted) {
            return false;
        }

        if ($output->isQuiet()) {
            return false;
        }

        if ($input->hasParameterOption(['--quiet', '-q', '--json', '--ssh'])) {
            return false;
        }

        return true;
    }

    private function shouldUsePager(InputInterface $input, OutputInterface $output): bool
    {
        if (!$input->isInteractive()) {
            return false;
        }

        if (!function_exists('posix_isatty') || !posix_isatty(STDOUT)) {
            return false;
        }

        $command = $input->getFirstArgument();
        if ($command === null || $command === 'list' || $command === 'help') {
            return true;
        }

        return $input->hasParameterOption(['--help', '-h']);
    }

    private function outputWithPagerIfNeeded(string $content, OutputInterface $output): void
    {
        if ($content === '') {
            return;
        }

        $terminal = new Terminal();
        $height = $terminal->getHeight();
        $lineCount = substr_count($content, "\n") + 1;

        if ($height > 0 && $lineCount <= $height) {
            $output->write($content);
            return;
        }

        $pager = getenv('PAGER');
        if ($pager === false || trim($pager) === '') {
            $pager = 'less -R';
        }

        $descriptorSpec = [
            0 => ['pipe', 'r'],
            1 => STDOUT,
            2 => STDERR,
        ];

        $process = proc_open($pager, $descriptorSpec, $pipes);
        if (!is_resource($process)) {
            $output->write($content);
            return;
        }

        fwrite($pipes[0], $content);
        fclose($pipes[0]);

        $exitCode = proc_close($process);
        if ($exitCode !== 0) {
            $output->write($content);
        }
    }

    private function renderLogo(string $version): string
    {
        $versionLength = strlen($version);
        $removeLeft = intdiv($versionLength, 2);
        $removeRight = $versionLength - $removeLeft;

        $leftSpaces = 19 - $removeLeft;
        $rightSpaces = 20 - $removeRight;

        $versionLine = '│' . str_repeat(' ', $leftSpaces) . 'v' . $version . str_repeat(' ', $rightSpaces) . '│';

        return implode("\n", [
            '┌────────────────────────────────────────┐',
            '│                                        │',
            '│               MODX CLI                 │',
            $versionLine,
            '│     github.com/Finetuned/modx-cli      │',
            '│                                        │',
            '└────────────────────────────────────────┘                                                     ',
        ]);
    }

    /**
     * Run a command with an alias
     *
     * @param string $alias The alias
     * @param InputInterface $input An Input instance
     * @param OutputInterface $output An Output instance
     *
     * @return int 0 if everything went fine, or an error code
     */
    protected function runWithAlias(string $alias, InputInterface $input, OutputInterface $output): int
    {
        $config = new YamlConfig();
        $resolver = new Resolver($config);

        try {
            $aliasDef = $resolver->resolveAlias($alias);

            // Handle alias group
            if ($resolver->isAliasGroup($aliasDef)) {
                return $this->runWithAliasGroup($aliasDef, $input, $output);
            }

            // Handle SSH alias
            if (isset($aliasDef['ssh'])) {
                return $this->runWithSSH($aliasDef['ssh'], $input, $output);
            }

            throw new \Exception("Unsupported alias type.");
        } catch (\Exception $e) {
            $output->writeln("<error>" . $e->getMessage() . "</error>");
            return 1;
        }
    }

    /**
     * Run a command with an alias group
     *
     * @param array $group The alias group
     * @param InputInterface $input An Input instance
     * @param OutputInterface $output An Output instance
     *
     * @return int 0 if everything went fine, or an error code
     */
    protected function runWithAliasGroup(array $group, InputInterface $input, OutputInterface $output): int
    {
        $resolver = new Resolver(new YamlConfig());
        $exitCode = 0;

        foreach ($group as $memberAlias) {
            // Skip the first argument (the group alias) and replace with member alias
            $args = $_SERVER['argv'];
            $args[1] = $memberAlias;

            // Create new input with the member alias
            $newInput = new ArgvInput($args);
            $newInput->setInteractive($input->isInteractive());

            // Run the command with the member alias
            $code = $this->doRun($newInput, $output);
            $exitCode = max($exitCode, $code);
        }

        return $exitCode;
    }

    /**
     * Run a command in SSH mode
     *
     * @param InputInterface $input An Input instance
     * @param OutputInterface $output An Output instance
     *
     * @return int 0 if everything went fine, or an error code
     */
    protected function runInSSHMode(InputInterface $input, OutputInterface $output): int
    {
        // Parse connection string
        $sshString = $input->getParameterOption('--ssh');
        $handler = new Handler($sshString);

        // Get command and args
        $args = $_SERVER['argv'];

        // Remove script name, --ssh option and its value
        array_shift($args);
        $sshIndex = array_search('--ssh', $args);
        if ($sshIndex !== false) {
            unset($args[$sshIndex]);
            if (isset($args[$sshIndex + 1]) && strpos($args[$sshIndex + 1], '-') !== 0) {
                unset($args[$sshIndex + 1]);
            }
        } else {
            foreach ($args as $i => $arg) {
                if (strpos($arg, '--ssh=') === 0) {
                    unset($args[$i]);
                    break;
                }
            }
        }

        $args = array_values($args);
        $command = !empty($args) ? array_shift($args) : null;

        if (!$command) {
            $output->writeln("<error>No command specified.</error>");
            return 1;
        }

        return $handler->execute($command, $args);
    }

    /**
     * Run a command with SSH
     *
     * @param string $sshString The SSH connection string
     * @param InputInterface $input An Input instance
     * @param OutputInterface $output An Output instance
     *
     * @return int 0 if everything went fine, or an error code
     */
    protected function runWithSSH(string $sshString, InputInterface $input, OutputInterface $output): int
    {
        // Similar to runInSSHMode but uses the SSH string from the alias
        $handler = new Handler($sshString);

        // Get command and args (excluding the alias)
        $args = $_SERVER['argv'];
        array_shift($args); // Remove script name
        array_shift($args); // Remove alias

        $command = !empty($args) ? array_shift($args) : null;

        if (!$command) {
            $output->writeln("<error>No command specified.</error>");
            return 1;
        }

        return $handler->execute($command, $args);
    }
}
