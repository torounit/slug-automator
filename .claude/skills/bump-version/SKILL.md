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
   `## Changelog` section. If recent merged PRs aren't reflected yet, add entries there first:

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
     `### <version>` with a fresh empty `### Unreleased` heading above it
   - `package.json` and `package-lock.json`: the `version` field

   Review the diff before committing.

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
- Do not edit version numbers in `slug-automator.php`, `package.json`, or the `Stable tag:` field
  by hand — use `bin/bump-version.sh`, so the files don't drift apart.
