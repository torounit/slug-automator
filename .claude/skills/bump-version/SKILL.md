---
name: bump-version
description: Use when the user wants to bump the plugin version (e.g. "bump version", "/bump-version").
---

# Bump Version Skill

Use this skill when the user wants to bump the plugin version (e.g. "bump version", "/bump-version").

Version bumps go through a pull request, because `main` only accepts changes via PRs.

- `bin/bump-version.sh <version>` updates `slug-automator.php`, `README.md`, `package.json`, and
  `package-lock.json`.
- When a PR from a `release/*` branch is merged, CI (`.github/workflows/draft-release.yml`) creates a
  draft GitHub release targeting the merge commit, with the changelog section as its notes.
- Publishing the draft in the GitHub web UI creates the tag on that merge commit and triggers the
  release job (`.github/workflows/release.yml`), which builds and deploys to WordPress.org.

## Steps

1. **Make sure the changelog is ready**

   Check that `README.md` has unreleased changes recorded under the `### Unreleased` heading in the
   `## Changelog` section. If recent merged PRs aren't reflected yet, add entries there first,
   creating the `### Unreleased` heading above the latest version if it doesn't exist:

   ```bash
   gh pr list --state merged --limit 10 --repo torounit/slug-automator
   ```

   Entry format:

   ```markdown
   ### Unreleased

   * Change description 1
   * Change description 2
   ```

2. **Create a release branch from the latest `main`**

   Choose the version following [Semantic Versioning](https://semver.org/).

   ```bash
   git switch main && git pull --ff-only
   git switch -c release/<version>
   ```

3. **Bump the version**

   ```bash
   bash bin/bump-version.sh <version>
   ```

   This updates:
   - `slug-automator.php`: the `Version:` header
   - `README.md`: `Stable tag:` (stable releases only), and the `### Unreleased` heading becomes
     `### <version>`. No empty `### Unreleased` heading is left behind, since it would ship to
     WordPress.org.
   - `package.json` and `package-lock.json`: the `version` field

   Review the diff, and check it with `bin/check-version.sh <version>`, before committing.

4. **Commit, push, and open a PR**

   ```bash
   git commit -am "Release <version>"
   git push -u origin release/<version>
   gh pr create --base main --title "Release <version>"
   ```

5. **Publish the draft release**

   After the PR is merged, CI creates a draft release `<version>`. Ask the user to review and publish
   it in the GitHub web UI (https://github.com/torounit/slug-automator/releases). Publishing it tags
   the merge commit on `main` and deploys to WordPress.org.

## Notes

- Write Changelog entries in English, based on PR titles and descriptions.
- `bin/bump-version.sh` checks everything before changing a file, so a failed run can be repeated:
  the version must be semantic (`1.2.3`, `1.2.3-rc.1`), and `README.md` needs a `### Unreleased`
  section with entries. A `-` suffix marks a prerelease, which leaves the `Stable tag:` alone. A
  stable release also takes in the sections of its prereleases (`### 1.2.3-rc.1`, …), so its
  notes list every change since the last stable release; it can then be bumped without a
  `### Unreleased`. Running it again for the same version merges any new `### Unreleased` entries
  into its section. `npm version` runs before README.md and the plugin header are changed.
- `bin/check-version.sh <version>` checks that the files (including `package-lock.json`) and the
  changelog are ready, and that no `### Unreleased` heading is left. CI runs it on the release PR
  ("Check Release Version") and when drafting with `--untagged`, which also refuses a version
  whose tag already exists, and when publishing. Run it locally after bumping.
- A prerelease is drafted as a GitHub pre-release. Keep it marked as one when publishing: the
  publish check skips the `Stable tag:` only for releases marked pre-release.
- Do not edit version numbers in `slug-automator.php`, `package.json`, or the `Stable tag:` field
  by hand — use `bin/bump-version.sh`, so the files don't drift apart.
