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
        parent::setUp();
        $this->package = $this->getMockBuilder(Package::class)
            ->setConstructorArgs(array(md5(mt_rand()), '1.0.0.0', '1.0.0'))
            ->getMock();//$this->createPackageMock(); //new Package('CamelCased', '1.0', '1.0');
        $this->io = $this->getMock(IOInterface::class);
        $this->composer = new Composer();
        $this->composer->setConfig(new Config(false));
        $this->repository = $this->getMock(InstalledRepositoryInterface::class);
    }

    /**
     * Runs after each test.
     *
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();
        //unset($this->package, $this->io, $this->composer);
    }

    /**
     * @test
     * @return void
     */
    public function itShouldSupportThePuphpetReleasePackageType()
    {
        $installer = new PuphpetReleaseInstaller($this->io, $this->composer);

        static::assertTrue($installer->supports('puphpet-release'));
    }

    /**
     * @test
     * @return void
     */
    public function itShouldCallInstallCodeWhenInstalling()
    {
        /* @var PuphpetReleaseInstaller|\PHPUnit_Framework_MockObject_MockObject $installer */
        $installer = $this->getMock(PuphpetReleaseInstaller::class, [
            'initializeVendorDir',
            'getInstallPath',
            'removeBinaries',
            'installCode',
            'installBinaries'
        ], [], '', false);

        $installer->expects(static::once())->method('installCode')->with($this->package);

        $installer->install($this->repository, $this->package);
    }
}
