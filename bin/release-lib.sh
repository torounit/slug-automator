#!/usr/bin/env bash
#
# Shared by bin/bump-version.sh and bin/check-version.sh, so both agree on
# what a version is and where its changelog section starts and ends.

# The main plugin file, which carries the "Version:" header.
PLUGIN_FILE=slug-automator.php

# Whether $1 is a semantic version npm accepts: 1.2.3 or 1.2.3-rc.1, without
# leading zeros or empty prerelease identifiers.
is_semver() {
	local number='(0|[1-9][0-9]*)'
	local identifier='(0|[1-9][0-9]*|[0-9]*[A-Za-z-][0-9A-Za-z-]*)'
	echo "$1" | grep -qE "^${number}\.${number}\.${number}(-${identifier}(\.${identifier})*)?$"
}

# Whether $1 is a prerelease: a version with a "-" suffix.
is_prerelease() {
	case "$1" in
		*-*) return 0 ;;
		*) return 1 ;;
	esac
}

# Prints the lines of README.md under the changelog heading "$1" (e.g.
# "### 1.2.3"), up to the next heading.
changelog_section() {
	awk -v heading="$1" '
		$0 == heading { found = 1; next }
		found && /^#/ { exit }
		found { print }
	' README.md
}
