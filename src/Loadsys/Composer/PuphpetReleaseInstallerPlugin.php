<?php

namespace Loadsys\Composer;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;

class PuphpetReleaseInstallerPlugin implements PluginInterface {
    public function activate(Composer $composer, IOInterface $io) {
        $installer = new PuphpetReleaseInstaller($io, $composer);
        $composer->getInstallationManager()->addInstaller($installer);
    }
}
