<?php

namespace MODX\CLI\Tests\Command;

use MODX\CLI\Command\Install;

class InstallTestHelper extends Install
{
    public function testNormalizeVersionConstraint(?string $version): ?string
    {
        return $this->normalizeVersionConstraint($version);
    }
}
