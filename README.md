<p align="center">
  <img src="https://raw.githubusercontent.com/bestwebsite/website-support/master/assets/social/website-support-banner.svg"
       alt="Best Website Support — client admin cleanup, branding, and support tools for managed WordPress sites" />
</p>

# Best Website Support

[![Latest release](https://img.shields.io/github/v/release/bestwebsite/website-support)](../../releases)
[![Release date](https://img.shields.io/github/release-date/bestwebsite/website-support)](../../releases)
[![License: GPL-2.0+](https://img.shields.io/badge/license-GPL--2.0%2B-blue.svg)](LICENSE)
[![WordPress](https://img.shields.io/badge/WordPress-plugin-21759b.svg)](https://wordpress.org/)
[![PHP](https://img.shields.io/badge/PHP-%3E%3D7.4-777bb4.svg)](https://www.php.net/)
[![Maintained by Best Website](https://img.shields.io/badge/maintainer-Best%20Website-3AA0FF)](https://bestwebsite.com)

**Best Website Support** is an internal WordPress plugin used by **Best Website** to streamline wp-admin for managed client sites, apply safe hardening defaults, and provide a clear, branded support pathway inside the dashboard.

It focuses on:
- reducing client-facing clutter and confusion
- preventing risky actions (plugin/theme editors, installs, update screens, etc.)
- adding a branded **Website Support** experience (dashboard widget + sidebar page)
- applying safe security/performance defaults (optional + reversible)
- maintaining consistent defaults across sites with per-site overrides

---

## Key Admin Pages

### Website Support (client-facing)
- **Sidebar page:** `wp-admin/admin.php?page=bw-support`
- **Dashboard widget:** shown on `wp-admin/index.php`

### Settings (admin-only)
- **Settings page (UI):** `wp-admin/options-general.php?page=bw-settings`
- If the settings menu link is hidden, the page remains accessible by direct URL above.

---

## Features (high level)

### Admin cleanup
- Dashboard widget cleanup (core widgets + custom widget IDs)
- Elementor-aware widget removals (Overview + Accessibility) when Elementor is active
- Update UI cleanup (hide nags/badges/update rows; optionally hide Updates screen)
- Restrictions (hide plugin/theme editors, installs, deletes, theme switching)
- Menu cleanup + custom menu slug removal

### Branding & Support
- Replace admin footer text with Best Website branding
- Branded Website Support dashboard widget and sidebar page
- Support request form emails `support@bestwebsite.com` with optional diagnostics

### Hardening & Performance (optional + reversible)
- Force SSL for wp-admin when site uses HTTPS
- Disable XML-RPC pingbacks, Application Passwords, author enumeration
- Disable emojis, oEmbed discovery, Dashicons for visitors
- Limit revisions (default 10)
- Disable attachment pages (redirect to media file)
- Disable comments site-wide (optional comment feed disable)

---

## GitHub Releases Updates
This plugin supports updates from **GitHub Releases**.

Workflow:
1. Update `CHANGELOG.md` with the new version heading.
2. Push a new tag like `v1.0.7`.
3. GitHub Actions validates hook callbacks, patches the plugin version, and builds `website-support.zip`.
4. Client sites detect the update through the plugin updater (or WPRemote).

---

## Support
Best Website  
https://bestwebsite.com  
support@bestwebsite.com


## Stability notes
- Settings are cached per request to reduce repeated option lookups.
- Structured multiline fields such as menu slugs, widget IDs, selector lists, and CPT label maps are sanitized with field-specific parsing instead of generic textarea sanitization.
- Optional support email From overrides are scoped only to support-form sends.
- The release workflow now validates hooked callbacks and blocks placeholder plugin versions from shipping.
