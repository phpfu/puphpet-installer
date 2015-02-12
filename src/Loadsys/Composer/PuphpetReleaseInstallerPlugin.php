<?php

namespace Loadsys\Composer;

// Needed for PluginInterface:
use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;

/**
 * Plugin entry point.
 *
 */
class PuphpetReleaseInstallerPlugin implements PluginInterface {

	/**
	 * Activate the plugin (called from {@see \Composer\Plugin\PluginManager})
	 *
	 * @param \Composer\Composer $composer The active instance of the composer base class.
	 * @param \Composer\IO\IOInterface $io The I/O instance.
	 * @return void
	 */
    public function activate(Composer $composer, IOInterface $io) {
        // Register the custom installer.
        $installer = new PuphpetReleaseInstaller($io, $composer);
        $composer->getInstallationManager()->addInstaller($installer);
    }
}
