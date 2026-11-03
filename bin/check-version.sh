#!/usr/bin/env bash
#
# Checks that the plugin files agree on a release version, and that the
# changelog has entries for it. Used by CI on release PRs, drafts and releases.
#
# usage: bin/check-version.sh <version> [stable|prerelease]
#
# Prereleases (versions with a "-" suffix, unless told otherwise) leave the
# Stable tag on the last stable release, so it is not checked for them.

set -e

if [ $# -lt 1 ]; then
	echo "usage: $0 <version> [stable|prerelease]" >&2
	exit 1
fi

version=$1
kind=${2:-}
if [ -z "${kind}" ]; then
	case "${version}" in
		*-*) kind=prerelease ;;
		*) kind=stable ;;
	esac
fi

cd "$(dirname "$0")/.."

# Annotate the workflow run on GitHub Actions; plain messages elsewhere.
error() {
	echo "${GITHUB_ACTIONS:+::error::}$1" >&2
	status=1
}

status=0

package_version=$(node -p 'require("./package.json").version')
header_version=$(sed -nE 's/^ \* Version:[[:space:]]*([^[:space:]]+).*$/\1/p' slug-automator.php)
stable_tag=$(sed -nE 's/^Stable tag:[[:space:]]*([^[:space:]]+).*$/\1/p' README.md)

if [ "${version}" != "${package_version}" ]; then
	error "${version} does not match the version in package.json (${package_version}). Run bin/bump-version.sh ${version}."
fi
if [ "${version}" != "${header_version}" ]; then
	error "${version} does not match the Version header in slug-automator.php (${header_version})."
fi
if [ "${kind}" = stable ] && [ "${version}" != "${stable_tag}" ]; then
	error "${version} does not match the Stable tag in README.md (${stable_tag})."
fi

# The section under "### <version>" must contain something besides blank lines.
if ! awk -v heading="### ${version}" '
	$0 == heading { found = 1; next }
	found && /^#/ { exit }
	found && /[^[:space:]]/ { entries = 1 }
	END { exit !entries }
' README.md; then
	error "README.md has no changelog entries under ### ${version}."
fi

exit $status
