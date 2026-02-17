<?php
/**
 * Meta box template – post editor sidebar.
 *
 * Variables available: $post, $content, $color, $status, $assigned, $history, $templates, $admins
 *
 * @package QuickContentNotes
 */
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<div class="qcn-meta-box">

    <p class="qcn-info">
        <span class="dashicons dashicons-lock"></span>
        <span><?php esc_html_e( 'Visible only to admins. Supports Markdown.', 'quick-content-notes' ); ?></span>
    </p>

    <?php if ( ! empty( $templates ) ) : ?>
    <div class="qcn-field">
        <label for="qcn_template_picker"><?php esc_html_e( 'Load template:', 'quick-content-notes' ); ?></label>
        <select id="qcn_template_picker">
            <option value=""><?php esc_html_e( '— Choose template —', 'quick-content-notes' ); ?></option>
            <?php foreach ( $templates as $tpl ) : ?>
                <option value="<?php echo esc_attr( $tpl['content'] ); ?>"
                        data-color="<?php echo esc_attr( $tpl['color'] ); ?>"
                        data-id="<?php echo esc_attr( $tpl['id'] ); ?>">
                    <?php echo esc_html( $tpl['name'] ); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <input type="hidden" id="qcn_template_used" name="qcn_template_used" value="">
    </div>
    <?php endif; ?>

    <div class="qcn-field">
        <label for="qcn_note_content"><?php esc_html_e( 'Note:', 'quick-content-notes' ); ?></label>
        <textarea id="qcn_note_content" name="qcn_note_content" rows="6"
            placeholder="<?php esc_attr_e( 'Add your admin note here…', 'quick-content-notes' ); ?>"
        ><?php echo esc_textarea( $content ); ?></textarea>
    </div>

    <div class="qcn-inline-row">
        <div class="qcn-sub-field">
            <label for="qcn_note_color"><?php esc_html_e( 'Priority:', 'quick-content-notes' ); ?></label>
            <select id="qcn_note_color" name="qcn_note_color">
                <option value="default" <?php selected( $color, 'default' ); ?>>⚪ <?php esc_html_e( 'Default',   'quick-content-notes' ); ?></option>
                <option value="red"     <?php selected( $color, 'red' );     ?>>🔴 <?php esc_html_e( 'Important', 'quick-content-notes' ); ?></option>
                <option value="yellow"  <?php selected( $color, 'yellow' );  ?>>🟡 <?php esc_html_e( 'Idea',      'quick-content-notes' ); ?></option>
                <option value="green"   <?php selected( $color, 'green' );   ?>>🟢 <?php esc_html_e( 'Review',    'quick-content-notes' ); ?></option>
                <option value="blue"    <?php selected( $color, 'blue' );    ?>>🔵 <?php esc_html_e( 'Info',      'quick-content-notes' ); ?></option>
            </select>
        </div>

        <div class="qcn-sub-field">
            <label for="qcn_note_status"><?php esc_html_e( 'Status:', 'quick-content-notes' ); ?></label>
            <select id="qcn_note_status" name="qcn_note_status">
                <option value="active"      <?php selected( $status, 'active' );      ?>>📌 <?php esc_html_e( 'Active',      'quick-content-notes' ); ?></option>
                <option value="in-progress" <?php selected( $status, 'in-progress' ); ?>>🔄 <?php esc_html_e( 'In Progress', 'quick-content-notes' ); ?></option>
                <option value="completed"   <?php selected( $status, 'completed' );   ?>>✅ <?php esc_html_e( 'Completed',   'quick-content-notes' ); ?></option>
            </select>
        </div>
    </div>

    <?php if ( ! empty( $admins ) ) : ?>
    <div class="qcn-field">
        <label for="qcn_note_assigned"><?php esc_html_e( 'Assign to:', 'quick-content-notes' ); ?></label>
        <select id="qcn_note_assigned" name="qcn_note_assigned">
            <option value="0"><?php esc_html_e( '— Unassigned —', 'quick-content-notes' ); ?></option>
            <?php foreach ( $admins as $u ) : ?>
                <option value="<?php echo esc_attr( $u->ID ); ?>" <?php selected( $assigned, $u->ID ); ?>>
                    <?php echo esc_html( $u->display_name ); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <?php endif; ?>

    <div class="qcn-markdown-help">
        <details>
            <summary><?php esc_html_e( 'Markdown guide', 'quick-content-notes' ); ?></summary>
            <table class="qcn-md-table">
                <tr><td><code>**bold**</code></td><td>→</td><td><strong><?php esc_html_e( 'bold', 'quick-content-notes' ); ?></strong></td></tr>
                <tr><td><code>*italic*</code></td><td>→</td><td><em><?php esc_html_e( 'italic', 'quick-content-notes' ); ?></em></td></tr>
                <tr><td><code>- item</code></td><td>→</td><td><?php esc_html_e( 'list item', 'quick-content-notes' ); ?></td></tr>
                <tr><td><code>[text](url)</code></td><td>→</td><td><?php esc_html_e( 'link', 'quick-content-notes' ); ?></td></tr>
            </table>
        </details>
    </div>

    <?php if ( ! empty( $history ) ) : ?>
    <div class="qcn-history-toggle">
        <button type="button" class="button-link qcn-history-btn" data-post-id="<?php echo esc_attr( $post->ID ); ?>">
            🕐 <?php esc_html_e( 'View history', 'quick-content-notes' ); ?> (<?php echo count( $history ); ?>)
        </button>
        <div class="qcn-history-panel" style="display:none;"></div>
    </div>
    <?php endif; ?>

</div>
