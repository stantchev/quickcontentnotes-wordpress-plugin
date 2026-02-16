# Quick Content Notes - WordPress Plugin

**Admin-only note-taking meta box for WordPress posts and pages**

## 📋 Description

Quick Content Notes allows WordPress administrators to add private notes to posts and pages that are only visible in the admin panel. Perfect for team coordination, editing reminders, and content planning.

## ✨ Features

- **Admin-Only Visibility**: Notes are completely private and only visible to users with administrator capabilities
- **Meta Box in Editor**: Clean, intuitive note-taking interface in the post/page editor sidebar
- **Priority Color Coding**: 
  - ⚪ Default (blue)
  - 🔴 Important (red)
  - 🟡 Idea (yellow)
  - 🟢 Review (green)
  - 🔵 Info (light blue)
- **Status Tracking**: Mark notes as Active or Completed
- **Markdown Support**: Use basic Markdown formatting (bold, italic, links, lists)
- **Posts List Column**: See note previews directly in your posts/pages list
- **Lightweight**: Minimal performance impact, no database bloat

## 🚀 Installation

### Method 1: Manual Installation

1. Download the `quick-content-notes.php` file
2. Upload it to your WordPress plugins directory (`/wp-content/plugins/`)
3. Log in to your WordPress admin panel
4. Go to **Plugins** → **Installed Plugins**
5. Find "Quick Content Notes" and click **Activate**

### Method 2: As a Plugin Folder

1. Create a folder named `quick-content-notes` in `/wp-content/plugins/`
2. Place the `quick-content-notes.php` file inside this folder
3. Activate the plugin from the WordPress admin panel

## 📖 Usage

### Adding Notes to Posts/Pages

1. Edit any post or page
2. Look for the **"📝 Admin Notes (Private)"** meta box in the right sidebar
3. Enter your note in the text area
4. Select a priority color (optional)
5. Set status as Active or Completed
6. Save or update the post

### Markdown Formatting

The plugin supports basic Markdown:

- `**bold text**` → **bold text**
- `*italic text*` → *italic text*
- `[link text](https://example.com)` → clickable link
- Line breaks are automatically converted

### Viewing Notes in Posts List

- Notes appear in a dedicated "📝 Admin Note" column
- Color-coded based on priority
- Shows completion status
- Truncated preview (click edit to see full note)

## 🎨 Priority Colors Explained

- **Default (Blue)**: General notes and reminders
- **Important (Red)**: Urgent items requiring immediate attention
- **Idea (Yellow)**: Creative ideas or suggestions for content
- **Review (Green)**: Items ready for review or approval
- **Info (Blue)**: Informational notes or context

## 🔒 Security

- Only users with `manage_options` capability (administrators) can view and edit notes
- Notes are stored as post meta and are never displayed on the frontend
- Proper nonce verification and data sanitization
- No security risks for your public-facing website

## 💡 Use Cases

- **Content Planning**: Jot down ideas for future updates
- **Team Collaboration**: Leave instructions for other editors
- **Editorial Workflow**: Track content status and next steps
- **SEO Notes**: Remind yourself about keyword targets
- **Client Communications**: Store client feedback privately
- **Revision Tracking**: Note what changes were made and why

## 🛠️ Technical Details

- **Database Storage**: Uses WordPress post meta (`_qcn_note_content`, `_qcn_note_color`, `_qcn_note_status`)
- **Meta keys are prefixed with underscore**: Hidden from custom fields UI
- **Post Types Supported**: Posts and Pages (easily extensible to custom post types)
- **WordPress Version**: 5.0 or higher recommended
- **PHP Version**: 7.0 or higher

## 🔧 Customization

### Adding Support for Custom Post Types

Edit the plugin file and modify the `add_notes_meta_box()` function:

```php
$post_types = array('post', 'page', 'your-custom-post-type');
```

### Changing Who Can See Notes

By default, only administrators can see notes. To allow editors, modify the `can_manage_notes()` function:

```php
private function can_manage_notes() {
    return current_user_can('edit_posts'); // Allows editors
}
```

## 📝 Changelog

### Version 1.0.0
- Initial release
- Admin-only meta box with note editor
- Color coding system (5 priority levels)
- Status tracking (Active/Completed)
- Basic Markdown support
- Notes column in posts list
- Lightweight and secure implementation

## 🤝 Support

For issues, questions, or feature requests, please contact the plugin author or create an issue in the plugin repository.

## 📄 License

This plugin is licensed under the GPL v2 or later.

```
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
```

## 🎯 Future Enhancement Ideas

- Export notes to CSV
- Note search/filter functionality
- Note history/versioning
- Email notifications for completed notes
- Quick toggle for note status from posts list
- Note templates for common use cases
- Multi-user note assignments

---

**Made by Stanchev Digital for WordPress administrators who need better content coordination**
