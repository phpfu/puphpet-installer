#!/usr/bin/env bash

#---------------------------------------------------------------------
usage () {
	cat <<EOT

${0##*/}
    Simulates the operation of the \`composer install\` command using this project.

Usage:
    bin/${0##*/} [-h|-t] [release-project-branch-name]

Options:
    -h Prints this help text.
    -t Enables "testing" mode. Will run without prompts, and will execute a number of "unit tests" on the result afterwards, exiting non-zero on failure.

EOT

	exit 0
}
if [ "$1" = '-h' ]; then
	usage
fi


SHUNIT_FETCH_URL="https://shunit2.googlecode.com/files/shunit2-2.1.6.tgz"
SHUNIT_DOWNLOAD_VERSION=$(basename "${SHUNIT_FETCH_URL}" '.tgz')


# Define working directories.
BASE_DIR="$( cd -P "$( dirname "$0" )"/../.. >/dev/null 2>&1 && pwd )"
TEST_DIR="${BASE_DIR}/tests/integration"
BUILD_DIR="${BASE_DIR}/build"


# Set testing mode.
TEST_MODE=
if [ "$1" = '-t' ]; then
	echo "## Setting test mode ON."
	TEST_MODE="yes"
	shift
fi


# Make sure the build dir exists.
if [ ! -d "${BUILD_DIR}" ]; then
	echo "## Creating build directory."
	mkdir -p "${BUILD_DIR}"
fi


# Make sure the build dir contains a symlink to your working copy of `loadsys/puphpet-release`.
echo "## Checking the symlink to the release project."
RELEASE_PROJECT_SYMLINK="${BUILD_DIR}/release-project"

if [ -h "${RELEASE_PROJECT_SYMLINK}" ]; then
	RELEASE_PROJECT_PATH=$(readlink "${RELEASE_PROJECT_SYMLINK}")
elif [ -d "${RELEASE_PROJECT_SYMLINK}" ]; then
	RELEASE_PROJECT_PATH="${RELEASE_PROJECT_SYMLINK}"
elif [ "${TEST_MODE}" ]; then
	echo "!! No symlink to the release project working copy"
	echo "!! is present at \`${RELEASE_PROJECT_SYMLINK}\`."
	echo "!! Please create it."
	exit 1
else
	read -p "  Please provide the path to the release project working copy > " RELEASE_PROJECT_PATH
	ln -s "${RELEASE_PROJECT_PATH}" "${RELEASE_PROJECT_SYMLINK}"
fi


# Set the release project's branch name to use.
if [ "${TEST_MODE}" ]; then
	# In testing mode, just default to master when no arg provided.
	RELEASE_PROJECT_BRANCH=${1:-master}
	shift
elif [ -n "$1" ]; then
	RELEASE_PROJECT_BRANCH=$1
else
	read -p "  Please provide the branch name from the release project to use > " RELEASE_PROJECT_BRANCH
fi
echo "## Release project branch name is \`${RELEASE_PROJECT_BRANCH}\`."


# Get the name of the branch that is currently checked out in ../ to use.
if [[ -n "${TRAVIS_PULL_REQUEST}" && "${TRAVIS_PULL_REQUEST}" -ne "false" ]]; then
	INSTALLER_PROJECT_BRANCH="pull/${TRAVIS_PULL_REQUEST}/merge"
	(
		cd "${BASE_DIR}" >/dev/null 2>&1
		git checkout -qb $INSTALLER_PROJECT_BRANCH
	)
elif [ -n "${TRAVIS_COMMIT}" ]; then
	INSTALLER_PROJECT_BRANCH="${TRAVIS_BRANCH}#${TRAVIS_COMMIT}"
else
	INSTALLER_PROJECT_BRANCH=$( cd "${BASE_DIR}" >/dev/null 2>&1; git rev-parse --quiet --abbrev-ref HEAD 2>/dev/null )
fi
echo "## Installer project branch name is \`${INSTALLER_PROJECT_BRANCH}\`."


# Delete all contents from the build dir, except the .gitkeep file and release-project symlink.
echo "## Purging old files from build directory."
shopt -s dotglob extglob
(
	cd "${BUILD_DIR}"
	rm -rf !(.|..|.gitkeep|release-project|${SHUNIT_DOWNLOAD_VERSION})
)


# Copy the testing files from tests/integration/ to build/.
echo "## Populating the build directory."
shopt -s dotglob
(
	cd "${TEST_DIR}"
	cp -R * "${BUILD_DIR}/"
	mkdir "${BUILD_DIR}/.git/"
)

shopt -u dotglob extglob


# Write the composer.json file in this test dir to the build/ dir, adding branch names obtained earlier.
echo "## Writing customized composer.json file."
sed \
 -e "s|PRCI_BRANCH_NAME|${INSTALLER_PROJECT_BRANCH}|" \
 -e "s|PR_BRANCH_NAME|${RELEASE_PROJECT_BRANCH}|" \
 -e "s|PR_DIRECTORY|${RELEASE_PROJECT_PATH}|" \
 <"${TEST_DIR}/composer.json" \
 >"${BUILD_DIR}/composer.json"


# Execute the `composer install` command itself.
echo "## Executing \`composer install\` in the build directory."
COMPOSER_OUTPUT=$(
	cd "${BUILD_DIR}/";
	composer install --no-interaction --ignore-platform-reqs
)
COMPOSER_EXIT_CODE=$?

# End the script if test mode is OFF.
if [ -z "${TEST_MODE}" ]; then
	if [ "${COMPOSER_EXIT_CODE}" ]; then
		echo "!! Composer installation failed. Examine the results in \`${BUILD_DIR}\`."
		echo ''
		echo "${COMPOSER_OUTPUT}"
		exit $COMPOSER_EXIT_CODE
	else
		echo "## Done simulating \`composer install\`. Examine the results in \`${BUILD_DIR}\`."
		exit 0
	fi
fi


# In test mode, run assertions using the shunit2 test framework.
# (We run this script via travis as an integration test suite.)


# Make sure we have shunit2 available.
SHUNIT_TMP_DOWNLOAD="${BUILD_DIR}/${SHUNIT_DOWNLOAD_VERSION}.tgz"
SHUNIT_EXTRACT_PATH="${BUILD_DIR}"
SHUNIT_EXECUTABLE="${SHUNIT_EXTRACT_PATH}/${SHUNIT_DOWNLOAD_VERSION}/src/shunit2"
if [ ! -x "${SHUNIT_EXECUTABLE}" ]; then
	if [ ! -f "${SHUNIT_TMP_DOWNLOAD}" ]; then
		echo "## Fetching shunit2."
		curl -L \
			--silent \
			--output "${SHUNIT_TMP_DOWNLOAD}" \
			$SHUNIT_FETCH_URL
	fi
	echo "## Unpacking shunit2."
	tar zxf "${SHUNIT_TMP_DOWNLOAD}" -C "${SHUNIT_EXTRACT_PATH}"
fi


# Define the tests to execute.
echo "## Defining tests."
testComposerExitCode () {
	assertTrue "composer must not error during install.

${COMPOSER_OUTPUT}
		" "$COMPOSER_EXIT_CODE"
}

testGitignore () {
	grep -qe '^/Vagrantfile$' "${BUILD_DIR}/.gitignore"
	assertTrue ".gitignore must have a '/Vagrantfile' entry." "$?"

	grep -qe '^/puphpet/$' "${BUILD_DIR}/.gitignore"
	assertTrue ".gitignore must have a '/puphpet/' entry." "$?"

	grep -qe '^/.vagrant/$' "${BUILD_DIR}/.gitignore"
	assertTrue ".gitignore must have a '/.vagrant/' entry." "$?"
}

testPuphpetDir () {
	[ -d "${BUILD_DIR}/puphpet" ]
	assertTrue "puphpet/ directory must be present." "$?" || return

	grep -qe '^canary: "foo"$' "${BUILD_DIR}/puphpet/config.yaml"
	assertTrue "puphpet.yaml file must be copied into puphpet/ directory." "$?"
}

# Load and run shUnit2
echo "## Executing tests:"
. "${SHUNIT_EXECUTABLE}"

