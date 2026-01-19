<?php

// phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// Minimal Composer stubs for static analysis when composer/composer is not installed.

namespace Composer\IO {
    interface IOInterface
    {
        /**
         * Write output.
         *
         * @param string|array $messages  The messages to write.
         * @param boolean      $newline   Whether to append a newline.
         * @param integer      $verbosity The verbosity level.
         * @return void
         */
        public function write($messages, bool $newline = true, int $verbosity = 0): void;
    }

    class NullIO implements IOInterface
    {
        /**
         * Write output.
         *
         * @param string|array $messages  The messages to write.
         * @param boolean      $newline   Whether to append a newline.
         * @param integer      $verbosity The verbosity level.
         * @return void
         */
        public function write($messages, bool $newline = true, int $verbosity = 0): void
        {
            // no-op for stub
        }
    }
}

namespace Composer\Script {
    use Composer\IO\IOInterface;
    use Composer\IO\NullIO;

    class Event
    {
        /**
         * Get the IO instance.
         *
         * @return IOInterface
         */
        public function getIO(): IOInterface
        {
            return new NullIO();
        }
    }
}
