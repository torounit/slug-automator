#!/usr/bin/env bash
#
# Checks that the plugin files agree on a release version, and that the
# changelog has entries for it. Used by CI on release PRs, drafts and releases.
#
# usage: bin/check-version.sh <version> [stable|prerelease] [--untagged]
#        bin/check-version.sh --notes <version>
#
# A version with a "-" suffix is a prerelease, which leaves the Stable tag on
# the last stable release, so the Stable tag is not checked for it. The kind,
# when given (e.g. from a GitHub release), must agree with the version.
# --untagged also fails when the version's tag already exists on origin.
# --notes prints the changelog entries for the version, for release notes.

set -e

cd "$(dirname "$0")/.."
# shellcheck source=bin/release-lib.sh
source bin/release-lib.sh

usage() {
	echo "usage: $0 <version> [stable|prerelease] [--untagged]" >&2
	echo "       $0 --notes <version>" >&2
	exit 1
}

if [ "$1" = --notes ]; then
	[ $# -eq 2 ] || usage
	notes=$(changelog_section "### $2")
	if ! echo "${notes}" | grep -q '[^[:space:]]'; then
		echo "README.md has no changelog entries under ### $2." >&2
		exit 1
	fi
	echo "${notes}"
	exit
fi

version=""
kind=""
untagged=false
for arg in "$@"; do
	case "${arg}" in
		--untagged) untagged=true ;;
		stable | prerelease) [ -n "${version}" ] && [ -z "${kind}" ] || usage; kind=${arg} ;;
		-*) usage ;;
		*) [ -z "${version}" ] || usage; version=${arg} ;;
	esac
done
[ -n "${version}" ] || usage

# Annotate the workflow run on GitHub Actions; plain messages elsewhere.
error() {
	echo "${GITHUB_ACTIONS:+::error::}$1" >&2
	status=1
}

status=0

if ! is_semver "${version}"; then
	error "${version} is not a semantic version, e.g. 1.2.3 or 1.2.3-rc.1."
fi

if is_prerelease "${version}"; then
	[ "${kind}" = stable ] && error "${version} is a prerelease, but the release is not marked as a pre-release."
	kind=prerelease
else
	[ "${kind}" = prerelease ] && error "${version} is a stable version, but the release is marked as a pre-release."
	kind=stable
fi

if [ "${untagged}" = true ] && tag_exists "${version}"; then
	error "The tag ${version} already exists. Release a new version."
fi

package_version=$(node -p 'require("./package.json").version')
lock_version=$(node -p 'require("./package-lock.json").version')
header_version=$(sed -nE 's/^ \* Version:[[:space:]]*([^[:space:]]+).*$/\1/p' "${PLUGIN_FILE}")
stable_tag=$(sed -nE 's/^Stable tag:[[:space:]]*([^[:space:]]+).*$/\1/p' README.md)

if [ "${version}" != "${package_version}" ]; then
	error "${version} does not match the version in package.json (${package_version}). Run bin/bump-version.sh ${version}."
fi
if [ "${version}" != "${lock_version}" ]; then
	error "${version} does not match the version in package-lock.json (${lock_version})."
fi
if [ "${version}" != "${header_version}" ]; then
	error "${version} does not match the Version header in ${PLUGIN_FILE} (${header_version})."
fi
if [ "${kind}" = stable ] && [ "${version}" != "${stable_tag}" ]; then
	error "${version} does not match the Stable tag in README.md (${stable_tag})."
fi
if ! changelog_section "### ${version}" | grep -q '[^[:space:]]'; then
	error "README.md has no changelog entries under ### ${version}."
fi
if grep -qx "### Unreleased" README.md; then
	error "README.md still has a \"### Unreleased\" heading, which would ship to WordPress.org."
fi

exit $status
