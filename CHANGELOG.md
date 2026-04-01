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

## [1.0.2] - 2026-04-01
### Changed
- Hardened plugin bootstrap so admin-only features are only initialized in wp-admin (prevents front-end crashes from admin UI code).
- Added optional “Admin Notice CSS Selectors to Hide” setting (one selector per line) for theme/plugin notices.

### Fixed
- Added a late dashboard widget cleanup pass to remove widgets registered after `wp_dashboard_setup` (e.g., some Elementor/third-party widgets).
- Improved safety around custom CSS selector handling (sanitization + limits) to avoid breaking admin output.

---

## [1.0.1] - 2026-03-?? 
### Changed
- UI polish and admin cleanup improvements.

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
