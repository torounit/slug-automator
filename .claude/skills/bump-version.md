# Bump Version Skill

Use this skill when the user wants to bump the plugin version (e.g. "bump version", "/bump-version").

## Steps

1. **Check current version**

   ```bash
   grep -n "Version:" slug-automator.php
   grep -n '"version"' package.json
   ```

2. **Ask for the new version**

   Ask the user for the new version number. Offer patch / minor / major options.

3. **Update version in 3 files simultaneously**

   - `slug-automator.php` — `Version:` header
   - `package.json` — `version` field
   - `README.md` — `Stable tag:` field

4. **Check recent PRs and update Changelog**

   ```bash
   gh pr list --state merged --limit 10 --repo torounit/slug-automator
   ```

   Review PRs merged since the previous version tag and add a new entry to the `## Changelog` section of `README.md`.

   Entry format:

   ```markdown
   ### X.Y.Z

   * Change description 1
   * Change description 2
   ```

## Notes

- `package-lock.json` does not need manual edits — it is updated automatically by `npm install`.
- Write Changelog entries in English, based on PR titles and descriptions.
- Follow [Semantic Versioning](https://semver.org/).
