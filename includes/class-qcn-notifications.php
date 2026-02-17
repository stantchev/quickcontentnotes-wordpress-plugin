<?php
/**
 * QCN_Notifications – email notifications for note events.
 *
 * @package QuickContentNotes
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class QCN_Notifications {

    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) self::$instance = new self();
        return self::$instance;
    }

    private function __construct() {
        add_action( 'qcn_note_completed', array( $this, 'on_completed' ), 10, 2 );
        add_action( 'qcn_note_assigned',  array( $this, 'on_assigned' ),  10, 3 );
    }

    /** @return array Plugin settings */
    private function settings() {
        return wp_parse_args( get_option( 'qcn_settings', array() ), array(
            'email_notifications' => 0,
            'notify_email'        => get_option( 'admin_email' ),
            'notify_on_complete'  => 1,
            'notify_on_assign'    => 1,
        ) );
    }

    /**
     * Send email when a note is marked completed.
     *
     * @param int $post_id
     * @param int $assigned_user_id
     */
    public function on_completed( $post_id, $assigned_user_id = 0 ) {
        $s = $this->settings();
        if ( empty( $s['email_notifications'] ) || empty( $s['notify_on_complete'] ) ) return;

        $post    = get_post( $post_id );
        $post_url= get_edit_post_link( $post_id, 'raw' );
        $note    = get_post_meta( $post_id, '_qcn_note_content', true );
        $actor   = wp_get_current_user();

        $to      = $s['notify_email'];
        // Also notify assigned user if different
        if ( $assigned_user_id ) {
            $au = get_userdata( $assigned_user_id );
            if ( $au && $au->user_email !== $to ) {
                $to = array( $to, $au->user_email );
            }
        }

        $subject = sprintf(
            /* translators: %s: post title */
            __( '[QCN] Note completed: %s', 'quick-content-notes' ),
            $post ? $post->post_title : __( 'Unknown post', 'quick-content-notes' )
        );

        $body  = '<h2>' . esc_html__( 'Note marked as completed', 'quick-content-notes' ) . '</h2>';
        $body .= '<p><strong>' . esc_html__( 'Post:', 'quick-content-notes' ) . '</strong> ';
        $body .= $post ? '<a href="' . esc_url( $post_url ) . '">' . esc_html( $post->post_title ) . '</a>' : '—';
        $body .= '</p>';
        $body .= '<p><strong>' . esc_html__( 'Completed by:', 'quick-content-notes' ) . '</strong> '
               . esc_html( $actor->display_name ) . '</p>';
        $body .= '<p><strong>' . esc_html__( 'Note preview:', 'quick-content-notes' ) . '</strong><br>'
               . nl2br( esc_html( mb_substr( $note, 0, 300 ) ) ) . '</p>';
        $body .= '<p><a href="' . esc_url( $post_url ) . '">'
               . esc_html__( 'View in editor', 'quick-content-notes' ) . '</a></p>';

        $this->send( $to, $subject, $body );
    }

    /**
     * Send email when a note is assigned.
     *
     * @param int $post_id
     * @param int $assigned_to
     * @param int $assigned_by
     */
    public function on_assigned( $post_id, $assigned_to, $assigned_by ) {
        $s = $this->settings();
        if ( empty( $s['email_notifications'] ) || empty( $s['notify_on_assign'] ) ) return;

        $au = get_userdata( $assigned_to );
        if ( ! $au ) return;

        $post     = get_post( $post_id );
        $post_url = get_edit_post_link( $post_id, 'raw' );
        $actor    = get_userdata( $assigned_by );
        $note     = get_post_meta( $post_id, '_qcn_note_content', true );

        $subject  = sprintf(
            __( '[QCN] Note assigned to you: %s', 'quick-content-notes' ),
            $post ? $post->post_title : __( 'Unknown post', 'quick-content-notes' )
        );

        $body  = '<h2>' . esc_html__( 'You have been assigned a note', 'quick-content-notes' ) . '</h2>';
        $body .= '<p><strong>' . esc_html__( 'Post:', 'quick-content-notes' ) . '</strong> ';
        $body .= $post ? '<a href="' . esc_url( $post_url ) . '">' . esc_html( $post->post_title ) . '</a>' : '—';
        $body .= '</p>';
        $body .= '<p><strong>' . esc_html__( 'Assigned by:', 'quick-content-notes' ) . '</strong> '
               . esc_html( $actor ? $actor->display_name : '—' ) . '</p>';
        $body .= '<p><strong>' . esc_html__( 'Note preview:', 'quick-content-notes' ) . '</strong><br>'
               . nl2br( esc_html( mb_substr( $note, 0, 300 ) ) ) . '</p>';
        $body .= '<p><a href="' . esc_url( $post_url ) . '">'
               . esc_html__( 'View in editor', 'quick-content-notes' ) . '</a></p>';

        $this->send( $au->user_email, $subject, $body );
    }

    /**
     * Wrapper around wp_mail with HTML headers.
     *
     * @param string|array $to
     * @param string       $subject
     * @param string       $body
     */
    private function send( $to, $subject, $body ) {
        $site    = get_bloginfo( 'name' );
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $site . ' <' . get_option( 'admin_email' ) . '>',
        );
        $full_body = '<!DOCTYPE html><html><body style="font-family:sans-serif;color:#2c3338;max-width:600px;margin:auto;">'
                   . $body
                   . '<hr><small style="color:#999;">' . esc_html( $site ) . ' – Quick Content Notes</small>'
                   . '</body></html>';

        wp_mail( $to, $subject, $full_body, $headers );
    }
}
