<?php

namespace MODX\CLI\Mock;

class PackageManager
{
    /**
     * List upgradeable packages.
     *
     * @return array
     */
    public function listUpgradeablePackages(): array
    {
        echo "Mock: Listing upgradeable packages.\n";
        return [];
    }

    /**
     * Add a package provider.
     *
     * @param string $name The provider name.
     * @param string $url  The provider URL.
     * @return void
     */
    public function addProvider(string $name, string $url): void
    {
        echo "Mock: Adding package provider '$name' with URL '$url'.\n";
    }
}
