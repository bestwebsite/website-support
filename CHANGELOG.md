# Changelog
All notable changes to **Best Website Support** will be documented in this file.

This project follows **Semantic Versioning** (MAJOR.MINOR.PATCH).  
Release tags are formatted as `vX.Y.Z`.

## [Unreleased]
### Added
- TBD

### Changed
- TBD

### Fixed
- TBD

---

## [1.0.6] - 2026-04-05
### Added
- New **Hardening & Performance** settings tab with practical security/performance toggles:
  - Force SSL for wp-admin (auto-enables only when the site home URL uses HTTPS).
  - Disable XML-RPC pingbacks.
  - Disable Application Passwords.
  - Block author enumeration (`?author=`).
  - Remove generator/version output.
  - Disable emojis, oEmbed discovery, and Dashicons for visitors.
  - Limit post revisions (default: 10; configurable).
  - Disable attachment pages (redirect to media file) for SEO hygiene.
  - Disable comments site-wide (with optional comment feed disable).
- Dashboard Cleanup:
  - Checkbox to remove **WP Mail SMTP** dashboard widget (`wp_mail_smtp_reports_widget_lite`).
  - Elementor-aware checkboxes (only shown when Elementor is active) to remove:
    - Elementor Overview (`e-dashboard-overview`)
    - Elementor Accessibility (`e-dashboard-ally`)
- Support:
  - Optional “Force outgoing From email to site domain” toggle (skips overrides when WP Mail SMTP is active).

### Changed
- Settings UI: reorganized Dashboard tab to include **Admin Notice CSS Selectors to Hide** alongside custom dashboard widget IDs.
- Admin notice selector handling now supports either full CSS selectors or space-delimited class lists (auto-normalized to `.class1.class2...`).

### Fixed
- Restored a clean, deterministic plugin bootstrap file (prevents corrupted release artifacts from taking sites down).
- Improved dashboard widget removal coverage across additional contexts (`advanced`, `column3`, `column4`) for better compatibility with third-party widgets.
- Fixed top-level menu hiding to support common pasted values (DOM IDs like `toplevel_page_*` / `menu-posts-*` and `admin.php?page=*`) by normalizing them into valid WordPress menu slugs before removal.
- Fixed release build failure caused by a misplaced `private` helper method in `BWS_Admin_Cleanup`, and improved top-level menu hiding by normalizing `toplevel_page_*` admin menu IDs into valid `remove_menu_page()` slugs.

---

## [1.0.1] - 2026-04-01
### Added
- Admin Notice cleanup: **Admin Notice CSS Selectors to Hide** (one CSS selector per line) to hide theme/plugin admin notices without hard-coding per-theme rules.

### Changed
- Dashboard widget removal now performs a **late removal pass on the Dashboard screen** to catch widgets added after `wp_dashboard_setup` (notably Elementor’s “Accessibility” dashboard widget).

### Fixed
- Release workflow: version patching no longer risks corrupting plugin PHP files during release packaging (prevents parse errors like `$11.0.0$2` in `website-support.php`).

---

## [1.0.0] - 2026-03-06
### Added
- Dashboard cleanup options (Quick Draft, Events/News, Activity, At a Glance, Site Health; optional Welcome panel).
- Update UI cleanup options (hide update nags, update badges, plugin update rows, auto-update column, “Update Available” tab).
- Admin restrictions and menu cleanup options (hide plugin/theme editors, plugin/theme add/upload, theme switching/pages, plugin delete links, Updates screen; hide Tools and Comments menus; optional hides for Settings/Users/Plugins/Appearance).
- Custom menu slug removal (top-level + submenu via `parent|submenu` format).
- Label renaming (Posts/Pages/Media) and CPT menu label overrides (`post_type|Menu Label|Add New Label`).
- Branding:
  - Admin footer replacement text.
  - Support branding fields (logo URL, widget intro, page intro).
- Website Support:
  - Dashboard widget + sidebar page with polished UI.
  - Support form (topic, message, name, email) sending via `wp_mail()` to configurable support email.
  - Subject format: `[%TOPIC%] New Message from %NAME%`.
  - Optional diagnostics metadata included by default.
- Login branding options (logo URL/link/title, background color, button color, help text).
- White-label options (hide settings page link, optionally hide plugin from Plugins list, etc.).
- GitHub Releases–based updates:
  - GitHub Action to build and publish release ZIP on tag (`vX.Y.Z`).
  - Plugin updater checks GitHub Releases API, prefers ZIP asset, falls back to `zipball_url`.

### Changed
- N/A

### Fixed
- N/A
