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

@TODO


## License

[MIT](https://github.com/loadsys/puphpet-release/blob/master/LICENSE). In particular, all [PuPHPet](http://puphpet.com) work belongs to the original authors. This project is strictly for our own convenience.


## Copyright

&copy; [Loadsys Web Strategies](http://loadsys.com) 2015
