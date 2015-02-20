<?php
namespace Loadsys\Composer\Test;

use Loadsys\Composer\PuphpetReleaseInstallerPlugin;
use Composer\Repository\RepositoryManager;
use Composer\Repository\InstalledArrayRepository;
use Composer\Package\Package;
use Composer\Package\RootPackage;
use Composer\Package\Link;
use Composer\Package\Version\VersionParser;
use Composer\Composer;
use Composer\Config;

class PuphpetReleaseInstallerPluginTest extends \PHPUnit_Framework_TestCase {
    private $package;
    private $io;
    private $composer;
    private $plugin;

    /**
     * setUp
     *
     * @return void
     */
    public function setUp() {
        $this->package = new Package('CamelCased', '1.0', '1.0');
        $this->io = $this->getMock('Composer\IO\IOInterface');
        $this->composer = new Composer();
        $this->plugin = new PuphpetReleaseInstallerPlugin();
    }

    /**
     * tearDown
     *
     * @return void
     */
    public function tearDown() {
        unset($this->package);
        unset($this->io);
        unset($this->composer);
        unset($this->plugin);
    }

    /**
     * All we can do is confirm that the plugin tried to register the
     * correct installer class during ::activate().
     *
     * @return void
     */
    public function testActivate() {
        $this->composer = $this->getMock('Composer\Composer', [
        	'getInstallationManager',
        	'addInstaller'
        ]);
        $this->composer->setConfig(new Config(false));

        $this->composer->expects($this->once())
            ->method('getInstallationManager')
            ->will($this->returnSelf());
        $this->composer->expects($this->once())
            ->method('addInstaller')
            ->with($this->isInstanceOf('Loadsys\Composer\PuphpetReleaseInstaller'));

        $this->plugin->activate($this->composer, $this->io);
    }
}
