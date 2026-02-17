<?php
/**
 * Settings & Templates page template.
 *
 * Variables: $settings, $templates
 *
 * @package QuickContentNotes
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$s = wp_parse_args( $settings, array(
    'email_notifications' => 0,
    'notify_email'        => get_option( 'admin_email' ),
    'notify_on_complete'  => 1,
    'notify_on_assign'    => 1,
) );

$color_options = array(
    'default' => '⚪ ' . __( 'Default',   'quick-content-notes' ),
    'red'     => '🔴 ' . __( 'Important', 'quick-content-notes' ),
    'yellow'  => '🟡 ' . __( 'Idea',      'quick-content-notes' ),
    'green'   => '🟢 ' . __( 'Review',    'quick-content-notes' ),
    'blue'    => '🔵 ' . __( 'Info',      'quick-content-notes' ),
);
?>
<div class="wrap qcn-wrap">
    <h1 class="wp-heading-inline">
        <span class="dashicons dashicons-admin-settings" style="vertical-align:middle;margin-right:6px;"></span>
        <?php esc_html_e( 'Notes Settings & Templates', 'quick-content-notes' ); ?>
    </h1>
    <span class="qcn-version-badge">v<?php echo esc_html( QCN_VERSION ); ?></span>
    <hr class="wp-header-end">

    <?php if ( isset( $_GET['saved'] ) ) : ?>
        <div class="notice notice-success is-dismissible">
            <p><?php esc_html_e( 'Settings saved.', 'quick-content-notes' ); ?></p>
        </div>
    <?php endif; ?>

    <div class="qcn-settings-grid">

        <!-- ── Email Notifications ──────────────────────────────────────── -->
        <section class="qcn-settings-card">
            <h2 class="qcn-card-title">
                <span class="dashicons dashicons-email-alt"></span>
                <?php esc_html_e( 'Email Notifications', 'quick-content-notes' ); ?>
            </h2>
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                <?php wp_nonce_field( 'qcn_save_settings' ); ?>
                <input type="hidden" name="action" value="qcn_save_settings">

                <table class="form-table qcn-form-table">
                    <tr>
                        <th><?php esc_html_e( 'Enable Notifications', 'quick-content-notes' ); ?></th>
                        <td>
                            <label class="qcn-toggle">
                                <input type="checkbox" name="email_notifications" value="1"
                                    <?php checked( $s['email_notifications'], 1 ); ?>>
                                <span class="qcn-toggle-slider"></span>
                            </label>
                            <p class="description"><?php esc_html_e( 'Master on/off switch for all email notifications.', 'quick-content-notes' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="notify_email"><?php esc_html_e( 'Notification Email', 'quick-content-notes' ); ?></label></th>
                        <td>
                            <input type="email" id="notify_email" name="notify_email"
                                   value="<?php echo esc_attr( $s['notify_email'] ); ?>"
                                   class="regular-text">
                            <p class="description"><?php esc_html_e( 'Default recipient. Assigned users are also notified automatically.', 'quick-content-notes' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Notify When', 'quick-content-notes' ); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="notify_on_complete" value="1"
                                    <?php checked( $s['notify_on_complete'], 1 ); ?>>
                                <?php esc_html_e( 'Note is marked Completed', 'quick-content-notes' ); ?>
                            </label>
                            <br><br>
                            <label>
                                <input type="checkbox" name="notify_on_assign" value="1"
                                    <?php checked( $s['notify_on_assign'], 1 ); ?>>
                                <?php esc_html_e( 'Note is assigned to a user', 'quick-content-notes' ); ?>
                            </label>
                        </td>
                    </tr>
                </table>

                <?php submit_button( __( 'Save Notification Settings', 'quick-content-notes' ) ); ?>
            </form>
        </section>

        <!-- ── Note Templates ───────────────────────────────────────────── -->
        <section class="qcn-settings-card">
            <h2 class="qcn-card-title">
                <span class="dashicons dashicons-media-document"></span>
                <?php esc_html_e( 'Note Templates', 'quick-content-notes' ); ?>
            </h2>
            <p class="description" style="margin-bottom:16px;">
                <?php esc_html_e( 'Create reusable note templates that editors can quickly insert from the post editor.', 'quick-content-notes' ); ?>
            </p>

            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" id="qcn-templates-form">
                <?php wp_nonce_field( 'qcn_save_templates' ); ?>
                <input type="hidden" name="action" value="qcn_save_templates">

                <div id="qcn-templates-list">
                    <?php if ( empty( $templates ) ) :
                        // Seed with defaults on first load
                        $defaults = array(
                            array( 'id' => 'review',    'name' => __( 'Needs Review',   'quick-content-notes' ), 'content' => __( "**Action required:** Please review this content before publishing.\n\n- Check facts\n- Verify links\n- Confirm tone", 'quick-content-notes' ), 'color' => 'red' ),
                            array( 'id' => 'seo',       'name' => __( 'SEO Checklist',  'quick-content-notes' ), 'content' => __( "**SEO tasks:**\n\n- [ ] Target keyword: \n- [ ] Meta description updated\n- [ ] Internal links added\n- [ ] Image alt text added", 'quick-content-notes' ), 'color' => 'blue' ),
                            array( 'id' => 'idea',      'name' => __( 'Content Idea',   'quick-content-notes' ), 'content' => __( "**Idea:** \n\nContext: \nTarget audience: \nDeadline: ", 'quick-content-notes' ), 'color' => 'yellow' ),
                            array( 'id' => 'scheduled', 'name' => __( 'Scheduled Task', 'quick-content-notes' ), 'content' => __( "**Scheduled for:** \n\nTask: \nResponsible: ", 'quick-content-notes' ), 'color' => 'green' ),
                        );
                    else :
                        $defaults = $templates;
                    endif;

                    foreach ( $defaults as $i => $tpl ) : ?>
                        <div class="qcn-tpl-row" data-index="<?php echo (int) $i; ?>">
                            <div class="qcn-tpl-header">
                                <input type="text"
                                       name="templates[<?php echo (int) $i; ?>][name]"
                                       value="<?php echo esc_attr( $tpl['name'] ); ?>"
                                       placeholder="<?php esc_attr_e( 'Template name', 'quick-content-notes' ); ?>"
                                       class="regular-text qcn-tpl-name">
                                <select name="templates[<?php echo (int) $i; ?>][color]" class="qcn-tpl-color">
                                    <?php foreach ( $color_options as $val => $label ) : ?>
                                        <option value="<?php echo esc_attr( $val ); ?>"
                                            <?php selected( $tpl['color'] ?? 'default', $val ); ?>>
                                            <?php echo esc_html( $label ); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="button" class="button-link qcn-remove-tpl" title="<?php esc_attr_e( 'Remove', 'quick-content-notes' ); ?>">✕</button>
                            </div>
                            <textarea
                                name="templates[<?php echo (int) $i; ?>][content]"
                                rows="4"
                                class="widefat qcn-tpl-content"
                                placeholder="<?php esc_attr_e( 'Template content (supports Markdown)', 'quick-content-notes' ); ?>"
                            ><?php echo esc_textarea( $tpl['content'] ); ?></textarea>
                        </div>
                    <?php endforeach; ?>
                </div>

                <button type="button" id="qcn-add-template" class="button">
                    + <?php esc_html_e( 'Add Template', 'quick-content-notes' ); ?>
                </button>

                <?php submit_button( __( 'Save Templates', 'quick-content-notes' ) ); ?>
            </form>
        </section>

    </div><!-- .qcn-settings-grid -->

    <!-- ── Plugin info ──────────────────────────────────────────────────── -->
    <section class="qcn-settings-card qcn-about-card">
        <h2 class="qcn-card-title">
            <span class="dashicons dashicons-info"></span>
            <?php esc_html_e( 'About Quick Content Notes', 'quick-content-notes' ); ?>
        </h2>
        <div class="qcn-about-grid">
            <div>
                <p><strong><?php esc_html_e( 'Version:', 'quick-content-notes' ); ?></strong> <?php echo esc_html( QCN_VERSION ); ?></p>
                <p><strong><?php esc_html_e( 'Author:', 'quick-content-notes' ); ?></strong>
                    <a href="https://stanchev.bg/" target="_blank" rel="noopener">Milen Stanchev</a></p>
            </div>
            <div>
                <p>
                    <a href="https://stantchev.github.io/QuickContentNotes-WordPress-Plugin/" target="_blank" rel="noopener" class="button">
                        📖 <?php esc_html_e( 'Documentation', 'quick-content-notes' ); ?>
                    </a>
                    <a href="https://github.com/stantchev/QuickContentNotes-WordPress-Plugin" target="_blank" rel="noopener" class="button">
                        ⭐ <?php esc_html_e( 'GitHub', 'quick-content-notes' ); ?>
                    </a>
                </p>
            </div>
        </div>
    </section>

</div>

<!-- Template row prototype (hidden, cloned by JS) -->
<script type="text/template" id="qcn-tpl-prototype">
<div class="qcn-tpl-row" data-index="__INDEX__">
    <div class="qcn-tpl-header">
        <input type="text" name="templates[__INDEX__][name]" value="" placeholder="<?php esc_attr_e( 'Template name', 'quick-content-notes' ); ?>" class="regular-text qcn-tpl-name">
        <select name="templates[__INDEX__][color]" class="qcn-tpl-color">
            <?php foreach ( $color_options as $val => $label ) : ?>
                <option value="<?php echo esc_attr( $val ); ?>"><?php echo esc_html( $label ); ?></option>
            <?php endforeach; ?>
        </select>
        <button type="button" class="button-link qcn-remove-tpl" title="<?php esc_attr_e( 'Remove', 'quick-content-notes' ); ?>">✕</button>
    </div>
    <textarea name="templates[__INDEX__][content]" rows="4" class="widefat qcn-tpl-content" placeholder="<?php esc_attr_e( 'Template content (supports Markdown)', 'quick-content-notes' ); ?>"></textarea>
</div>
</script>
