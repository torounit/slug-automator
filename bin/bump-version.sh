#!/usr/bin/env bash

set -e

if [ $# -lt 1 ]; then
	echo "usage: $0 <version>" >&2
	exit 1
fi

version=$1

# Work from the repository root, wherever the script is run from.
cd "$(dirname "$0")/.."

# Everything is checked before any file is changed, so a failed run can simply be repeated.
if ! echo "${version}" | grep -qE '^[0-9]+\.[0-9]+\.[0-9]+(-[0-9A-Za-z.-]+)?$'; then
	echo "${version} is not a semantic version, e.g. 1.2.3 or 1.2.3-rc.1." >&2
	exit 1
fi

# A "-" suffix marks a prerelease, as in bin/check-version.sh.
case "${version}" in
	*-*) prerelease=true ;;
	*) prerelease=false ;;
esac

# Pick the changelog heading that becomes "### <version>": the pending
# "### Unreleased", or for a stable release the heading of its latest
# prerelease. A heading already named "### <version>" is kept as it is.
if grep -qxF "### ${version}" README.md; then
	changelog_heading=""
elif grep -qx "### Unreleased" README.md; then
	changelog_heading="### Unreleased"
elif [ "${prerelease}" = false ] && grep -qE "^### ${version//./\\.}-" README.md; then
	changelog_heading=$(grep -m 1 -E "^### ${version//./\\.}-" README.md)
else
	echo "README.md has no \"### Unreleased\" changelog heading. Add the entries for ${version} under it first." >&2
	exit 1
fi

# Stable tag should only track stable releases, not prereleases.
if [ "${prerelease}" = false ]; then
	# Preserve the trailing markdown line break (two spaces) after the value.
	sed -i.bak -E "s/^(Stable tag: *).*/\1${version}  /" README.md
	rm README.md.bak
fi

if [ -n "${changelog_heading}" ]; then
	sed -i.bak -e "s/^${changelog_heading//./\\.}$/### ${version}/" README.md
	rm README.md.bak
fi

# Preserve the existing header alignment, only replace the value.
sed -i.bak -E "s/^( \* Version: *).*/\1${version}/" slug-automator.php
rm slug-automator.php.bak

# Update package.json and package-lock.json without creating a git commit or tag.
npm version "${version}" --no-git-tag-version --allow-same-version
