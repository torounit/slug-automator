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

# "### Unreleased" and any "### <version>" become one "### <version>" section.
# A stable release also takes in the sections of its prereleases (e.g.
# "### 1.2.3-rc.1"), so its notes list every change since the last stable
# release. Running it again for the same version keeps the section as it is.
merge_prereleases=0
if [ "${prerelease}" = false ]; then
	merge_prereleases=1
fi

status=0
awk -v version="${version}" -v merge_prereleases="${merge_prereleases}" -v boundary="${CHANGELOG_BOUNDARY}" '
	function is_merged(line) {
		return line == "### Unreleased" || line == "### " version ||
			(merge_prereleases && index(line, "### " version "-") == 1)
	}
	{ lines[NR] = $0 }
	END {
		for (i = 1; i <= NR; i++) {
			if (lines[i] ~ boundary) {
				merged = is_merged(lines[i])
				if (merged) {
					drop[i] = 1
					if (!first) {
						first = i
					}
					continue
				}
			}
			if (merged) {
				drop[i] = 1
				if (lines[i] ~ /[^[:space:]]/) {
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
	echo "README.md has no changelog section for ${version}. Add its entries under a \"### Unreleased\" heading first." >&2
	exit 1
elif [ "${status}" -eq 3 ]; then
	echo "The changelog sections for ${version} in README.md (### Unreleased, ### ${version} and its prereleases) have no entries. Add the changes first." >&2
	exit 1
elif [ "${status}" -ne 0 ]; then
	exit "${status}"
fi

# Stable tag should only track stable releases, not prereleases.
if [ "${prerelease}" = false ]; then
	# Preserve the trailing markdown line break (two spaces) after the value.
	sed -i.bak -E "s/^(Stable tag: *).*/\1${version}  /" "${readme}"
	rm "${readme}.bak"
fi

# Update package.json and package-lock.json without creating a git commit or tag.
# It runs first, since it is the step most likely to fail (npm itself); nothing
# else has changed yet when it does.
npm version "${version}" --no-git-tag-version --allow-same-version

# Preserve the existing header alignment, only replace the value.
sed -i.bak -E "s/^( \* Version: *).*/\1${version}/" "${PLUGIN_FILE}"
rm "${PLUGIN_FILE}.bak"

cp "${readme}" README.md
