# puphpet-release-composer-installer

Provides a composer custom installer that works with `loadsys/puphpet-release` to add a PuPHPet.com vagrant box to a project via composer.

You probably will never need to use this project yourself directly. We use it for our [loadsys/puphpet-release](https://github.com/loadsys/puphpet-release) package to copy parts of the PuPHPet package into the necessary locations for the consuming project.


## Usage

To use this installer with another composer package, add the following block to your package's `composer.json` file:

```json
    "type": "puphpet-release",
    "require": {
        "loadsys/puphpet-release-composer-installer": "*"
    },
```


### Composer Post Install Actions

This installer is responsible for performing post-`composer install` actions for the `loadsys/puphpet-release` package.

When this package is included in another project via composer, the installer fires a number of additional actions in order to address some of the incompatibilities between puphpet's default setup and the requirements for Vagrant (such as the `Vagrantfile` living in the project's root directory instead of the composer-installed `/vendors/loadsys/puphpet-release/release/` folder.)

* Copies a Vagrantfile into the consuming project's root folder.
* Copies a puphpet/ folder into the consuming project's root folder. 
* Copies the consuming project's `/puphpet.yaml` into the correct place as `/puphpet/config.yaml`.
* Tries to ensure that the consuming project's `/.gitignore` file contains the proper entries to ignore `/Vagrantfile` and `/puphpet/`, if it is present.

Unresolved Questions:

* Do we always overwrite the Vagrantfile and puphpet/ folders?
* What if there are customizations to files/ or exec-*/ folders? Should we even try to detect those? (diff the contents of the package's release/ folder with the versions in project root?)
* Should we try to validate that the target project's config.yaml file has all expected (mandatory) keys as the spec changes upstream. Can we write/maintain a "unit test" and/or diffing tool for it? It's just YAML after all.
* What should we do if there isn't a `/puphpet.yaml` for us to copy? The VM will surely not work correctly with completely "default" options. Maybe prompt the user to go generate one?


## Contributing

Testing this composer plugin is difficult because it involves at least 2 other projects: the loadsys/puphpet-release, and the project from which you want to consume it. To set up a local project that will exercise this installer and test the result of including the `loadsys/puphpet-release` package in your project, follow these instructions:

1. `mkdir some-project-folder; cd some-project-folder`
1. `git clone git@github.com:loadsys/puphpet-release-composer-installer.git`
1. `git clone git@github.com:loadsys/puphpet-release.git`
1. Create a branch in either project, and **commit** your changes to the branch. (This is very important to the process: Any changes you wish to test must exist in the git index already, not just in your working copy.)
1. `mkdir test-app; cd test-app`
1. Copy a previously configured and tested PuPHPet `config.yaml` file into the `test-app/` folder, but renaming it to `puphpet.yaml`.
1. Create a `composer.json` file in the `test-app/` folder with the following contents:
		{
			"name": "you/test-app",
			"description": "Tests that the puphpet-release-composer-installer performs all of its actions properly.",
			"require": {
				"loadsys/puphpet-release-composer-installer": "dev-yourWorkingBranchNameHere",
				"loadsys/puphpet-release": "dev-yourWorkingBranchNameHere"
			},
			"repositories": [
				{
					"packagist": false
				},
				{
					"type": "vcs",
					"url": "../puphpet-release"
				},
				{
					"type": "vcs",
					"url": "../puphpet-release-composer-installer"
				}
			]
		}
	This file will instruct composer to use local git repos to fetch the dependencies listed, and to ignore the packagist.org website entirely.
1. Update the targeted branches in the `require:` block of the above composer.json file to match your local working branch names in each project (remember to keep the `dev-` prefix.)
1. From within the `test-app` folder, run `composer install -vvv`. (The `v`s enable highly verbose output that won't always be necessary. They're safe to exclude once you're in a rhythm.)
	* The `test-app/` folder should end up with a `Vagrantfile` and `puphpet` folder directly in its root.
	* Your sample `puphpet.yaml` file should have been copied to `/puphpet/config.yaml`.
	* If you have a `.gitignore` file present, it should have been "safely" updated to include the new additions to the root project folder.
1. From here, the process loops through the following steps:
	* Make changes to the puphpet-release or puphpet-release-composer-installer projects.
	* **Commit** the changes to your working branch.
	* Remove some files from `test-app/` to get clean results each time. Something like `rm -rf vendor composer.lock Vagrantfile puphpet;` should do the trick.
	* Run `composer install` again.
	* Check the results.
	* Repeat.
1. Once you're satisfied with the results, push your branch and submit a PR.


## License

[MIT](https://github.com/loadsys/puphpet-release/blob/master/LICENSE). In particular, all [PuPHPet](http://puphpet.com) work belongs to the original authors. This project is strictly for our own convenience.


## Copyright

&copy; [Loadsys Web Strategies](http://loadsys.com) 2015
