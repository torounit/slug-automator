#!/usr/bin/env bash
#
# Shared by bin/bump-version.sh and bin/check-version.sh, so both agree on
# what a version is and where a changelog section starts and ends.

# The main plugin file, which carries the "Version:" header.
PLUGIN_FILE=slug-automator.php

# Lines that start a new changelog section: "#" to "###" headings. Deeper
# subheadings ("#### Fixed") and lines such as "#123 ..." belong to the section.
# (No "#{1,3}": mawk, the default awk on Ubuntu, may not support intervals.)
CHANGELOG_BOUNDARY='^(#|##|###)( |$)'

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

# Whether the tag $1 already exists on the origin remote.
tag_exists() {
	git ls-remote --exit-code --tags origin "refs/tags/$1" > /dev/null 2>&1
}

# Prints the lines of README.md under the changelog heading "$1" (e.g.
# "### 1.2.3"), up to the next section.
changelog_section() {
	awk -v heading="$1" -v boundary="${CHANGELOG_BOUNDARY}" '
		$0 == heading { found = 1; next }
		found && $0 ~ boundary { exit }
		found { print }
	' README.md
}
