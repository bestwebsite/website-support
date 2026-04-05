# Best Website Support — Guardrails & Drift Prevention

This document is the **source of truth** for how the plugin is structured, how releases are produced, and what must remain consistent as new features are added.

## Goals
- Keep the plugin **safe** for client sites (no front-end breakage, no fatal errors on activation/update).
- Keep releases **repeatable** (GitHub tag → release workflow → `website-support.zip` asset).
- Avoid drift by documenting **where things live**, **what must stay stable**, and **what to update**.

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
- `includes/class-bws-plugin.php` — orchestrates initialization and admin asset loading for settings UI.
- `includes/class-bws-settings.php` — defaults, sanitization, tabbed settings UI.
- `includes/class-bws-dashboard.php` — dashboard widget cleanup (core + custom IDs + Elementor/WP Mail SMTP helpers).
- `includes/class-bws-admin-cleanup.php` — admin UI cleanup + restrictions + update UI cleanup.
- `includes/class-bws-support.php` — dashboard widget + support page + email sending.
- `includes/class-bws-branding.php` — admin footer branding + admin notice CSS hide output.
- `includes/class-bws-hardening.php` — security/performance/SEO toggles (safe defaults).
- `includes/class-bws-github-updater.php` — GitHub Releases update checker and package URL resolver.

> **Guardrail:** Any new feature should live inside an existing class (or a new `includes/class-bws-*.php`) and be included from `website-support.php`.

---

## Hook Safety (No Fatal Errors)

### Callback methods are API-stable
If a method is referenced in `add_action()` / `add_filter()`, WordPress will fatal if it cannot call it.

> **Guardrail:** Do **not** rename/remove a method that is used in hooks unless you update every reference and validate activation on a staging site.

---

## Settings Storage

Settings are stored as a single option array:
- Option key: `bw_support_settings` (`BWS_OPTION_KEY`)

Defaults:
- `BWS_Settings::get_defaults()`

Sanitization:
- `BWS_Settings::sanitize_settings()`

> **Guardrail:** When adding a new setting:
1. Add default in `get_defaults()`
2. Add sanitization entry in `sanitize_settings()`
3. Render the field in settings UI
4. Implement behavior in the appropriate class

---

## Tabbed Settings UI (Critical)

The settings screen uses tabs for navigation, but settings are stored in **one option array**.

> **Guardrail:** The settings form must preserve values across tabs.
- Preferred: keep **one form** containing all fields (hidden panels are still in the DOM) so saving never wipes other tabs.
- If ever changed to “one tab submits only its fields”, sanitization MUST merge with existing saved options.

---

## Dashboard Cleanup

Widget removal uses two passes to catch late-registered widgets:
1. `wp_dashboard_setup` pass
2. late pass on `admin_head-index.php`

Common widget IDs supported:
- WP Mail SMTP: `wp_mail_smtp_reports_widget_lite`
- Elementor: `e-dashboard-overview`, `e-dashboard-ally`

Custom IDs:
- `dashboard_remove_custom_widget_ids` (one per line)

---

## Admin Notice Hiding

Setting:
- `admin_notice_hide_selectors`

Output method:
- `BWS_Branding::output_admin_notice_hide_css()` runs on `admin_head`

Input supports:
- Full CSS selectors (e.g. `.notice.notice-info`, `#some-id`)
- Class-list shorthand: `class1 class2 class3` → `.class1.class2.class3`

---

## Hardening & Performance

Settings live under the “Hardening & Performance” tab and are implemented in `BWS_Hardening`.

Notable guardrails:
- **Force SSL admin** only redirects when `home_url()` is HTTPS (prevents loops on HTTP-only sites).
- Revision limiting uses `wp_revisions_to_keep` filter (does not rely on config constants).
- Attachment page redirects run on `template_redirect` and are bypassed in admin/AJAX.

---

## Release Workflow (GitHub Actions)

Workflow:
- `.github/workflows/release.yml`

Trigger:
- Push tag event matching `v*` (example: `v1.0.6`)

Output:
- GitHub Release + asset `website-support.zip` containing top-level `website-support/` folder

> **Guardrail:** Creating a release must create a **new tag**. Reusing an existing tag will not trigger the workflow.

---

## Release Checklist
Before tagging `vX.Y.Z`:
- [ ] `CHANGELOG.md` updated with `## [X.Y.Z] - YYYY-MM-DD`
- [ ] Plugin activates on staging (wp-admin + front-end loads)
- [ ] New files included from `website-support.php`
- [ ] New settings defaults + sanitization + UI fields added
- [ ] Dashboard cleanup still runs two passes
- [ ] Release ZIP contains `website-support/` root and required files

---

## Single Source of Truth for Version
- Source of truth is the Git tag: `vX.Y.Z`
- Workflow patches:
  - Plugin header `Version: X.Y.Z`
  - `BWS_VERSION` constant

> Do **not** manually edit version strings in multiple places. Tag the repo and let the workflow patch.
