<?php

// Minimal Composer stubs for static analysis when composer/composer is not installed.

namespace Composer\IO {
    interface IOInterface
    {
        public function write($messages, $newline = true, $verbosity = 0);
    }

    class NullIO implements IOInterface
    {
        public function write($messages, $newline = true, $verbosity = 0)
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
        public function getIO(): IOInterface
        {
            return new NullIO();
        }
    }
}
