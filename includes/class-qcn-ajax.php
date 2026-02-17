<?php
/**
 * QCN_Ajax – all AJAX endpoints.
 *
 * @package QuickContentNotes
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class QCN_Ajax {

    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) self::$instance = new self();
        return self::$instance;
    }

    private function __construct() {
        $actions = array(
            'qcn_update_status',
            'qcn_delete_note',
            'qcn_get_history',
            'qcn_get_note',
            'qcn_quick_save_note',
        );
        foreach ( $actions as $action ) {
            add_action( 'wp_ajax_' . $action, array( $this, str_replace( 'qcn_', 'handle_', $action ) ) );
        }
    }

    private function verify() {
        if ( ! check_ajax_referer( 'qcn_ajax', 'nonce', false ) || ! QCN_Meta_Box::can() ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'quick-content-notes' ) ), 403 );
        }
    }

    // ── Update status ─────────────────────────────────────────────────────────

    public function handle_update_status() {
        $this->verify();

        $post_id  = absint( $_POST['post_id'] ?? 0 );
        $status   = sanitize_key( $_POST['status'] ?? '' );
        $allowed  = array( 'active', 'in-progress', 'completed' );

        if ( ! $post_id || ! in_array( $status, $allowed, true ) ) {
            wp_send_json_error( array( 'message' => __( 'Invalid parameters.', 'quick-content-notes' ) ), 400 );
        }

        $old_status = get_post_meta( $post_id, '_qcn_note_status', true );
        update_post_meta( $post_id, '_qcn_note_status', $status );

        // Snapshot history
        QCN_DB::insert_history( $post_id, array(
            'content'     => get_post_meta( $post_id, '_qcn_note_content', true ),
            'color'       => get_post_meta( $post_id, '_qcn_note_color',   true ) ?: 'default',
            'status'      => $status,
            'assigned_to' => get_post_meta( $post_id, '_qcn_note_assigned', true ),
        ) );

        // Fire notification hook
        if ( $status === 'completed' && $old_status !== 'completed' ) {
            $assigned = (int) get_post_meta( $post_id, '_qcn_note_assigned', true );
            do_action( 'qcn_note_completed', $post_id, $assigned );
        }

        wp_send_json_success( array( 'status' => $status, 'post_id' => $post_id ) );
    }

    // ── Delete note ───────────────────────────────────────────────────────────

    public function handle_delete_note() {
        $this->verify();

        $post_id = absint( $_POST['post_id'] ?? 0 );
        if ( ! $post_id ) {
            wp_send_json_error( array( 'message' => __( 'Invalid post ID.', 'quick-content-notes' ) ), 400 );
        }

        delete_post_meta( $post_id, '_qcn_note_content' );
        delete_post_meta( $post_id, '_qcn_note_color' );
        delete_post_meta( $post_id, '_qcn_note_status' );
        delete_post_meta( $post_id, '_qcn_note_assigned' );

        wp_send_json_success( array( 'post_id' => $post_id ) );
    }

    // ── Get history ───────────────────────────────────────────────────────────

    public function handle_get_history() {
        $this->verify();

        $post_id = absint( $_POST['post_id'] ?? 0 );
        if ( ! $post_id ) {
            wp_send_json_error( array( 'message' => __( 'Invalid post ID.', 'quick-content-notes' ) ), 400 );
        }

        $rows = QCN_DB::get_history( $post_id, 10 );
        $html = '';

        if ( empty( $rows ) ) {
            $html = '<p class="qcn-muted">' . esc_html__( 'No history yet.', 'quick-content-notes' ) . '</p>';
        } else {
            $html .= '<ul class="qcn-history-list">';
            foreach ( $rows as $row ) {
                $user     = get_userdata( $row->user_id );
                $uname    = $user ? esc_html( $user->display_name ) : esc_html__( 'Unknown', 'quick-content-notes' );
                $time_ago = human_time_diff( strtotime( $row->changed_at ), current_time( 'timestamp' ) );
                $snippet  = mb_substr( $row->content, 0, 120 );
                $html    .= '<li class="qcn-history-item qcn-color-' . esc_attr( $row->color ) . '">';
                $html    .= '<span class="qcn-history-meta">' . esc_html( $uname ) . ' &bull; '
                           . esc_html( $time_ago ) . ' ' . esc_html__( 'ago', 'quick-content-notes' )
                           . ' &bull; <em>' . esc_html( $row->status ) . '</em></span>';
                $html    .= '<p>' . esc_html( $snippet ) . ( mb_strlen( $row->content ) > 120 ? '…' : '' ) . '</p>';
                $html    .= '</li>';
            }
            $html .= '</ul>';
        }

        wp_send_json_success( array( 'html' => $html ) );
    }

    // ── Get note (for admin-bar quick edit modal) ─────────────────────────────

    public function handle_get_note() {
        $this->verify();

        $post_id = absint( $_POST['post_id'] ?? 0 );
        if ( ! $post_id ) {
            wp_send_json_error( null, 400 );
        }

        wp_send_json_success( array(
            'content'  => get_post_meta( $post_id, '_qcn_note_content',  true ),
            'color'    => get_post_meta( $post_id, '_qcn_note_color',    true ) ?: 'default',
            'status'   => get_post_meta( $post_id, '_qcn_note_status',   true ) ?: 'active',
            'assigned' => get_post_meta( $post_id, '_qcn_note_assigned', true ),
        ) );
    }

    // ── Quick save note (from admin-bar modal) ────────────────────────────────

    public function handle_quick_save_note() {
        $this->verify();

        $post_id  = absint( $_POST['post_id'] ?? 0 );
        $content  = sanitize_textarea_field( wp_unslash( $_POST['content'] ?? '' ) );
        $color    = sanitize_key( $_POST['color']  ?? 'default' );
        $status   = sanitize_key( $_POST['status'] ?? 'active' );

        if ( ! $post_id ) {
            wp_send_json_error( null, 400 );
        }

        update_post_meta( $post_id, '_qcn_note_content', $content );
        update_post_meta( $post_id, '_qcn_note_color',   $color );
        update_post_meta( $post_id, '_qcn_note_status',  $status );

        QCN_DB::insert_history( $post_id, array(
            'content' => $content,
            'color'   => $color,
            'status'  => $status,
        ) );

        wp_send_json_success( array( 'saved' => true ) );
    }
}
