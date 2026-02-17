# 📝 Quick Content Notes

<div align="center">

![Version](https://img.shields.io/badge/version-1.5.0-1a4fff?style=flat-square)
![WordPress](https://img.shields.io/badge/WordPress-5.8%2B-21759b?style=flat-square&logo=wordpress&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-7.4%2B-777bb4?style=flat-square&logo=php&logoColor=white)
![License](https://img.shields.io/badge/license-GPL%20v2-green?style=flat-square)
![Tested up to](https://img.shields.io/badge/tested%20up%20to-WP%206.7-blue?style=flat-square)

**Admin-only notes for WordPress teams — right where you work.**

Private, colour-coded notes on any post or page. View them in the admin bar, search them in a dedicated dashboard, track history, assign to teammates, get email alerts. No external tools. No page slowdown.

[📖 Documentation](https://stantchev.github.io/QuickContentNotes-WordPress-Plugin/) · [🐛 Report a Bug](https://github.com/stantchev/QuickContentNotes-WordPress-Plugin/issues) · [💡 Request a Feature](https://github.com/stantchev/QuickContentNotes-WordPress-Plugin/issues) · [👤 Author](https://stanchev.bg/)

</div>

---

## Table of Contents

- [Why Quick Content Notes?](#-why-quick-content-notes)
- [Features](#-features)
- [What's New in v1.5](#-whats-new-in-v15)
- [Screenshots](#-screenshots)
- [Installation](#-installation)
- [Usage](#-usage)
- [Priority Colours](#-priority-colours)
- [Markdown Support](#-markdown-support)
- [Email Notifications](#-email-notifications)
- [Note Templates](#-note-templates)
- [Security](#-security)
- [Technical Details](#-technical-details)
- [Customization](#-customization)
- [Changelog](#-changelog)
- [License](#-license)

---

## 🎯 Why Quick Content Notes?

Large WordPress sites run on teams. Teams need context — reminders, client feedback, SEO targets, editorial instructions. That context usually lives in a separate Slack thread, Google Doc, or someone's memory.

**Quick Content Notes puts it right inside the editor.**

| Without QCN | With QCN |
|---|---|
| Notes scattered across Slack, email, Docs | All notes attached to the exact post they belong to |
| No record of who changed what | Full version history with user & timestamp |
| Manually notifying teammates | Automatic email on completion or assignment |
| Checking notes one post at a time | Searchable dashboard with filters |

---

## ✨ Features

### Core (since v1.0)
- 🔒 **Admin-only visibility** — notes are stored as private post meta, never rendered on the front end
- 🖊️ **Meta box in post editor** — clean sidebar widget for any post or page
- 🎨 **5-level colour coding** — Default, Important, Idea, Review, Info
- ✅ **Status tracking** — Active · In Progress · Completed
- 📝 **Markdown support** — bold, italic, links, line breaks
- 📋 **Posts list column** — colour-coded note previews at a glance

### New in v1.5
- 🔔 **Admin bar integration** — live badge + status mini-menu on every admin page
- 🔍 **Notes dashboard** — search, filter by priority / status / assignee
- 🕐 **Version history** — every save creates a snapshot with user & timestamp
- 📧 **Email notifications** — per-event toggles (complete / assign)
- ⚡ **Quick status toggle** — AJAX buttons in the posts list, no page reload
- 📄 **Note templates** — reusable starters loadable in one click
- 👥 **Multi-user assignment** — assign notes to any admin or editor

---

## 🚀 What's New in v1.5

### Admin Bar Integration
A persistent **Notes** node appears in the WordPress admin bar on every page. It shows:
- A **red badge** with the count of non-completed notes
- A **preview snippet** of the current post's note (when on a post editor page)
- **One-click status change** — Active / In Progress / Completed — without leaving the page
- Quick links to the Notes dashboard and Settings

### Notes Dashboard
A dedicated **Content Notes** page under its own admin menu gives you a bird's-eye view of every note across your entire site:
- Full-text search across note content
- Filter by priority colour, status, or assigned user
- Inline AJAX quick-toggle buttons
- One-click history modal per note
- Delete notes without visiting the post editor

### Version History
Every time a note is saved (content changed), a snapshot is written to a custom database table with:
- The full note content at that point in time
- Which user saved it
- The colour and status at the time
- A human-readable timestamp ("3 hours ago")

Click the **History** link in the meta box or dashboard to browse the full edit trail in a modal.

### Email Notifications
Configure in **Content Notes → Settings**:

| Toggle | When it fires |
|---|---|
| Master on/off | Disables all notifications |
| Notify on Completed | Fires when status changes to Completed |
| Notify on Assign | Fires when a note is assigned to a user |

Assigned users are always notified at their profile email address, regardless of the "notification email" field.

### Note Templates
Create reusable note starters in **Content Notes → Settings**. Each template has a name, default priority colour, and pre-filled content. Load any template from the meta box dropdown — with an optional confirmation prompt if a note already exists.

Four defaults are included: **Needs Review**, **SEO Checklist**, **Content Idea**, **Scheduled Task**.

### Multi-User Assignment
Assign any note to an administrator or editor. The assignee's avatar and display name appear in the Notes dashboard and posts list. Assignment triggers an email notification (if enabled).

---

## 📸 Screenshots

> Screenshots are hosted in the `/assets` directory of this repository.

| | |
|---|---|
| ![Meta box in post editor](assets/screenshot-1.png) | ![Notes dashboard with search & filters](assets/screenshot-2.png) |
| *Meta box with template picker, assign dropdown and history link* | *Notes dashboard — search, filter, quick-toggle, history modal* |
| ![Admin bar dropdown](assets/screenshot-3.png) | ![Settings page — notifications & templates](assets/screenshot-4.png) |
| *Admin bar: badge + status mini-menu + note preview* | *Settings page — email toggles and template builder* |

---

## 📦 Installation

### Via WordPress Admin (recommended)

1. Download **[quick-content-notes-v1.5.zip](https://github.com/stantchev/QuickContentNotes-WordPress-Plugin/releases/latest)**
2. In your WordPress admin go to **Plugins → Add New → Upload Plugin**
3. Select the `.zip` file and click **Install Now**
4. Click **Activate Plugin**

The **Content Notes** menu item appears immediately in your admin sidebar.

### Manual Installation

```bash
# In your WordPress root
cd wp-content/plugins
unzip quick-content-notes-v1.5.zip
```

Then activate via **Plugins → Installed Plugins**.

### Via WP-CLI

```bash
wp plugin install quick-content-notes.zip --activate
```

> **Database note:** A single custom table (`{prefix}qcn_note_history`) is created on activation for version snapshots. It is removed cleanly on uninstall.

---

## 📖 Usage

### Adding a Note to a Post or Page

1. Open any post or page in the editor
2. Find the **Admin Notes** meta box in the right sidebar
3. *(Optional)* Load a template from the **Load template** dropdown
4. Write your note — Markdown is supported
5. Choose a **Priority** colour and **Status**
6. *(Optional)* Assign to a teammate
7. **Save / Update** the post — the note is saved automatically

### Changing Status from the Posts List

Each row in the posts list shows three emoji buttons:

| Button | Status set |
|---|---|
| 📌 | Active |
| 🔄 | In Progress |
| ✅ | Completed |

Click any button — the status updates via AJAX with no page reload.

### Using the Notes Dashboard

Go to **Content Notes → All Notes** in the admin sidebar.

- Type in the **search box** to filter by note content
- Use the **Priority**, **Status** and **Assignee** dropdowns to narrow results
- Click **🕐 History** on any row to open the version history modal
- Click **Edit** to jump straight to the post editor
- Click **Delete** to remove the note permanently

### Changing Status from the Admin Bar

On any admin page, click the **📝 Notes** item in the top bar to open the dropdown. When you are on a post editor page, the dropdown shows the current note and three status buttons. Changes are applied via AJAX immediately.

---

## 🎨 Priority Colours

| Colour | Slug | Intended use |
|---|---|---|
| ⚪ Default | `default` | General notes and everyday reminders |
| 🔴 Important | `red` | Urgent — requires immediate action |
| 🟡 Idea | `yellow` | Creative suggestions for future content |
| 🟢 Review | `green` | Ready for review, approval, or sign-off |
| 🔵 Info | `blue` | References, links, background context |

---

## 📝 Markdown Support

The meta box and dashboard render a safe subset of Markdown:

```
**bold text**          →  bold
*italic text*          →  italic
[Link text](https://…) →  clickable hyperlink (https/http only)
Line break             →  <br>
```

Full HTML is never output — all user content is escaped with `esc_html()` before parsing, so there is no XSS risk.

---

## 📧 Email Notifications

Notifications are sent using the native `wp_mail()` function with an HTML template. No third-party mailer is required.

**Configure at:** Content Notes → Settings & Templates → Email Notifications

```
Master toggle:       ON / OFF  (disables all notifications)
Notification email:  admin@example.com  (default: site admin email)
Notify on Completed: ON / OFF
Notify on Assign:    ON / OFF
```

Assigned users are notified at their WordPress profile email regardless of the "Notification email" field value.

---

## 📄 Note Templates

Templates are stored in `wp_options` as a serialised array and are never cached externally.

**Default templates included:**

| Name | Colour | Purpose |
|---|---|---|
| Needs Review | 🔴 Important | Editorial checklist before publishing |
| SEO Checklist | 🔵 Info | Keyword, meta description, internal links |
| Content Idea | 🟡 Idea | Idea capture with audience and deadline fields |
| Scheduled Task | 🟢 Review | Task with responsible person and date |

Add, edit, or remove templates at **Content Notes → Settings & Templates**.

---

## 🔒 Security

| Measure | Implementation |
|---|---|
| Capability check | `current_user_can('manage_options')` on every entry point |
| Nonce verification | Per-post nonce on meta box save; `check_ajax_referer()` on all AJAX endpoints |
| Data sanitization | `sanitize_textarea_field()`, `sanitize_key()`, `absint()` throughout |
| Output escaping | `esc_html()`, `esc_attr()`, `esc_url()`, `wp_kses()` on all output |
| Front-end isolation | Zero output on the public site — notes never leave the admin |
| SQL safety | All queries use `$wpdb->prepare()` with type-safe placeholders |

Notes are stored with underscore-prefixed meta keys (`_qcn_*`), which hides them from the standard Custom Fields UI.

---

## 🛠️ Technical Details

### Plugin Architecture

```
quick-content-notes/
├── quick-content-notes.php          Bootstrap, constants, hooks
├── readme.txt                       WordPress.org format
│
├── includes/
│   ├── class-qcn-db.php             Custom history table + query engine
│   ├── class-qcn-meta-box.php       Post editor meta box (Singleton)
│   ├── class-qcn-admin-bar.php      Admin bar node + dropdown
│   ├── class-qcn-admin-page.php     Menu, Notes dashboard, Settings
│   ├── class-qcn-columns.php        Posts list column + quick-toggle
│   ├── class-qcn-notifications.php  Email dispatch via wp_mail()
│   └── class-qcn-ajax.php           AJAX endpoints
│
├── templates/
│   ├── meta-box.php                 Editor sidebar HTML
│   ├── admin-notes-page.php         Dashboard HTML
│   └── admin-settings-page.php      Settings & Templates HTML
│
└── assets/
    ├── css/admin.css                ~600 lines, CSS custom properties
    └── js/admin.js                  ~250 lines, jQuery AJAX & UI
```

### Database

One custom table is created on activation:

```sql
CREATE TABLE {prefix}qcn_note_history (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    post_id     BIGINT UNSIGNED NOT NULL,
    user_id     BIGINT UNSIGNED NOT NULL DEFAULT 0,
    content     LONGTEXT        NOT NULL,
    color       VARCHAR(20)     NOT NULL DEFAULT 'default',
    status      VARCHAR(20)     NOT NULL DEFAULT 'active',
    assigned_to BIGINT UNSIGNED          DEFAULT NULL,
    template_id VARCHAR(60)              DEFAULT NULL,
    changed_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX (post_id),
    INDEX (user_id)
);
```

The table is dropped and all `_qcn_*` post meta is removed on plugin uninstall.

### Post Meta Keys

| Key | Type | Description |
|---|---|---|
| `_qcn_note_content` | `string` | Note body (raw, unsanitized storage) |
| `_qcn_note_color` | `string` | Priority slug: `default` `red` `yellow` `green` `blue` |
| `_qcn_note_status` | `string` | `active` · `in-progress` · `completed` |
| `_qcn_note_assigned` | `int` | User ID of the assigned admin/editor (0 = unassigned) |

### Requirements

| | Minimum | Recommended |
|---|---|---|
| WordPress | 5.8 | 6.5+ |
| PHP | 7.4 | 8.1+ |
| MySQL / MariaDB | 5.6 | 8.0+ |

---

## 🔧 Customization

### Add Support for Custom Post Types

Filter `qcn_post_types` anywhere in your theme or a mu-plugin:

```php
add_filter( 'qcn_post_types', function( $types ) {
    $types[] = 'product';   // WooCommerce products
    $types[] = 'event';     // Custom events CPT
    return $types;
} );
```

### Allow Editors to See Notes

Override the capability check (add to your `functions.php`):

```php
add_filter( 'qcn_required_capability', function() {
    return 'edit_posts'; // Administrators + Editors
} );
```

### Hook Into Note Events

```php
// Fires when a note is marked Completed
add_action( 'qcn_note_completed', function( $post_id, $assigned_user_id ) {
    // e.g. ping a project management tool
}, 10, 2 );

// Fires when a note is assigned to a user
add_action( 'qcn_note_assigned', function( $post_id, $assigned_to, $assigned_by ) {
    // e.g. post a Slack message
}, 10, 3 );
```

---

## 📋 Changelog

### v1.5.0 — February 2026

**New features**
- Admin bar integration with live badge, note preview and status dropdown
- Dedicated **Content Notes** admin page with full notes dashboard
- Full-text search and filters (priority, status, assignee) in the dashboard
- Note version history — every content change creates a timestamped snapshot
- Email notification system with master toggle and per-event controls
- AJAX quick status toggle directly in the posts list column
- Note templates with colour presets and a template builder in Settings
- Multi-user note assignments with avatar display in dashboard and posts list
- New **In Progress** status level (alongside Active and Completed)

**Improvements**
- Plugin architecture split into focused, single-responsibility classes
- Full i18n / l10n readiness with `load_textdomain()`
- Stricter data sanitization with `wp_unslash()` before `sanitize_*()`
- All SQL queries use `$wpdb->prepare()` with explicit type placeholders
- CSS refactored with custom properties for easy theming
- JS refactored into a single IIFE module pattern

### v1.0.0 — Initial release

- Admin-only meta box in post and page editor
- 5-level priority colour system
- Active / Completed status tracking
- Basic Markdown rendering (bold, italic, links)
- Colour-coded notes column in posts and pages list

---

## 🤝 Contributing

Contributions, bug reports and feature requests are welcome.

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/my-new-feature`
3. Commit your changes: `git commit -m 'Add some feature'`
4. Push to the branch: `git push origin feature/my-new-feature`
5. Open a Pull Request

Please follow [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/) for PHP and use meaningful commit messages.

---

## 📄 License

Licensed under the **GNU General Public License v2.0 or later**.

```
This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
```

Full license text: [https://www.gnu.org/licenses/gpl-2.0.html](https://www.gnu.org/licenses/gpl-2.0.html)

---

<div align="center">

Built with care by **[Milen Stanchev](https://stanchev.bg/)** for WordPress teams who need better content coordination.

[stanchev.bg](https://stanchev.bg/) · [Documentation](https://stantchev.github.io/QuickContentNotes-WordPress-Plugin/) · [GitHub](https://github.com/stantchev/QuickContentNotes-WordPress-Plugin)

</div>
