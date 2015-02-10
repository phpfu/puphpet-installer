<?php

namespace Loadsys\Composer;

use Composer\Package\PackageInterface;
use Composer\Installer\LibraryInstaller;

class PuphpetReleaseInstaller extends LibraryInstaller {

    /**
     * {@inheritDoc}
     */
    public function getPackageBasePath(PackageInterface $package) {
        return '';
    }

    /**
     * {@inheritDoc}
     */
    public function supports($packageType) {
        return 'puphpet-release' === $packageType;
    }
}
