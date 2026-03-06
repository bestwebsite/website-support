# Best Website Support (Website Support plugin)

**Repository:** `bestwebsite/website-support`  
**Plugin folder:** `/wp-content/plugins/website-support/`  
**Plugin name:** Best Website Support  
**Client-facing menu label:** Website Support  
**Text domain:** `bw-support`  
**Option key:** `bw_support_settings`  
**Settings page slug:** `bw-settings`  
**Support page slug:** `bw-support`  
**Class prefix:** `BWS_`

This document is the canonical, up-to-date reference for how the plugin works, its features, defaults, workflows, and architectural decisions. When adding new features, use this file as the baseline to avoid drift.

---

## Goals

1. **Reduce client confusion** in wp-admin by removing clutter and risky actions.
2. **Provide a clear, branded support pathway** inside WordPress admin (Dashboard widget + sidebar page).
3. **Support per-site exceptions** via a settings page (checkbox-driven feature flags).
4. **White-label friendly** (optionally hide plugin UI, settings menu link, etc.).
5. **Easy managed updates at scale** via GitHub Releases + normal WordPress update flow (WP remote-friendly).

---

## Primary Admin Entry Points

### 1) Website Support dashboard widget
- Appears on `index.php` (Dashboard).
- Branded support card + contact email + support form.

### 2) Website Support sidebar page
- Appears as a top-level admin menu item.
- Uses the same support form as the dashboard widget.

### 3) Settings page (under Settings, optionally hidden)
- Default behavior: visible under **Settings** for easy access after install.
- Can be hidden by checking: **“Hide settings page in admin menu (direct URL only)”**
- Always accessible via direct URL (admin-only):
  - `/wp-admin/admin.php?page=bw-settings`

---

## Support Form Behavior

**Location:** Dashboard widget + sidebar page  
**Delivery:** Email via `wp_mail()`  
**Recipient:** `support@bestwebsite.com` by default (configurable)  
**Subject format:** `[%TOPIC%] New Message from %NAME%`  
**Reply-To:** uses submitted email when valid  
**Diagnostics metadata:** included by default (toggleable)

### Fields
- Topic dropdown (configurable list)
- Message
- Name (prefilled from current user)
- Email (prefilled from current user)

### Diagnostics included (default)
- Site name, site URL, admin URL
- Current user display name + email + roles
- Timestamp (site TZ + UTC)
- WP version, PHP version
- Theme name/version
- Locale, memory limit
- Screen ID and admin page URL (when available)
- Installed plugin count (when available)

---

## Features & Defaults

All settings are stored in a single options array: `bw_support_settings`.

### Dashboard Cleanup (default ON unless noted)
- ✅ Remove Quick Draft
- ✅ Remove WordPress Events & News
- ✅ Remove Activity
- ✅ Remove At a Glance
- ✅ Remove Site Health
- ⬜ Remove Welcome panel (default OFF)
- Custom dashboard widget IDs to remove (textarea; default empty)

### Update UI Cleanup (default ON)
- ✅ Hide update nag
- ✅ Hide plugin update rows/messages
- ✅ Hide update badges/counts
- ✅ Hide plugin auto-update column/links
- ✅ Hide Plugins “Update Available” tab
- ✅ Hide/redirect Updates screen

### Admin Restrictions & Menu Cleanup (default ON unless noted)
- ✅ Hide Plugin File Editor
- ✅ Hide Theme File Editor
- ✅ Hide Plugin Add New / Upload
- ✅ Hide Plugin Delete links
- ✅ Hide Theme Add New / Upload
- ✅ Hide Theme switching / Theme pages
- ✅ Hide Tools menu
- ✅ Hide Comments menu (default ON)
- ⬜ Hide Settings menu (default OFF)
- ⬜ Hide Users menu (default OFF)
- ⬜ Hide Plugins menu (default OFF)
- ⬜ Hide Appearance menu (default OFF)
- Custom top-level menu slugs to hide (textarea)
- Custom submenu slugs to hide (textarea; format parent|child)

### Label Renaming (default empty)
- Rename Posts (optional)
- Rename Pages (optional)
- Rename Media (optional)
- CPT label mapping (textarea format):
  - `post_type|Menu Label|Add New Label`

### Branding (default ON for footer)
- ✅ Replace admin footer text
- Footer text default: `Managed by Best Website • support@bestwebsite.com`
- Optional footer version text override
- Support logo URL (optional)
- Support widget intro text (default set)
- Support page intro text (default set)

### Website Support (default ON)
- ✅ Enable dashboard widget
- ✅ Enable support sidebar page
- Sidebar label default: `Website Support`
- Support email default: `support@bestwebsite.com`
- Topics default list (configurable)
- Success message default set
- Instructions default set
- ✅ Include diagnostics in emails

### Login Branding (default ON)
- ✅ Enable custom login branding
- Logo URL (optional)
- Logo link URL (default home URL)
- Logo title (default site name)
- Background color (default set)
- Button color (default set)
- Help text below login form (default set)

### Plugin Visibility / White-Label (default ON for whitelabel)
- ✅ Enable white-label behavior
- ⬜ Hide settings page in admin menu (direct URL only) (default OFF)
  - When checked, settings link disappears from Settings menu
- ⬜ Hide this plugin from Plugins list (advanced) (default OFF)
- ⬜ Hide plugin update row/badges when possible (default OFF)
- ⬜ Hide support page from admin bar shortcuts (future-safe) (default OFF)

---

## GitHub Updates Workflow (Source of Truth)

### Version source of truth
- **Git tag** is the source of truth, e.g. `v1.0.0`

### Release automation
- GitHub Action: `.github/workflows/release.yml`
- Trigger: pushing a tag matching `v*.*.*`
- Workflow does:
  1. Extract version from tag
  2. Patch plugin version in `website-support.php`:
     - plugin header `Version: X.Y.Z`
     - `define('BWS_VERSION', 'X.Y.Z')`
  3. Build `dist/website-support.zip` (with correct folder name)
  4. Create GitHub Release + upload ZIP asset
  5. Sync version patch back to `main` branch

### Updater behavior
- Plugin checks GitHub Releases API:
  - `https://api.github.com/repos/bestwebsite/website-support/releases/latest`
- Prefers a `.zip` asset if present.
- Falls back to `zipball_url` if no asset exists.
- Handles folder naming differences during install.

---

## Implementation Notes (Where things live)

- Settings + defaults: `includes/class-bws-settings.php`
- Support UI + email sending: `includes/class-bws-support.php`
- Dashboard widget cleanup: `includes/class-bws-dashboard.php`
- Update UI cleanup + CSS selectors: `includes/class-bws-admin-cleanup.php`
- Branding footer: `includes/class-bws-branding.php`
- GitHub updater: `includes/class-bws-github-updater.php`
- Bootstrap: `website-support.php` and `includes/class-bws-plugin.php`

---

## Operational Notes

- Support emails use `wp_mail()`. Some hosts require SMTP for reliable delivery.
- If a client site has mail issues, configure SMTP or plan future webhook-based delivery.

---

## Future Roadmap (non-v1)

- Topic-based routing (different recipients) and/or webhook delivery
- Presets (profiles for different client types)
- Admin bar shortcut improvements
- Debug/discovery mode (menu slugs/widget IDs/screen IDs)
- Export/import settings JSON
- Optional “check-in” inventory endpoint (if needed beyond WP remote)

---
