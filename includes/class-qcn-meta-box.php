<?php
/**
 * QCN_Meta_Box – post editor meta box.
 *
 * @package QuickContentNotes
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class QCN_Meta_Box {

    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) self::$instance = new self();
        return self::$instance;
    }

    private function __construct() {
        add_action( 'add_meta_boxes', array( $this, 'register' ) );
        add_action( 'save_post',      array( $this, 'save' ), 10, 2 );
    }

    public static function can() {
        return current_user_can( 'manage_options' );
    }

    /** Supported post types */
    public static function post_types() {
        return apply_filters( 'qcn_post_types', array( 'post', 'page' ) );
    }

    public function register() {
        if ( ! self::can() ) return;
        foreach ( self::post_types() as $pt ) {
            add_meta_box(
                'qcn_admin_notes',
                '<span class="dashicons dashicons-edit-page" style="vertical-align:middle;margin-right:4px"></span> ' . esc_html__( 'Admin Notes', 'quick-content-notes' ),
                array( $this, 'render' ),
                $pt, 'side', 'high'
            );
        }
    }

    public function render( $post ) {
        wp_nonce_field( 'qcn_save_' . $post->ID, 'qcn_nonce' );

        $content    = get_post_meta( $post->ID, '_qcn_note_content',  true ) ?: '';
        $color      = get_post_meta( $post->ID, '_qcn_note_color',    true ) ?: 'default';
        $status     = get_post_meta( $post->ID, '_qcn_note_status',   true ) ?: 'active';
        $assigned   = get_post_meta( $post->ID, '_qcn_note_assigned', true ) ?: '';
        $history    = QCN_DB::get_history( $post->ID, 5 );
        $templates  = get_option( 'qcn_templates', array() );
        $admins     = get_users( array( 'role__in' => array( 'administrator', 'editor' ), 'fields' => array( 'ID', 'display_name' ) ) );

        include QCN_PLUGIN_DIR . 'templates/meta-box.php';
    }

    public function save( $post_id, $post ) {
        if ( ! isset( $_POST['qcn_nonce'] ) || ! wp_verify_nonce( $_POST['qcn_nonce'], 'qcn_save_' . $post_id ) ) return;
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        if ( ! self::can() ) return;

        $pt_obj = get_post_type_object( $post->post_type );
        if ( ! $pt_obj || ! current_user_can( $pt_obj->cap->edit_post, $post_id ) ) return;

        $old_content = get_post_meta( $post_id, '_qcn_note_content', true );
        $new_content = sanitize_textarea_field( wp_unslash( $_POST['qcn_note_content'] ?? '' ) );
        $color       = sanitize_key( $_POST['qcn_note_color']    ?? 'default' );
        $status      = sanitize_key( $_POST['qcn_note_status']   ?? 'active' );
        $assigned    = absint( $_POST['qcn_note_assigned']        ?? 0 );
        $template_id = sanitize_key( $_POST['qcn_template_used'] ?? '' );

        $allowed_colors   = array( 'default', 'red', 'yellow', 'green', 'blue' );
        $allowed_statuses = array( 'active', 'in-progress', 'completed' );

        if ( ! in_array( $color,  $allowed_colors,   true ) ) $color  = 'default';
        if ( ! in_array( $status, $allowed_statuses, true ) ) $status = 'active';

        update_post_meta( $post_id, '_qcn_note_content',  $new_content );
        update_post_meta( $post_id, '_qcn_note_color',    $color );
        update_post_meta( $post_id, '_qcn_note_status',   $status );
        update_post_meta( $post_id, '_qcn_note_assigned', $assigned );

        // Only snapshot if content changed
        if ( $new_content !== $old_content || ! $old_content ) {
            QCN_DB::insert_history( $post_id, array(
                'content'     => $new_content,
                'color'       => $color,
                'status'      => $status,
                'assigned_to' => $assigned,
                'template_id' => $template_id,
            ) );
        }

        // Trigger notification if just completed
        $old_status = get_post_meta( $post_id, '_qcn_note_status', true );
        if ( $status === 'completed' && $old_status !== 'completed' ) {
            do_action( 'qcn_note_completed', $post_id, $assigned );
        }
    }
}
