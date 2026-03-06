# Best Website Support

**Best Website Support** is an internal WordPress plugin used by **Best Website** to streamline wp-admin for managed client sites and provide a clear, branded support pathway inside the dashboard.

It focuses on:
- reducing client-facing clutter and confusion
- preventing risky actions (plugin/theme editors, installs, updates screens, etc.)
- adding a branded **Website Support** experience (dashboard widget + sidebar page)
- maintaining consistent defaults across sites with per-site overrides

---

## Key Admin Pages

### Website Support (client-facing)
- **Sidebar page:** `wp-admin/admin.php?page=bw-support`
- **Dashboard widget:** shown on `wp-admin/index.php`

### Settings (admin-only)
- **Settings page:** `wp-admin/admin.php?page=bw-settings`

By default, the Settings link is visible under **Settings → Website Support** after install.
You can hide it (direct URL only) via the white-label settings.

---

## Features

### Dashboard cleanup
Removes common default dashboard widgets (configurable), such as:
- Quick Draft
- WordPress Events & News
- Activity
- At a Glance
- Site Health
- (Optional) Welcome panel

Also supports removing custom plugin dashboard widgets by ID.

### Update UI cleanup
Reduces update-related noise in wp-admin:
- hides update nags and update badges
- hides plugin update rows/messages
- hides auto-update column/links
- hides “Plugins → Update Available” tab
- can hide/redirect the core Updates screen

> Updates are still managed normally by the Best Website team (e.g. via WPremote), but client-facing UI noise is minimized.

### Admin restrictions & menu cleanup
Optional restrictions (checkboxes) to prevent risky actions:
- hide plugin/theme file editors
- hide plugin/theme add/upload UI
- hide theme switching/theme pages
- hide plugin delete links
- hide Tools / Comments menus (and optionally Settings/Users/Plugins/Appearance)
- hide additional menus by slug (top-level or submenu)

### Label renaming
Optional renaming for:
- Posts / Pages / Media
- CPT menu labels (via mapping)

### Branding
- replaces admin footer text with Best Website-managed messaging
- branded Website Support header/content (logo URL optional)
- optional login page branding (logo/colors/help text)

### Website Support widget + page
A branded support form available inside wp-admin:
- Topic dropdown (configurable list)
- Message
- Name + Email (prefilled)
- Sends to configurable support address (default `support@bestwebsite.com`)
- Includes optional diagnostics metadata (enabled by default)

**Subject format:**
- `[%TOPIC%] New Message from %NAME%`

---

## Defaults (safe-by-default)
Most cleanup/restriction options are enabled by default to reduce client confusion.
Per-site exceptions can be made via the settings page.

For the authoritative, detailed list of defaults and settings keys, see:
- `docs/PLUGIN_SPEC.md`

---

## Releases & Updates (GitHub → Client Sites)

This plugin is updated via **GitHub Releases** and a built-in updater. The workflow is:

1. Update `CHANGELOG.md` with the new version section.
2. Push a tag: `vX.Y.Z`
3. GitHub Actions builds `website-support.zip` and creates a Release.
4. Client sites see the update via normal WordPress update mechanisms (WPremote-friendly).

### Release workflow
- Workflow file: `.github/workflows/release.yml`
- Trigger: tag push matching `v*.*.*`
- Workflow:
  - validates required files & changelog entry
  - patches version in `website-support.php`
  - builds `website-support.zip` (correct folder name)
  - publishes a GitHub Release with the ZIP asset
  - syncs version back to `main`

---

## Documentation (source of truth)
- **Plugin spec / drift guard:** `docs/PLUGIN_SPEC.md`
- **Changelog:** `CHANGELOG.md`
- (Optional) Release guide: `RELEASE.md` (if added)

---

## Notes
- Support emails use `wp_mail()`. Some hosts may require SMTP configuration for reliable delivery.
- Inventory of sites using the plugin is tracked via **WPremote**.

---

© Best Website — https://bestwebsite.com
