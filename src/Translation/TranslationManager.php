<?php

declare(strict_types=1);

namespace MODX\CLI\Translation;

use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\Loader\YamlFileLoader;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Translation Manager - Singleton service for managing translations
 * 
 * Handles locale detection, translator initialization, and translation management
 * for the MODX CLI application.
 */
class TranslationManager
{
    private static ?TranslationManager $instance = null;
    
    private ?Translator $translator = null;
    
    private string $locale = 'en';
    
    private string $fallbackLocale = 'en';
    
    private array $translationPaths = [];
    
    /**
     * Get singleton instance
     *
     * @return TranslationManager
     */
    public static function getInstance(): TranslationManager
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }
    
    /**
     * Private constructor to enforce singleton pattern
     */
    private function __construct()
    {
        // Initialize with default translation path
        $this->addTranslationPath(__DIR__ . '/../../translations');
        
        // Detect and set locale
        $this->locale = $this->detectLocale();
    }
    
    /**
     * Get the Symfony Translator instance
     *
     * @return Translator
     */
    public function getTranslator(): Translator
    {
        if ($this->translator === null) {
            $this->translator = $this->initializeTranslator();
        }
        
        return $this->translator;
    }
    
    /**
     * Detect the user's preferred locale
     * 
     * Priority order:
     * 1. CLI option (--locale)
     * 2. MODX_CLI_LOCALE environment variable
     * 3. LANG or LC_ALL environment variables
     * 4. System locale via PHP
     * 5. Fallback to 'en'
     *
     * @param array $cliOptions Optional CLI options array
     * @return string The detected locale code
     */
    public function detectLocale(array $cliOptions = []): string
    {
        // 1. Check CLI option
        if (isset($cliOptions['locale']) && !empty($cliOptions['locale'])) {
            return $this->normalizeLocale($cliOptions['locale']);
        }
        
        // 2. Check MODX_CLI_LOCALE environment variable
        $envLocale = getenv('MODX_CLI_LOCALE');
        if ($envLocale !== false && !empty($envLocale)) {
            return $this->normalizeLocale($envLocale);
        }
        
        // 3. Check LANG or LC_ALL environment variables
        $langEnv = getenv('LANG') ?: getenv('LC_ALL');
        if ($langEnv !== false && !empty($langEnv)) {
            return $this->normalizeLocale($langEnv);
        }
        
        // 4. Try PHP's locale detection
        if (class_exists('\Locale')) {
            $systemLocale = \Locale::getDefault();
            if (!empty($systemLocale)) {
                return $this->normalizeLocale($systemLocale);
            }
        }
        
        // 5. Fallback to English
        return 'en';
    }
    
    /**
     * Normalize a locale string to just the language code
     * 
     * Examples:
     * - en_US.UTF-8 -> en
     * - fr_FR -> fr
     * - es-MX -> es
     *
     * @param string $locale The locale string to normalize
     * @return string The normalized locale code
     */
    private function normalizeLocale(string $locale): string
    {
        // Remove encoding (.UTF-8, etc.)
        $locale = preg_replace('/\.[^.]*$/', '', $locale);
        
        // Extract language code (before _ or -)
        if (preg_match('/^([a-z]{2})[_-]/i', $locale, $matches)) {
            return strtolower($matches[1]);
        }
        
        // If it's already just a language code, return it
        if (preg_match('/^[a-z]{2}$/i', $locale)) {
            return strtolower($locale);
        }
        
        // Fallback
        return 'en';
    }
    
    /**
     * Set the current locale
     *
     * @param string $locale The locale code to set
     * @return void
     */
    public function setLocale(string $locale): void
    {
        $this->locale = $this->normalizeLocale($locale);
        
        if ($this->translator !== null) {
            $this->translator->setLocale($this->locale);
        }
    }
    
    /**
     * Get the current locale
     *
     * @return string The current locale code
     */
    public function getLocale(): string
    {
        return $this->locale;
    }
    
    /**
     * Get the fallback locale
     *
     * @return string The fallback locale code
     */
    public function getFallbackLocale(): string
    {
        return $this->fallbackLocale;
    }
    
    /**
     * Add a translation directory path
     *
     * @param string $path Absolute path to translations directory
     * @return void
     */
    public function addTranslationPath(string $path): void
    {
        if (!in_array($path, $this->translationPaths, true)) {
            $this->translationPaths[] = $path;
        }
    }
    
    /**
     * Get available locales based on translation directories
     *
     * @return array Array of available locale codes
     */
    public function getAvailableLocales(): array
    {
        $locales = [];
        
        foreach ($this->translationPaths as $path) {
            if (!is_dir($path)) {
                continue;
            }
            
            $dirs = scandir($path);
            if ($dirs === false) {
                continue;
            }
            
            foreach ($dirs as $dir) {
                if ($dir === '.' || $dir === '..') {
                    continue;
                }
                
                $fullPath = $path . DIRECTORY_SEPARATOR . $dir;
                if (is_dir($fullPath) && preg_match('/^[a-z]{2}$/', $dir)) {
                    $locales[] = $dir;
                }
            }
        }
        
        return array_unique($locales);
    }
    
    /**
     * Check if a locale is available
     *
     * @param string $locale The locale code to check
     * @return bool True if the locale is available
     */
    public function isLocaleAvailable(string $locale): bool
    {
        return in_array($locale, $this->getAvailableLocales(), true);
    }
    
    /**
     * Initialize the Symfony Translator
     *
     * @return Translator
     */
    private function initializeTranslator(): Translator
    {
        $translator = new Translator($this->locale);
        $translator->setFallbackLocales([$this->fallbackLocale]);
        
        // Add YAML file loader
        $translator->addLoader('yaml', new YamlFileLoader());
        
        // Load translation files for all available locales
        $this->loadTranslationFiles($translator);
        
        return $translator;
    }
    
    /**
     * Load translation files from configured paths
     *
     * @param Translator $translator The translator instance
     * @return void
     */
    private function loadTranslationFiles(Translator $translator): void
    {
        $domains = ['messages', 'errors', 'commands', 'validation', 'success'];
        
        foreach ($this->translationPaths as $basePath) {
            if (!is_dir($basePath)) {
                continue;
            }
            
            foreach ($this->getAvailableLocales() as $locale) {
                $localePath = $basePath . DIRECTORY_SEPARATOR . $locale;
                
                if (!is_dir($localePath)) {
                    continue;
                }
                
                foreach ($domains as $domain) {
                    $file = $localePath . DIRECTORY_SEPARATOR . $domain . '.yaml';
                    
                    if (file_exists($file)) {
                        $translator->addResource('yaml', $file, $locale, $domain);
                    }
                }
            }
        }
    }
    
    /**
     * Reset the singleton instance (primarily for testing)
     *
     * @return void
     */
    public static function reset(): void
    {
        self::$instance = null;
    }
}