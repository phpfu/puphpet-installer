<?php

namespace Loadsys\Composer\Test;

use Composer\IO\IOInterface;
use Loadsys\Composer\PuphpetReleaseInstallerPlugin;
use Composer\Package\Package;
use Composer\Composer;
use Composer\Config;

class PuphpetReleaseInstallerPluginTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Package
     */
    private $package;

    /**
     * @var IOInterface
     */
    private $io;

    /**
     * @var Composer
     */
    private $composer;

    /**
     * @var PuphpetReleaseInstallerPlugin
     */
    private $plugin;

    /**
     * setUp
     *
     * @return void
     */
    public function setUp()
    {
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
    public function tearDown()
    {
        unset($this->package, $this->io, $this->composer, $this->plugin);
    }

    /**
     * All we can do is confirm that the plugin tried to register the
     * correct installer class during ::activate().
     *
     * @return void
     */
    public function testActivate()
    {
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
