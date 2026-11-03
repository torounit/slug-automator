#!/usr/bin/env bash

set -e

if [ $# -ne 1 ]; then
	echo "usage: $0 <version>" >&2
	exit 1
fi

version=$1

# Work from the repository root, wherever the script is run from.
cd "$(dirname "$0")/.."
# shellcheck source=bin/release-lib.sh
source bin/release-lib.sh

# Everything is checked, and the new README.md prepared, before any file is
# changed, so a failed run can simply be repeated.
if ! is_semver "${version}"; then
	echo "${version} is not a semantic version, e.g. 1.2.3 or 1.2.3-rc.1." >&2
	exit 1
fi

prerelease=false
if is_prerelease "${version}"; then
	prerelease=true
fi

readme=$(mktemp)
trap 'rm -f "${readme}"' EXIT
cp README.md "${readme}"

if grep -qxF "### ${version}" README.md; then
	# Already bumped: keep the changelog, unless new entries are waiting.
	if grep -qx "### Unreleased" README.md; then
		echo "README.md has both \"### Unreleased\" and \"### ${version}\". Move the Unreleased entries under ### ${version}, or release them as a new version." >&2
		exit 1
	fi
else
	# "### Unreleased" becomes "### <version>". A stable release also takes in
	# the sections of its prereleases (e.g. "### 1.2.3-rc.1"), so its notes
	# list every change since the last stable release.
	merge_prereleases=0
	if [ "${prerelease}" = false ]; then
		merge_prereleases=1
	fi

	status=0
	awk -v version="${version}" -v merge_prereleases="${merge_prereleases}" '
		function is_merged(line) {
			return line == "### Unreleased" ||
				(merge_prereleases && index(line, "### " version "-") == 1)
		}
		{ lines[NR] = $0 }
		END {
			for (i = 1; i <= NR; i++) {
				if (lines[i] ~ /^#/) {
					merged = is_merged(lines[i])
					if (merged && !first) {
						first = i
					}
				}
				if (merged) {
					drop[i] = 1
					if (lines[i] !~ /^#/ && lines[i] ~ /[^[:space:]]/) {
						entries[++count] = lines[i]
					}
				}
			}
			if (!first) {
				exit 2
			}
			if (!count) {
				exit 3
			}
			for (i = 1; i <= NR; i++) {
				if (i == first) {
					print "### " version
					print ""
					for (j = 1; j <= count; j++) {
						print entries[j]
					}
					print ""
				}
				if (!drop[i]) {
					print lines[i]
				}
			}
		}
	' README.md > "${readme}" || status=$?

	if [ "${status}" -eq 2 ]; then
		echo "README.md has no \"### Unreleased\" changelog heading. Add the entries for ${version} under it first." >&2
		exit 1
	elif [ "${status}" -eq 3 ]; then
		echo "The \"### Unreleased\" section in README.md has no entries. Add the changes in ${version} first." >&2
		exit 1
	elif [ "${status}" -ne 0 ]; then
		exit "${status}"
	fi
fi

# Stable tag should only track stable releases, not prereleases.
if [ "${prerelease}" = false ]; then
	# Preserve the trailing markdown line break (two spaces) after the value.
	sed -i.bak -E "s/^(Stable tag: *).*/\1${version}  /" "${readme}"
	rm "${readme}.bak"
fi

cp "${readme}" README.md

# Preserve the existing header alignment, only replace the value.
sed -i.bak -E "s/^( \* Version: *).*/\1${version}/" "${PLUGIN_FILE}"
rm "${PLUGIN_FILE}.bak"

# Update package.json and package-lock.json without creating a git commit or tag.
npm version "${version}" --no-git-tag-version --allow-same-version
