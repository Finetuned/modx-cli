<?php

// filepath: /Users/julianweaver/dev/modx/MODX-Shell/src/Mock/PackageManager.php
<  ? php

namespace MODX\CLI\Mock;

class PackageManager
{
    public function listUpgradeablePackages()
    {
        echo "Mock: Listing upgradeable packages.\n";
        return [];
    }

    public function addProvider($name, $url)
    {
        echo "Mock: Adding package provider '$name' with URL '$url'.\n";
    }
}
