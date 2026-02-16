<?php
/**
 * Plugin Name: Quick Content Notes
 * Plugin URI: https://github.com/stantchev/QuickContentNotes-WordPress-Plugin
 * Description: Admin-only note-taking meta box for posts and pages. Add private notes visible only to administrators for team coordination and reminders.
 * Version: 1.0.0
 * Author: Milen Stanchev
 * Author URI: https://stanchev.bg
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: quick-content-notes
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class Quick_Content_Notes {
    
    /**
     * Initialize the plugin
     */
    public function __construct() {
        // Add meta box to post editor
        add_action('add_meta_boxes', array($this, 'add_notes_meta_box'));
        
        // Save meta box data
        add_action('save_post', array($this, 'save_note_meta'));
        
        // Add admin styles
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        
        // Add custom columns to posts list
        add_filter('manage_posts_columns', array($this, 'add_note_column'));
        add_filter('manage_pages_columns', array($this, 'add_note_column'));
        add_action('manage_posts_custom_column', array($this, 'display_note_column'), 10, 2);
        add_action('manage_pages_custom_column', array($this, 'display_note_column'), 10, 2);
        
        // Make the column sortable
        add_filter('manage_edit-post_sortable_columns', array($this, 'make_note_column_sortable'));
        add_filter('manage_edit-page_sortable_columns', array($this, 'make_note_column_sortable'));
        
        // Add AJAX handler for note status updates
        add_action('wp_ajax_qcn_update_note_status', array($this, 'ajax_update_note_status'));
    }
    
    /**
     * Check if current user can manage notes (admin only)
     */
    private function can_manage_notes() {
        return current_user_can('manage_options');
    }
    
    /**
     * Add meta box to post/page editor
     */
    public function add_notes_meta_box() {
        // Only show to admins
        if (!$this->can_manage_notes()) {
            return;
        }
        
        $post_types = array('post', 'page');
        
        foreach ($post_types as $post_type) {
            add_meta_box(
                'qcn_admin_notes',
                '📝 Admin Notes (Private)',
                array($this, 'render_notes_meta_box'),
                $post_type,
                'side',
                'high'
            );
        }
    }
    
    /**
     * Render the meta box content
     */
    public function render_notes_meta_box($post) {
        // Add nonce for security
        wp_nonce_field('qcn_save_note', 'qcn_note_nonce');
        
        // Get existing values
        $note_content = get_post_meta($post->ID, '_qcn_note_content', true);
        $note_color = get_post_meta($post->ID, '_qcn_note_color', true);
        $note_status = get_post_meta($post->ID, '_qcn_note_status', true);
        
        // Default values
        if (empty($note_color)) {
            $note_color = 'default';
        }
        if (empty($note_status)) {
            $note_status = 'active';
        }
        
        ?>
        <div class="qcn-meta-box">
            <p class="qcn-info">
                <small>🔒 Only visible to administrators. Supports plain text and basic Markdown.</small>
            </p>
            
            <div class="qcn-field">
                <label for="qcn_note_content">Note:</label>
                <textarea 
                    id="qcn_note_content" 
                    name="qcn_note_content" 
                    rows="6" 
                    style="width: 100%;"
                    placeholder="Add your admin note here..."
                ><?php echo esc_textarea($note_content); ?></textarea>
            </div>
            
            <div class="qcn-field qcn-inline-fields">
                <div class="qcn-color-field">
                    <label for="qcn_note_color">Priority:</label>
                    <select id="qcn_note_color" name="qcn_note_color" class="qcn-color-select">
                        <option value="default" <?php selected($note_color, 'default'); ?>>⚪ Default</option>
                        <option value="red" <?php selected($note_color, 'red'); ?>>🔴 Important</option>
                        <option value="yellow" <?php selected($note_color, 'yellow'); ?>>🟡 Idea</option>
                        <option value="green" <?php selected($note_color, 'green'); ?>>🟢 Review</option>
                        <option value="blue" <?php selected($note_color, 'blue'); ?>>🔵 Info</option>
                    </select>
                </div>
                
                <div class="qcn-status-field">
                    <label for="qcn_note_status">Status:</label>
                    <select id="qcn_note_status" name="qcn_note_status">
                        <option value="active" <?php selected($note_status, 'active'); ?>>📌 Active</option>
                        <option value="completed" <?php selected($note_status, 'completed'); ?>>✅ Completed</option>
                    </select>
                </div>
            </div>
            
            <div class="qcn-markdown-help">
                <details>
                    <summary>Markdown formatting guide</summary>
                    <small>
                        <strong>**bold**</strong> → <strong>bold</strong><br>
                        <em>*italic*</em> → <em>italic</em><br>
                        - List item<br>
                        [link](url) → hyperlink
                    </small>
                </details>
            </div>
        </div>
        <?php
    }
    
    /**
     * Save meta box data
     */
    public function save_note_meta($post_id) {
        // Security checks
        if (!isset($_POST['qcn_note_nonce']) || !wp_verify_nonce($_POST['qcn_note_nonce'], 'qcn_save_note')) {
            return;
        }
        
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        if (!$this->can_manage_notes()) {
            return;
        }
        
        // Save note content
        if (isset($_POST['qcn_note_content'])) {
            update_post_meta($post_id, '_qcn_note_content', sanitize_textarea_field($_POST['qcn_note_content']));
        }
        
        // Save note color
        if (isset($_POST['qcn_note_color'])) {
            $allowed_colors = array('default', 'red', 'yellow', 'green', 'blue');
            $color = sanitize_text_field($_POST['qcn_note_color']);
            if (in_array($color, $allowed_colors)) {
                update_post_meta($post_id, '_qcn_note_color', $color);
            }
        }
        
        // Save note status
        if (isset($_POST['qcn_note_status'])) {
            $allowed_statuses = array('active', 'completed');
            $status = sanitize_text_field($_POST['qcn_note_status']);
            if (in_array($status, $allowed_statuses)) {
                update_post_meta($post_id, '_qcn_note_status', $status);
            }
        }
    }
    
    /**
     * Enqueue admin styles and scripts
     */
    public function enqueue_admin_assets($hook) {
        // Only load on post editor and posts list pages
        if (!in_array($hook, array('post.php', 'post-new.php', 'edit.php'))) {
            return;
        }
        
        if (!$this->can_manage_notes()) {
            return;
        }
        
        // Inline CSS
        $css = "
        .qcn-meta-box {
            padding: 5px 0;
        }
        .qcn-info {
            background: #f0f6fc;
            padding: 8px;
            border-radius: 4px;
            margin: 0 0 12px 0;
            border-left: 3px solid #0073aa;
        }
        .qcn-field {
            margin-bottom: 12px;
        }
        .qcn-field label {
            display: block;
            font-weight: 600;
            margin-bottom: 4px;
        }
        .qcn-inline-fields {
            display: flex;
            gap: 10px;
        }
        .qcn-color-field,
        .qcn-status-field {
            flex: 1;
        }
        .qcn-color-select {
            width: 100%;
        }
        .qcn-markdown-help {
            margin-top: 8px;
        }
        .qcn-markdown-help summary {
            cursor: pointer;
            color: #0073aa;
            font-size: 12px;
        }
        .qcn-markdown-help small {
            display: block;
            margin-top: 8px;
            line-height: 1.6;
        }
        
        /* Column styling */
        .column-qcn_note {
            width: 200px;
        }
        .qcn-note-preview {
            position: relative;
            padding: 8px;
            border-radius: 4px;
            font-size: 12px;
            line-height: 1.4;
            max-height: 60px;
            overflow: hidden;
        }
        .qcn-note-preview.qcn-color-default { background: #f0f0f1; border-left: 3px solid #2271b1; }
        .qcn-note-preview.qcn-color-red { background: #fee; border-left: 3px solid #dc3232; }
        .qcn-note-preview.qcn-color-yellow { background: #ffeaa7; border-left: 3px solid #f1c40f; }
        .qcn-note-preview.qcn-color-green { background: #d4edda; border-left: 3px solid #28a745; }
        .qcn-note-preview.qcn-color-blue { background: #e7f3ff; border-left: 3px solid #007bff; }
        .qcn-note-preview.qcn-status-completed {
            opacity: 0.6;
            text-decoration: line-through;
        }
        .qcn-note-preview strong {
            font-weight: 600;
        }
        .qcn-note-status-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
            margin-top: 4px;
            background: #ddd;
        }
        .qcn-note-status-badge.completed {
            background: #d4edda;
            color: #155724;
        }
        .qcn-no-note {
            color: #999;
            font-style: italic;
            font-size: 12px;
        }
        ";
        
        wp_add_inline_style('common', $css);
        
        // Add JavaScript for AJAX status updates (if needed in future)
        $js = "
        jQuery(document).ready(function($) {
            // Future enhancement: quick status toggle from posts list
        });
        ";
        wp_add_inline_script('jquery', $js);
    }
    
    /**
     * Add custom column to posts list
     */
    public function add_note_column($columns) {
        if (!$this->can_manage_notes()) {
            return $columns;
        }
        
        // Insert after title
        $new_columns = array();
        foreach ($columns as $key => $value) {
            $new_columns[$key] = $value;
            if ($key === 'title') {
                $new_columns['qcn_note'] = '📝 Admin Note';
            }
        }
        return $new_columns;
    }
    
    /**
     * Display note content in custom column
     */
    public function display_note_column($column, $post_id) {
        if ($column !== 'qcn_note' || !$this->can_manage_notes()) {
            return;
        }
        
        $note_content = get_post_meta($post_id, '_qcn_note_content', true);
        $note_color = get_post_meta($post_id, '_qcn_note_color', true);
        $note_status = get_post_meta($post_id, '_qcn_note_status', true);
        
        if (empty($note_content)) {
            echo '<span class="qcn-no-note">No note</span>';
            return;
        }
        
        // Parse simple Markdown
        $preview = $this->parse_simple_markdown($note_content);
        
        // Truncate for preview
        if (strlen($preview) > 100) {
            $preview = substr($preview, 0, 100) . '...';
        }
        
        $color_class = !empty($note_color) ? 'qcn-color-' . $note_color : 'qcn-color-default';
        $status_class = ($note_status === 'completed') ? 'qcn-status-completed' : '';
        
        echo '<div class="qcn-note-preview ' . esc_attr($color_class) . ' ' . esc_attr($status_class) . '">';
        echo wp_kses_post($preview);
        
        if ($note_status === 'completed') {
            echo '<div class="qcn-note-status-badge completed">✅ Completed</div>';
        }
        
        echo '</div>';
    }
    
    /**
     * Make note column sortable
     */
    public function make_note_column_sortable($columns) {
        $columns['qcn_note'] = 'qcn_note';
        return $columns;
    }
    
    /**
     * Parse simple Markdown to HTML
     */
    private function parse_simple_markdown($text) {
        // Bold
        $text = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $text);
        
        // Italic
        $text = preg_replace('/\*(.+?)\*/', '<em>$1</em>', $text);
        
        // Links
        $text = preg_replace('/\[(.+?)\]\((.+?)\)/', '<a href="$2" target="_blank">$1</a>', $text);
        
        // Line breaks
        $text = nl2br($text);
        
        return $text;
    }
    
    /**
     * AJAX handler for updating note status
     */
    public function ajax_update_note_status() {
        check_ajax_referer('qcn_update_status', 'nonce');
        
        if (!$this->can_manage_notes()) {
            wp_send_json_error('Permission denied');
        }
        
        $post_id = intval($_POST['post_id']);
        $status = sanitize_text_field($_POST['status']);
        
        if (in_array($status, array('active', 'completed'))) {
            update_post_meta($post_id, '_qcn_note_status', $status);
            wp_send_json_success(array('status' => $status));
        }
        
        wp_send_json_error('Invalid status');
    }
}

// Initialize the plugin
new Quick_Content_Notes();
