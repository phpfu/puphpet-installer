<?php

namespace Loadsys\Composer;

use Composer\Package\PackageInterface;
use Composer\Installer\LibraryInstaller;
use RecursiveDirectoryIterator;
use RecursiveCallbackFilterIterator;
use RecursiveIteratorIterator;
use Symfony\Component\Filesystem\Filesystem;

if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

/**
 * Custom installer and event handler.
 *
 * Ensures that a package with type=puphpet-release has its `release/`
 * sub-folder copied into the project root, and associated configs copied
 * into the `puphpet/` folder afterwards.
 */
class PuphpetReleaseInstaller extends LibraryInstaller
{

    /**
     * Defines the `type`s of composer packages to which this installer applies.
     *
     * A project's composer.json file must specify `"type": "puphpet-release"`
     * in order to trigger this installer.
     *
     * @param string $packageType The `type` specified in the consuming project's composer.json.
     * @return bool True if this installer should be activated for the package in question, false if not.
     */
    public function supports($packageType)
    {
        return 'puphpet-release' === $packageType;
    }

    /**
     * Override LibraryInstaller::installCode() to hook in additional post-download steps.
     *
     * @param PackageInterface $package Package instance
     */
    protected function installCode(PackageInterface $package)
    {
        parent::installCode($package);

        if (!$this->supports($package->getType())) {
            return;
        }

        $this->mirrorReleaseItems($package);
        $this->copyConfigFile($package);
        $this->checkGitignore($package);
    }

    /**
     * Mirror (copy or delete, only as necessary) items from the installed
     * package's release/ folder into the target directory.
     *
     */
    protected function mirrorReleaseItems($package)
    {
        // Copy everything from the release/ subfolder to the project root.
        $releaseDir = $this->getInstallPath($package) . DS . 'release';
        $targetDir = getcwd();
        $acceptList = [
            'Vagrantfile',
            'puphpet',
        ];

        // Return true if the first part of the subpath for the current file exists in the accept array.
        $acceptFunc = function ($current, $key, RecursiveDirectoryIterator $iterator) use ($acceptList) {
            $pathComponents = explode(DS, $iterator->getSubPathname());
            return in_array($pathComponents[0], $acceptList, true);
        };
        $dirIterator = new RecursiveDirectoryIterator($releaseDir, RecursiveDirectoryIterator::SKIP_DOTS);
        $filterIterator = new RecursiveCallbackFilterIterator($dirIterator, $acceptFunc);
        $releaseItems = new RecursiveIteratorIterator($filterIterator, RecursiveIteratorIterator::SELF_FIRST);

        $filesystem = new Filesystem();
        $filesystem->mirror($releaseDir, $targetDir, $releaseItems, ['override' => true]);
    }

    /**
     * Search for a config file in the consuming project and copy it into
     * place if present.
     *
     */
    protected function copyConfigFile($package)
    {
        $configFilePath = getcwd() . DS . 'puphpet.yaml';
        $targetPath = getcwd() . DS . 'puphpet' . DS . 'config.yaml';
        if (is_readable($configFilePath)) {
            copy($configFilePath, $targetPath);
        }
    }

    /**
     * Check that release items copied into the consuming project are
     * properly ignored in source control (very, VERY crudely.)
     *
     */
    protected function checkGitignore($package)
    {
        $gitFolder = getcwd() . DS . '.git' . DS;

        if (!file_exists($gitFolder)) {
            return;
        }

        $gitignoreFile = getcwd() . DS . '.gitignore';
        $required = [
            '/Vagrantfile',
            '/puphpet/',
            '/.vagrant/',
        ];

        touch($gitignoreFile);
        $lines = file($gitignoreFile, FILE_IGNORE_NEW_LINES);

        foreach ($required as $entry) {
            if (!in_array($entry, $lines, true)) {
                $lines[] = $entry;
            }
        }

        file_put_contents($gitignoreFile, implode(PHP_EOL, $lines));
    }
}
