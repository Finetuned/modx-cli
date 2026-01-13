<?php

namespace MODX\CLI;

use Composer\Script\Event;
use MODX\CLI\Application;
use MODX\CLI\Configuration\ConfigurationInterface;

/**
 * A simple class to implement to register third party commands
 */
abstract class CommandRegistrar
{
    /**
     * @var \Composer\IO\IOInterface|null
     */
    public static $io;
    /**
     * @var \ReflectionClass|null
     */
    protected static $reflection = null;
    protected static $unregistered = array();
    protected static $commandsFolder = 'Command';

    /**
     * Process the command registration
     *
     * @param Event $event
     */
    public static function run(Event $event)
    {
        self::setIO($event->getIO());

        /** @var Application $app */
        $app = new Application();
        $config = $app->extensions;
        self::writeIO('Editing extra commands for <info>' . self::getNS() . '</info>...');

        // First, un-register "deprecated" commands, if any
        static::unRegister($config);

        // Iterate the Command folder, looking for command classes
        self::writeIO("\n" . '  - looking for commands to register...');
        /** @var \Symfony\Component\Finder\SplFileInfo $file */
        foreach (static::listCommands() as $file) {
            $className = static::getCommandClass($file);
            if (!in_array($className, self::$unregistered)) {
                $config->set($className);
                self::writeIO("    Added   <info>{$className}</info>");
            }
        }
        $config->save();

        self::writeIO(' ');
        self::$reflection = null;
    }

    /**
     * Un-register previous commands, that are now deprecated/removed
     *
     * @param ConfigurationInterface $config Existing commands
     */
    public static function unRegister($config)
    {
        $deprecated = static::getRootPath() . '/deprecated.php';
        if (file_exists($deprecated)) {
            self::writeIO("\n" . '  - looking for commands to remove...');
            $deprecated = include $deprecated;
            foreach ($deprecated as $class) {
                self::$unregistered[] = $class;
                $config->remove($class);
                self::writeIO("    Removed <comment>{$class}</comment>");
            }
        }
    }

    /**
     * Inject IO for testing or custom runners.
     */
    public static function setIO($io): void
    {
        self::$io = $io;
    }

    /**
     * Write output only when IO is available.
     */
    protected static function writeIO($message): void
    {
        if (self::$io) {
            self::$io->write($message);
        }
    }

    /**
     * List instantiable commands
     *
     * @return \Symfony\Component\Finder\Finder
     */
    public static function listCommands()
    {
        $basePath = static::getRootPath() . '/' . static::$commandsFolder;

        $finder = new \Symfony\Component\Finder\Finder();
        $finder->files()
            ->in($basePath)
            ->notContains('abstract class')
            ->name('*.php');

        return $finder;
    }

    /**
     * Convert a file name to a class name
     *
     * @param \Symfony\Component\Finder\SplFileInfo $file
     *
     * @return string
     */
    public static function getCommandClass(\Symfony\Component\Finder\SplFileInfo &$file)
    {
        $name = rtrim($file->getRelativePathname(), '.php');
        $name = str_replace('/', '\\', $name);

        return static::getNS() . '\\' . static::$commandsFolder . '\\' . $name;
    }

    /**
     * Get the namespace of the called sub class
     *
     * @return string
     */
    protected static function getNS()
    {
        return static::getReflection()->getNamespaceName();
    }

    /**
     * Get a reflection of the called sub class
     *
     * @return \ReflectionClass
     */
    protected static function getReflection()
    {
        if (!self::$reflection) {
            self::$reflection = new \ReflectionClass(get_called_class());
        }

        return self::$reflection;
    }

    /**
     * Get the path of the called sub class
     *
     * @return string
     */
    protected static function getRootPath()
    {
        return dirname(static::getReflection()->getFileName());
    }
}
