=== Quick Content Notes ===
Contributors:      milenstanchev
Tags:              admin notes, meta box, editorial, content management, team collaboration
Requires at least: 5.8
Tested up to:      6.7
Requires PHP:      7.4
Stable tag:        1.5.0
License:           GPLv2 or later
License URI:       https://www.gnu.org/licenses/gpl-2.0.html

Admin-only note-taking for WordPress with admin bar integration, note history, search, email notifications, templates and multi-user assignments.

== Description ==

**Quick Content Notes v1.5** is a powerful admin-only note system for WordPress teams.

**Core features:**

* Admin Notes meta box in post/page editor sidebar
* Admin Bar integration – see current note, change status in one click
* Dedicated "Content Notes" admin page with full dashboard
* 5 priority colour levels (Default, Important, Idea, Review, Info)
* 3 status levels: Active, In Progress, Completed
* Basic Markdown support (bold, italic, links, line breaks)

**New in v1.5:**

* Search & filter notes by content, priority, status, assignee
* Note version history – every save creates a snapshot
* Email notifications (on/off toggle) for completion and assignment events
* Quick status toggle directly from the posts list column
* Note templates – create reusable note starters
* Multi-user note assignments with avatar display

Links: https://stantchev.github.io/QuickContentNotes-WordPress-Plugin/ | https://github.com/stantchev/QuickContentNotes-WordPress-Plugin | https://stanchev.bg/

== Installation ==

1. Upload the `quick-content-notes` folder to `/wp-content/plugins/`.
2. Activate via **Plugins > Installed Plugins**.
3. The "Content Notes" menu item will appear in your admin sidebar.

Or use **Plugins > Add New > Upload Plugin** with the .zip file.

The history table is created automatically on activation.

== Frequently Asked Questions ==

= Are notes visible to visitors? =
No. Notes are stored as post meta and are never rendered on the front end.

= Who can see notes? =
Only users with `manage_options` capability (Administrators by default).

= Where is the note dashboard? =
In the WordPress admin sidebar under "Content Notes".

= How do email notifications work? =
Enable them in Content Notes > Settings. Emails are sent when a note is completed or assigned, using wp_mail().

= Does it create database tables? =
One custom table (`{prefix}qcn_note_history`) for version snapshots. It is removed on plugin uninstall.

== Changelog ==

= 1.5.0 =
* Added: Admin Bar integration with status dropdown and note count badge
* Added: Dedicated "Content Notes" admin dashboard page
* Added: Search and filter (by content, priority, status, assignee)
* Added: Note version history / versioning with per-user snapshots
* Added: Email notification system with on/off toggles
* Added: Quick status toggle from posts list (AJAX, no page reload)
* Added: Note templates with colour presets
* Added: Multi-user note assignments
* Added: "In Progress" third status level
* Improved: Plugin architecture split into focused classes
* Improved: Full i18n / l10n support

= 1.0.0 =
* Initial release

== Upgrade Notice ==

= 1.5.0 =
Major feature release. A new database table is created on activation for note history. No data migration needed from v1.0.
