<?php
namespace Loadsys\Composer\Test;

use Loadsys\Composer\PuphpetReleaseInstaller;
use Composer\Repository\RepositoryManager;
use Composer\Repository\InstalledArrayRepository;
use Composer\Package\Package;
use Composer\Package\RootPackage;
use Composer\Package\Link;
use Composer\Package\Version\VersionParser;
use Composer\Composer;
use Composer\Config;

class PuphpetReleaseInstallerTest extends \PHPUnit_Framework_TestCase {
    private $package;
    private $composer;
    private $io;

    /**
     * setUp
     *
     * @return void
     */
    public function setUp() {
        $this->package = new Package('CamelCased', '1.0', '1.0');
        $this->io = $this->getMock('Composer\IO\PackageInterface');
        $this->composer = new Composer();
        $this->composer->setConfig(new Config(false));
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
    }

    /**
     * testNothing
     *
     * @return void
     */
    public function testNothing() {
        $this->markTestIncomplete('@TODO: No tests written for PuphpetReleaseInstaller.');
    }
}
