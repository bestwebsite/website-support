# Best Website Support — Guardrails & Drift Prevention

This document is the **source of truth** for how the plugin is structured, how releases are produced, and what must remain consistent as new features are added.

## Goals
- Keep the plugin **safe** for client sites (no front-end breakage, no fatal errors on activation/update).
- Keep releases **repeatable** (GitHub tag → release workflow → `website-support.zip` asset).
- Avoid “drift” by documenting **where things live**, **what must stay stable**, and **what to update**.

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
- `includes/class-bws-dashboard.php` — dashboard cleanup (core + custom widgets), late-pass widget cleanup.
- `includes/class-bws-admin-cleanup.php` — menu restrictions, update UI cleanup, etc.
- `includes/class-bws-support.php` — dashboard widget + support page + email sending.
- `includes/class-bws-branding.php` — login + footer branding + admin notice hide CSS output.
- `includes/class-bws-github-updater.php` — GitHub Releases update checker and package URL resolver.

> **Guardrail:** Any new feature should be implemented inside an existing class (or a new `includes/class-bws-*.php` file) and then included from `website-support.php`.

---

## Hook Safety (No Fatal Errors)

### Callback methods are API-stable
If a method is referenced in a hook callback, WordPress will fatal if it cannot call it.

Examples:
- `add_action( 'admin_head', [ $branding, 'output_admin_notice_hide_css' ], 1000 );`
- `add_action( 'wp_dashboard_setup', [ $dashboard, 'cleanup_dashboard_widgets' ], 1000 );`

> **Guardrail:** Do **not** rename/remove a method that is used in `add_action()` / `add_filter()` unless you:
1. Update *every* reference
2. Provide a backwards-compatible alias (preferred) for at least one release
3. Confirm activation + wp-admin load in a staging site

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

## Tabbed Settings UI (Critical Drift Guardrail)

The settings page uses **tabs**, but settings are stored in a **single option array** (`BWS_OPTION_KEY`).

**Problem we hit:** If the form only posts fields from the active tab, and sanitization overwrites the full settings array, then saving one tab will **wipe** values from other tabs.

> **Guardrail:** If the settings UI is tabbed, sanitization MUST be “tab-aware” and MERGE with existing saved settings.
- Read `bws_active_tab` from the form submission
- Load the existing option array
- Only update keys for the submitted tab
- Preserve all other keys unchanged

**Rule of thumb:** A tabbed UI must either:
- submit **all** fields for **all** tabs, **or**
- sanitize by **merging** only the current tab’s keys into the stored array (our preferred approach).

---

## Dashboard Cleanup (Widgets)

There are **two** widget cleanup mechanisms:

### 1) Standard dashboard widgets (checkboxes)
- Quick Draft, Events/News, Activity, At a Glance, Site Health, Welcome panel, etc.

### 2) Custom dashboard widget removal (by widget IDs)
- Setting: `dashboard_remove_custom_widget_ids`

> **Guardrail:** Widget removal must run in **two passes**:
1. Regular pass on `wp_dashboard_setup`
2. Late pass (e.g. `admin_init` or late `wp_dashboard_setup` priority) to remove widgets registered after the initial hook (Elementor/third-party widgets often register late).

### Common widget IDs we expect to support
- Elementor:
  - `e-dashboard-overview` (Elementor Overview)
  - `e-dashboard-ally` (Elementor Accessibility)
- WP Mail SMTP:
  - `wp_mail_smtp_reports_widget_lite`

> **Guardrail:** If we add “helper checkboxes” for popular plugins, they should still write to the same underlying settings and removal logic.

---

## Admin Notice Hiding (CSS Selectors)

Admin notice hiding is done by outputting CSS in wp-admin.

- Setting: `admin_notice_hide_selectors`
- Output method: `BWS_Branding::output_admin_notice_hide_css()` (runs on `admin_head`)

**Input rules**
- One selector per line
- Shorthand supported: `class1 class2` becomes `.class1.class2`
- Safety hardening:
  - Strip braces/semicolons/tags
  - Limit number of selectors and max length per selector

> **Tip:** For a notice with classes `venture_admin_notice venture_skins_notice ...` you can paste the class list as-is (space-separated) into the selector list.

---

## Release Workflow (GitHub Actions)

Release workflow file:
- `.github/workflows/release.yml`

Trigger:
- **Push tag event** matching `v*` (example: `v1.0.6`)

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
- [ ] Hook callback methods referenced in hooks actually exist (no stale method names)
- [ ] Settings sanitization updated for new options (especially for tabbed UI merge behavior)
- [ ] Dashboard cleanup still runs both normal + late pass
- [ ] (Optional) Run a quick activation test on a staging site

---

## Single Source of Truth for Version
- Source of truth is the Git tag: `vX.Y.Z`
- The workflow patches:
  - Plugin header `Version: X.Y.Z`
  - `define( 'BWS_VERSION', 'X.Y.Z' );`

> Do **not** manually edit version numbers in multiple places. Tag the repo and let the workflow patch the plugin file.