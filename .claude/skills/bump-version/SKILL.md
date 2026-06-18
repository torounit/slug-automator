---
name: bump-version
description: Use when the user wants to bump the plugin version (e.g. "bump version", "/bump-version").
---

# Bump Version Skill

Use this skill when the user wants to bump the plugin version (e.g. "bump version", "/bump-version").

Releases are handled by [release-it](https://github.com/release-it/release-it) via `npm run release`,
not by manually editing files. release-it determines the version, runs `bin/bump-version.sh` to update
`slug-automator.php` and `README.md`, updates `package.json` itself, then commits, tags, pushes, and
creates a GitHub release. Pushing the tag triggers the existing CI release job
(`.github/workflows/test-and-deploy.yml`), which builds and deploys to WordPress.org.

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

2. **(Optional) Dry run**

   ```bash
   npm run release -- --dry-run
   ```

   Confirms the version bump, changelog conversion, and release steps without making any changes.

3. **Run the release**

   ```bash
   npm run release
   ```

   release-it will prompt for the version bump (patch / minor / major / custom), following
   [Semantic Versioning](https://semver.org/). It then:
   - Runs `bin/bump-version.sh <version>` to update `slug-automator.php` (`Version:` header) and
     `README.md` (`Stable tag:` and the `### Unreleased` heading, which becomes `### <version>`)
   - Updates `package.json`'s `version` field
   - Commits, tags, and pushes
   - Creates a GitHub release

4. **Add a fresh `### Unreleased` heading**

   After release-it converts `### Unreleased` to `### <version>`, add a new empty
   `### Unreleased` heading above it in `README.md` so future changes have somewhere to go, then
   commit and push that small follow-up change directly (no PR needed).

## Notes

- `package-lock.json` does not need manual edits — it is updated automatically by `npm install`.
- Write Changelog entries in English, based on PR titles and descriptions.
- Do not edit version numbers in `slug-automator.php`, `package.json`, or the `Stable tag:` field
  by hand — that's `release-it`'s and `bin/bump-version.sh`'s job, and doing it manually risks
  drift between the files.
