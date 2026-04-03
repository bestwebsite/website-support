# Best Website Support — Guardrails & Drift Prevention

This document is the **source of truth** for how the plugin is structured, how releases are produced, and what must remain consistent as new features are added.

## Goals
- Keep the plugin **safe** for client sites (no front‑end breakage, no fatal errors on activation/update).
- Keep releases **repeatable** (GitHub tag → release workflow → `website-support.zip` asset).
- Avoid “drift” by documenting **where things live** and **what to update**.

---

## Repo & Plugin Structure

**Plugin folder (in ZIP and on sites):**
- `website-support/` (must be the root folder inside the release ZIP)

**Primary plugin bootstrap file:**
- `website-support.php`
  - Defines constants (repo owner/repo, slugs, option key, version).
  - Includes required class files under `includes/`.
  - Instantiates core classes via `BWS_Plugin`.

**Core classes**
- `includes/class-bws-plugin.php` — orchestrates initialization and hooks.
- `includes/class-bws-settings.php` — settings defaults, sanitization, settings UI.
- `includes/class-bws-admin-cleanup.php` — dashboard cleanup, menu restrictions, update UI cleanup, admin notice hiding.
- `includes/class-bws-support.php` — dashboard widget + support page + email sending.
- `includes/class-bws-branding.php` — login + footer branding.
- `includes/class-bws-github-updater.php` — GitHub Releases update checker and package URL resolver.

> **Guardrail:** Any new feature should be implemented inside an existing class (or a new `includes/class-bws-*.php` file) and then included from `website-support.php`.

---

## Settings Keys & Defaults

Settings are stored under:
- Option key: `bw_support_settings` (constant: `BWS_OPTION_KEY`)

Defaults live in:
- `BWS_Settings::get_defaults()`

Sanitization lives in:
- `BWS_Settings::sanitize_settings()`

> **Guardrail:** When adding a new setting:
1. Add default in `get_defaults()`
2. Add sanitization entry in `sanitize_settings()`
3. Render the field in the settings UI
4. Implement behavior in the appropriate class

---

## Admin Notice Hiding

There are **two** mechanisms:

1. **Dashboard widget removal** (by widget IDs)
   - Settings: `dashboard_remove_custom_widget_ids`

2. **Admin notice hiding** (by CSS selectors)
   - Settings: `admin_notice_hide_selectors`
   - Behavior: `BWS_Admin_Cleanup::admin_head_cleanup()` merges selectors and outputs CSS.
   - Shorthand supported: `class1 class2` becomes `.class1.class2`

> **Tip:** For a notice with classes `venture_admin_notice venture_skins_notice ...` you can paste the class list as-is (space-separated) into the selector list.

---

## Release Workflow (GitHub Actions)

Release workflow file:
- `.github/workflows/release.yml`

Trigger:
- **Push tag event** matching `v*` (example: `v1.0.4`)

Output:
- GitHub Release + attached asset **`website-support.zip`**
  - Contains a top-level `website-support/` directory
  - Contains `website-support.php` with header version + `BWS_VERSION` patched to the tag version

> **Guardrail:** Creating a Release in the UI must also create a **new tag**. Re-using an existing tag will not trigger the workflow.

---

## Release Checklist
Before tagging `vX.Y.Z`:

- [ ] `CHANGELOG.md` has a section `## [X.Y.Z] - YYYY-MM-DD`
- [ ] Plugin loads in wp-admin **and** front-end without fatal errors
- [ ] Any new files are included from `website-support.php`
- [ ] Settings sanitization updated for new options
- [ ] (Optional) Run a quick activation test on a staging site

---

## Single Source of Truth for Version
- Source of truth is the Git tag: `vX.Y.Z`
- The workflow patches:
  - Plugin header `Version: X.Y.Z`
  - `define( 'BWS_VERSION', 'X.Y.Z' );`

> Do **not** manually edit version numbers in multiple places. Tag the repo and let the workflow patch the plugin file.
