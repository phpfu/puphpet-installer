<?php

namespace Loadsys\Composer\Test;

use Composer\IO\IOInterface;
use Composer\Package\Package;
use Composer\Composer;
use Composer\Config;
use Composer\Repository\InstalledRepositoryInterface;
use Loadsys\Composer\PuphpetReleaseInstaller;

class PuphpetReleaseInstallerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Package
     */
    private $package;

    /**
     * @var Composer
     */
    private $composer;

    /**
     * @var IOInterface
     */
    private $io;

    /**
     * @var InstalledRepositoryInterface
     */
    private $repository;

    /**
     * Runs before each test.
     *
     * @return void
     */
    public function setUp()
    {
        $this->package = new Package('CamelCased', '1.0', '1.0');
        $this->io = $this->getMock('Composer\IO\IOInterface');
        $this->composer = new Composer();
        $this->composer->setConfig(new Config(false));
    }

    /**
     * tearDown
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->package, $this->io, $this->composer);
    }

    /**
     * testNothing
     *
     * @return void
     */
    public function testNothing()
    {
        $this->markTestIncomplete('@TODO: No tests written for PuphpetReleaseInstaller.');
    }
}
