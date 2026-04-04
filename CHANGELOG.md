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

## [1.0.5] - 2026-04-04
### Added
- Guardrails / drift documentation to reduce regressions during refactors and ensure required files, hooks, and settings keys remain consistent between releases.
- Restored visibility of the “Admin Notice CSS Selectors to Hide” capability in the updated settings UI so site admins can hide theme/plugin notice banners by selector (one per line).

### Changed
- Settings UI wiring was updated to ensure the notice-selector field is saved and read from its dedicated option key (instead of being inadvertently coupled to other textarea fields during the settings UI refactor).

### Fixed
- Prevented admin-notice selector settings from being dropped/hidden after the settings UI modernization work (so notice hiding is available again and behaves predictably).
- Fixed fatal error: add missing admin notice CSS output method

---

## [1.0.4] - 2026-04-02
### Fixed
- Resolved a critical-error crash introduced during the settings UI refactor where required methods were missing / not present in the loaded class set on some installs.
- Hardened initialization so missing class methods cannot take down wp-admin (fail-safe behavior rather than fatal error).

---

## [1.0.3] - 2026-04-02
### Added
- Modernized Settings UI: tabbed navigation for major sections (Dashboard, Updates, Restrictions, Labels, Branding, Support, Login, White-Label).
- Settings page styling improvements (card layout + spacing + typography) for a cleaner, more professional admin experience.
- Sticky “Save Settings” bar on the settings screen to reduce missed saves on long pages.

### Changed
- Refactored settings page rendering to support the new tabbed layout while keeping existing settings keys and behavior unchanged.
- Enqueued admin CSS only on the plugin settings screen to avoid impacting other wp-admin pages.

### Fixed
- Reduced visual clutter and improved readability on the settings page (consistent alignment, grouping, and labels).

---

## [1.0.2] - 2026-04-01
### Added
- Admin Notice cleanup: optional “Admin Notice CSS Selectors to Hide” setting (one selector per line) for theme/plugin notices.
- Release workflow preflight checks (PHP lint + ZIP structure validation) before publishing a release asset.
- Release workflow guard to detect corrupted version patch artifacts in `website-support.php` (prevents parse errors like `$11.0.0$2` from shipping).

### Changed
- Hardened plugin bootstrap so admin-only features are initialized only in wp-admin (prevents front-end crashes from admin UI code).
- Release version patching is now performed safely (no fragile regex backreferences) so plugin header + `BWS_VERSION` are updated cleanly from the tag.
- Release packaging now builds a deterministic `website-support.zip` with correct folder structure (`website-support/…`) and excludes repo-only files.

### Fixed
- Added a late dashboard widget cleanup pass to remove widgets registered after `wp_dashboard_setup` (e.g., some Elementor/third-party widgets).
- Improved safety around custom CSS selector handling (sanitization + limits) to avoid breaking admin output.
- Prevented site-wide “critical error” outages caused by corrupted `website-support.php` produced during release packaging/version patching.

---

## [1.0.1] - 2026-03-?? 
### Changed
- General UI polish and admin cleanup improvements.

### Fixed
- Stabilized several “hide/redirect” admin restrictions so they behave consistently across wp-admin entry points (buttons + menu links).

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
