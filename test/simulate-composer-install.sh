#!/usr/bin/env bash
# Run through a `composer install` using local dependencies.

# If the ../tmp dir is missing an existing symlink to your working copies of `loadsys/puphpet-release`:
	# Prompt for path to working copy.
	# Create symlink to it as ../tmp/puphpet-release

# If no first argument
	# Prompt for branch name from puphpet-release working copy to use. (Or default to master?)

# Get the name of the branch that is currently checked out in ../ to use for `puphpet-release-composer-installer` version.

# Delete all contents from the ../tmp folder.

# Write the composer.json file in this test dir to the tmp/ dir, adding branch names obtained earlier.
	# s/PRCI_BRANCH_NAME/$puphpet-release-composer-installer branch name/
	# s/PR_BRANCH_NAME/$puphpet-release branch name/

# Copy the puphpet.yaml file from test/ to ../tmp/.
# Copy the .gitignore file from test/ to ../tmp/.

# cd ../tmp

# Execute the `composer install` command itself.


# In test mode, check for canary values, exit >0 if any are missing. (We could run this script via travis as a test suite.)
	# grep the ../tmp/.gitignore for the presence of /Vagrantfile and /puphpet/.
	# ensure the puphpet/ directory exists.
	# grep the puphpet/config.yaml file for a canary value from test/puphpet.yaml.

