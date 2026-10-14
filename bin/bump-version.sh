#!/usr/bin/env bash

set -e

if [ $# -lt 1 ]; then
	echo "usage: $0 <version>"
	exit 1
fi

version=$1

# Stable tag should only track stable releases, not prereleases.
if ! echo "${version}" | grep -qE "alpha|beta|RC|rc"; then
	# Preserve the trailing markdown line break (two spaces) after the value.
	sed -i.bak -E "s/^(Stable tag: *).*/\1${version}  /" README.md
	rm README.md.bak
fi

# Turn the pending "Unreleased" changelog entry into the released version heading,
# and start a fresh "Unreleased" heading above it for future changes.
sed -i.bak -e "s/^### Unreleased$/### Unreleased\\
\\
### ${version}/" README.md
rm README.md.bak

# Preserve the existing header alignment, only replace the value.
sed -i.bak -E "s/^( \* Version: *).*/\1${version}/" slug-automator.php
rm slug-automator.php.bak

# Update package.json and package-lock.json without creating a git commit or tag.
npm version "${version}" --no-git-tag-version --allow-same-version
