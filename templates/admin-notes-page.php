<?php
/**
 * Admin Notes dashboard page template.
 *
 * Variables: $notes, $total, $pages, $paged, $search, $filter_col, $filter_stat, $filter_user, $admins
 *
 * @package QuickContentNotes
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$color_labels = array(
    'default' => array( 'label' => __( 'Default',   'quick-content-notes' ), 'emoji' => '⚪' ),
    'red'     => array( 'label' => __( 'Important', 'quick-content-notes' ), 'emoji' => '🔴' ),
    'yellow'  => array( 'label' => __( 'Idea',      'quick-content-notes' ), 'emoji' => '🟡' ),
    'green'   => array( 'label' => __( 'Review',    'quick-content-notes' ), 'emoji' => '🟢' ),
    'blue'    => array( 'label' => __( 'Info',      'quick-content-notes' ), 'emoji' => '🔵' ),
);
$status_labels = array(
    'active'      => array( 'label' => __( 'Active',      'quick-content-notes' ), 'emoji' => '📌' ),
    'in-progress' => array( 'label' => __( 'In Progress', 'quick-content-notes' ), 'emoji' => '🔄' ),
    'completed'   => array( 'label' => __( 'Completed',   'quick-content-notes' ), 'emoji' => '✅' ),
);
?>
<div class="wrap qcn-wrap">
    <h1 class="wp-heading-inline">
        <span class="dashicons dashicons-edit-page" style="vertical-align:middle;margin-right:6px;"></span>
        <?php esc_html_e( 'Content Notes', 'quick-content-notes' ); ?>
    </h1>
    <span class="qcn-version-badge">v<?php echo esc_html( QCN_VERSION ); ?></span>
    <hr class="wp-header-end">

    <?php if ( isset( $_GET['deleted'] ) ) : ?>
        <div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Note deleted.', 'quick-content-notes' ); ?></p></div>
    <?php endif; ?>

    <!-- ── Search & Filters ──────────────────────────────────────────────── -->
    <div class="qcn-filter-bar">
        <form method="get" action="" class="qcn-filter-form">
            <input type="hidden" name="page" value="qcn-notes">

            <div class="qcn-filter-group">
                <input type="search"
                       name="qcn_search"
                       class="qcn-search-input"
                       value="<?php echo esc_attr( $search ); ?>"
                       placeholder="<?php esc_attr_e( 'Search notes…', 'quick-content-notes' ); ?>">
            </div>

            <div class="qcn-filter-group">
                <select name="qcn_color">
                    <option value=""><?php esc_html_e( 'All priorities', 'quick-content-notes' ); ?></option>
                    <?php foreach ( $color_labels as $val => $info ) : ?>
                        <option value="<?php echo esc_attr( $val ); ?>" <?php selected( $filter_col, $val ); ?>>
                            <?php echo esc_html( $info['emoji'] . ' ' . $info['label'] ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="qcn-filter-group">
                <select name="qcn_status">
                    <option value=""><?php esc_html_e( 'All statuses', 'quick-content-notes' ); ?></option>
                    <?php foreach ( $status_labels as $val => $info ) : ?>
                        <option value="<?php echo esc_attr( $val ); ?>" <?php selected( $filter_stat, $val ); ?>>
                            <?php echo esc_html( $info['emoji'] . ' ' . $info['label'] ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <?php if ( ! empty( $admins ) ) : ?>
            <div class="qcn-filter-group">
                <select name="qcn_assigned">
                    <option value="0"><?php esc_html_e( 'All assignees', 'quick-content-notes' ); ?></option>
                    <?php foreach ( $admins as $u ) : ?>
                        <option value="<?php echo esc_attr( $u->ID ); ?>" <?php selected( $filter_user, $u->ID ); ?>>
                            <?php echo esc_html( $u->display_name ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>

            <button type="submit" class="button"><?php esc_html_e( 'Filter', 'quick-content-notes' ); ?></button>

            <?php if ( $search || $filter_col || $filter_stat || $filter_user ) : ?>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=qcn-notes' ) ); ?>" class="button">
                    <?php esc_html_e( '✕ Reset', 'quick-content-notes' ); ?>
                </a>
            <?php endif; ?>
        </form>

        <div class="qcn-filter-summary">
            <?php printf(
                /* translators: %d: number of notes */
                esc_html( _n( '%d note', '%d notes', $total, 'quick-content-notes' ) ),
                (int) $total
            ); ?>
        </div>
    </div>

    <!-- ── Notes Table ────────────────────────────────────────────────────── -->
    <?php if ( empty( $notes ) ) : ?>
        <div class="qcn-empty-state">
            <span class="dashicons dashicons-edit-page"></span>
            <p><?php esc_html_e( 'No notes found. Add a note from any post or page editor.', 'quick-content-notes' ); ?></p>
        </div>
    <?php else : ?>
    <table class="wp-list-table widefat fixed striped qcn-notes-table">
        <thead>
            <tr>
                <th class="qcn-col-post"><?php esc_html_e( 'Post / Page', 'quick-content-notes' ); ?></th>
                <th class="qcn-col-note"><?php esc_html_e( 'Note', 'quick-content-notes' ); ?></th>
                <th class="qcn-col-priority"><?php esc_html_e( 'Priority', 'quick-content-notes' ); ?></th>
                <th class="qcn-col-status"><?php esc_html_e( 'Status', 'quick-content-notes' ); ?></th>
                <th class="qcn-col-assigned"><?php esc_html_e( 'Assigned', 'quick-content-notes' ); ?></th>
                <th class="qcn-col-actions"><?php esc_html_e( 'Actions', 'quick-content-notes' ); ?></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ( $notes as $note ) :
            $note_color  = $note->note_color  ?: 'default';
            $note_status = $note->note_status ?: 'active';
            $assigned_id = (int) $note->note_assigned;
            $assigned_u  = $assigned_id ? get_userdata( $assigned_id ) : null;
            $cl          = $color_labels[ $note_color ]  ?? $color_labels['default'];
            $sl          = $status_labels[ $note_status ] ?? $status_labels['active'];
        ?>
            <tr class="qcn-row" data-post-id="<?php echo esc_attr( $note->post_id ); ?>">
                <td class="qcn-col-post">
                    <strong>
                        <a href="<?php echo esc_url( get_edit_post_link( $note->post_id ) ); ?>">
                            <?php echo esc_html( $note->post_title ?: __( '(no title)', 'quick-content-notes' ) ); ?>
                        </a>
                    </strong>
                    <br>
                    <span class="qcn-post-type-tag"><?php echo esc_html( $note->post_type ); ?></span>
                </td>

                <td class="qcn-col-note">
                    <div class="qcn-note-text qcn-color-<?php echo esc_attr( $note_color ); ?>">
                        <?php echo esc_html( mb_strlen( $note->note_content ) > 160
                            ? mb_substr( $note->note_content, 0, 160 ) . '…'
                            : $note->note_content ); ?>
                    </div>
                    <button type="button" class="button-link qcn-history-btn" data-post-id="<?php echo esc_attr( $note->post_id ); ?>">
                        🕐 <?php esc_html_e( 'History', 'quick-content-notes' ); ?>
                    </button>
                </td>

                <td class="qcn-col-priority">
                    <span class="qcn-priority-badge qcn-color-<?php echo esc_attr( $note_color ); ?>">
                        <?php echo esc_html( $cl['emoji'] . ' ' . $cl['label'] ); ?>
                    </span>
                </td>

                <td class="qcn-col-status">
                    <div class="qcn-quick-status" data-post-id="<?php echo esc_attr( $note->post_id ); ?>" data-current="<?php echo esc_attr( $note_status ); ?>">
                        <?php foreach ( $status_labels as $slug => $info ) : ?>
                            <button type="button"
                                class="qcn-stat-btn<?php echo ( $note_status === $slug ) ? ' qcn-btn-active' : ''; ?>"
                                data-status="<?php echo esc_attr( $slug ); ?>"
                                title="<?php echo esc_attr( $info['label'] ); ?>">
                                <?php echo esc_html( $info['emoji'] ); ?>
                            </button>
                        <?php endforeach; ?>
                        <span class="qcn-status-label"><?php echo esc_html( $sl['emoji'] . ' ' . $sl['label'] ); ?></span>
                    </div>
                </td>

                <td class="qcn-col-assigned">
                    <?php if ( $assigned_u ) : ?>
                        <span class="qcn-assignee">
                            <?php echo get_avatar( $assigned_u->ID, 20 ); ?>
                            <?php echo esc_html( $assigned_u->display_name ); ?>
                        </span>
                    <?php else : ?>
                        <span class="qcn-muted">—</span>
                    <?php endif; ?>
                </td>

                <td class="qcn-col-actions">
                    <a href="<?php echo esc_url( get_edit_post_link( $note->post_id ) . '#qcn_admin_notes' ); ?>"
                       class="button button-small"><?php esc_html_e( 'Edit', 'quick-content-notes' ); ?></a>
                    <button type="button"
                            class="button button-small qcn-delete-note"
                            data-post-id="<?php echo esc_attr( $note->post_id ); ?>">
                        <?php esc_html_e( 'Delete', 'quick-content-notes' ); ?>
                    </button>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <?php if ( $pages > 1 ) :
        echo paginate_links( array(
            'base'    => add_query_arg( 'paged', '%#%' ),
            'format'  => '',
            'current' => $paged,
            'total'   => $pages,
        ) );
    endif; ?>

    <?php endif; ?>
</div>

<!-- History modal -->
<div id="qcn-history-modal" class="qcn-modal" style="display:none;">
    <div class="qcn-modal-overlay"></div>
    <div class="qcn-modal-box">
        <button type="button" class="qcn-modal-close">✕</button>
        <h2><?php esc_html_e( 'Note History', 'quick-content-notes' ); ?></h2>
        <div id="qcn-history-content"><p><?php esc_html_e( 'Loading…', 'quick-content-notes' ); ?></p></div>
    </div>
</div>
