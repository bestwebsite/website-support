# Best Website Support

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
1. Push a new tag like `v1.0.6`
2. GitHub Actions builds and attaches `website-support.zip`
3. Client sites detect the update through the plugin updater (or WPRemote)

---

## Support
Best Website  
https://bestwebsite.com  
support@bestwebsite.com
