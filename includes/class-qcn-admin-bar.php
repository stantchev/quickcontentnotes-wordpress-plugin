<?php
/**
 * QCN_Admin_Bar – adds Quick Content Notes to the WordPress admin bar.
 *
 * Shows on every admin page:
 *  • Post-context pages: current note preview + quick status toggle
 *  • All admin pages:    "Add / Edit Note" + "All Notes" links
 *
 * @package QuickContentNotes
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class QCN_Admin_Bar {

    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) self::$instance = new self();
        return self::$instance;
    }

    private function __construct() {
        // Priority 100 so we appear after native nodes
        add_action( 'admin_bar_menu', array( $this, 'add_nodes' ), 100 );
    }

    public function add_nodes( WP_Admin_Bar $bar ) {
        if ( ! QCN_Meta_Box::can() ) return;

        // ── Root node ─────────────────────────────────────────────────────────
        $bar->add_node( array(
            'id'    => 'qcn-root',
            'title' => '<span class="ab-icon dashicons dashicons-edit-page"></span>'
                     . '<span class="ab-label">' . esc_html__( 'Notes', 'quick-content-notes' ) . '</span>'
                     . $this->unseen_badge(),
            'href'  => admin_url( 'admin.php?page=qcn-notes' ),
            'meta'  => array( 'class' => 'qcn-ab-root' ),
        ) );

        // ── Current-post context ───────────────────────────────────────────────
        $post_id = $this->current_post_id();

        if ( $post_id ) {
            $note_content = get_post_meta( $post_id, '_qcn_note_content', true );
            $note_status  = get_post_meta( $post_id, '_qcn_note_status',  true ) ?: 'active';
            $note_color   = get_post_meta( $post_id, '_qcn_note_color',   true ) ?: 'default';

            // Preview snippet
            $snippet = $note_content
                ? ( mb_strlen( $note_content ) > 60 ? mb_substr( $note_content, 0, 60 ) . '…' : $note_content )
                : esc_html__( 'No note yet for this post.', 'quick-content-notes' );

            $bar->add_node( array(
                'parent' => 'qcn-root',
                'id'     => 'qcn-current-preview',
                'title'  => '<span class="qcn-ab-dot qcn-dot-' . esc_attr( $note_color ) . '"></span>'
                           . esc_html( $snippet ),
                'href'   => get_edit_post_link( $post_id ) . '#qcn_admin_notes',
                'meta'   => array( 'class' => 'qcn-ab-preview' ),
            ) );

            // Status-change sub-items
            $bar->add_node( array(
                'parent' => 'qcn-root',
                'id'     => 'qcn-status-group',
                'title'  => esc_html__( 'Change Status', 'quick-content-notes' ),
                'href'   => '#',
                'meta'   => array( 'class' => 'qcn-ab-group-header' ),
            ) );

            $statuses = array(
                'active'      => '📌 ' . __( 'Active',       'quick-content-notes' ),
                'in-progress' => '🔄 ' . __( 'In Progress',  'quick-content-notes' ),
                'completed'   => '✅ ' . __( 'Completed',    'quick-content-notes' ),
            );
            foreach ( $statuses as $slug => $label ) {
                $active_class = ( $note_status === $slug ) ? ' qcn-ab-current-status' : '';
                $bar->add_node( array(
                    'parent' => 'qcn-root',
                    'id'     => 'qcn-set-status-' . $slug,
                    'title'  => $label,
                    'href'   => '#',
                    'meta'   => array(
                        'class'         => 'qcn-ab-status-btn' . $active_class,
                        'data-post-id'  => $post_id,
                        'data-status'   => $slug,
                    ),
                ) );
            }

            // Separator
            $bar->add_node( array(
                'parent' => 'qcn-root',
                'id'     => 'qcn-sep-1',
                'title'  => '<hr class="qcn-ab-sep">',
                'href'   => false,
            ) );

            // Edit note link
            $bar->add_node( array(
                'parent' => 'qcn-root',
                'id'     => 'qcn-edit-note',
                'title'  => '<span class="dashicons dashicons-edit"></span> '
                           . esc_html__( 'Edit Note', 'quick-content-notes' ),
                'href'   => get_edit_post_link( $post_id ) . '#qcn_admin_notes',
                'meta'   => array( 'class' => 'qcn-ab-action' ),
            ) );
        } else {
            // Not on a post page
            $bar->add_node( array(
                'parent' => 'qcn-root',
                'id'     => 'qcn-no-post',
                'title'  => esc_html__( 'Navigate to a post or page to add a note.', 'quick-content-notes' ),
                'href'   => false,
                'meta'   => array( 'class' => 'qcn-ab-muted' ),
            ) );
        }

        // ── Global links (always shown) ────────────────────────────────────────
        $bar->add_node( array(
            'parent' => 'qcn-root',
            'id'     => 'qcn-sep-2',
            'title'  => '<hr class="qcn-ab-sep">',
            'href'   => false,
        ) );

        $bar->add_node( array(
            'parent' => 'qcn-root',
            'id'     => 'qcn-all-notes',
            'title'  => '<span class="dashicons dashicons-list-view"></span> '
                       . esc_html__( 'All Notes', 'quick-content-notes' ),
            'href'   => admin_url( 'admin.php?page=qcn-notes' ),
            'meta'   => array( 'class' => 'qcn-ab-action' ),
        ) );

        $bar->add_node( array(
            'parent' => 'qcn-root',
            'id'     => 'qcn-settings-link',
            'title'  => '<span class="dashicons dashicons-admin-settings"></span> '
                       . esc_html__( 'Notes Settings', 'quick-content-notes' ),
            'href'   => admin_url( 'admin.php?page=qcn-settings' ),
            'meta'   => array( 'class' => 'qcn-ab-action' ),
        ) );
    }

    /** Detect current post ID from screen / query var */
    private function current_post_id() {
        if ( is_admin() ) {
            $screen = get_current_screen();
            if ( $screen && in_array( $screen->base, array( 'post', 'page' ), true ) ) {
                return absint( $_GET['post'] ?? get_the_ID() );
            }
        }
        return 0;
    }

    /** Badge with count of active non-completed notes */
    private function unseen_badge() {
        global $wpdb;
        $count = (int) $wpdb->get_var(
            "SELECT COUNT(DISTINCT post_id)
             FROM {$wpdb->postmeta}
             WHERE meta_key = '_qcn_note_status'
               AND meta_value != 'completed'
               AND meta_value != ''
               AND post_id IN (
                   SELECT post_id FROM {$wpdb->postmeta}
                   WHERE meta_key = '_qcn_note_content' AND meta_value != ''
               )"
        );
        if ( $count < 1 ) return '';
        return ' <span class="qcn-ab-badge">' . $count . '</span>';
    }
}
