<?php
/**
 * QCN_Admin_Page – top-level admin menu with Notes dashboard + Settings.
 *
 * @package QuickContentNotes
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class QCN_Admin_Page {

    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) self::$instance = new self();
        return self::$instance;
    }

    private function __construct() {
        add_action( 'admin_menu',            array( $this, 'register_menu' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_action( 'admin_post_qcn_save_settings',  array( $this, 'save_settings' ) );
        add_action( 'admin_post_qcn_save_templates', array( $this, 'save_templates' ) );
    }

    public function register_menu() {
        if ( ! QCN_Meta_Box::can() ) return;

        add_menu_page(
            __( 'Content Notes', 'quick-content-notes' ),
            __( 'Content Notes', 'quick-content-notes' ),
            'manage_options',
            'qcn-notes',
            array( $this, 'render_notes_page' ),
            'dashicons-edit-page',
            31
        );

        add_submenu_page(
            'qcn-notes',
            __( 'All Notes', 'quick-content-notes' ),
            __( 'All Notes', 'quick-content-notes' ),
            'manage_options',
            'qcn-notes',
            array( $this, 'render_notes_page' )
        );

        add_submenu_page(
            'qcn-notes',
            __( 'Settings & Templates', 'quick-content-notes' ),
            __( 'Settings & Templates', 'quick-content-notes' ),
            'manage_options',
            'qcn-settings',
            array( $this, 'render_settings_page' )
        );
    }

    public function enqueue_assets( $hook ) {
        if ( ! QCN_Meta_Box::can() ) return;

        wp_enqueue_style(
            'qcn-admin',
            QCN_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            QCN_VERSION
        );
        wp_enqueue_script(
            'qcn-admin',
            QCN_PLUGIN_URL . 'assets/js/admin.js',
            array( 'jquery' ),
            QCN_VERSION,
            true
        );
        wp_localize_script( 'qcn-admin', 'qcnVars', array(
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'qcn_ajax' ),
            'i18n'    => array(
                'confirm_delete' => __( 'Delete this note?', 'quick-content-notes' ),
                'saving'         => __( 'Saving…', 'quick-content-notes' ),
                'saved'          => __( 'Saved!', 'quick-content-notes' ),
                'error'          => __( 'Error – please try again.', 'quick-content-notes' ),
            ),
        ) );
    }

    public function render_notes_page() {
        if ( ! QCN_Meta_Box::can() ) return;

        $search      = sanitize_text_field( $_GET['qcn_search']   ?? '' );
        $filter_col  = sanitize_key(        $_GET['qcn_color']    ?? '' );
        $filter_stat = sanitize_key(        $_GET['qcn_status']   ?? '' );
        $filter_user = absint(              $_GET['qcn_assigned'] ?? 0 );
        $paged       = max( 1, absint(      $_GET['paged']        ?? 1 ) );

        $result = QCN_DB::get_notes( array(
            'search'      => $search,
            'color'       => $filter_col,
            'status'      => $filter_stat,
            'assigned_to' => $filter_user,
            'per_page'    => 20,
            'paged'       => $paged,
        ) );

        $notes  = $result['items'];
        $total  = $result['total'];
        $pages  = ceil( $total / 20 );
        $admins = get_users( array( 'role__in' => array( 'administrator', 'editor' ), 'fields' => array( 'ID', 'display_name' ) ) );

        include QCN_PLUGIN_DIR . 'templates/admin-notes-page.php';
    }

    public function render_settings_page() {
        if ( ! QCN_Meta_Box::can() ) return;
        $settings  = get_option( 'qcn_settings',  array() );
        $templates = get_option( 'qcn_templates', array() );
        include QCN_PLUGIN_DIR . 'templates/admin-settings-page.php';
    }

    public function save_settings() {
        if ( ! QCN_Meta_Box::can() ) wp_die( 'Access denied.' );
        check_admin_referer( 'qcn_save_settings' );

        $settings = array(
            'email_notifications' => ! empty( $_POST['email_notifications'] ) ? 1 : 0,
            'notify_email'        => sanitize_email( $_POST['notify_email'] ?? get_option( 'admin_email' ) ),
            'notify_on_complete'  => ! empty( $_POST['notify_on_complete'] ) ? 1 : 0,
            'notify_on_assign'    => ! empty( $_POST['notify_on_assign'] )   ? 1 : 0,
        );
        update_option( 'qcn_settings', $settings );

        wp_redirect( admin_url( 'admin.php?page=qcn-settings&saved=1' ) );
        exit;
    }

    public function save_templates() {
        if ( ! QCN_Meta_Box::can() ) wp_die( 'Access denied.' );
        check_admin_referer( 'qcn_save_templates' );

        $raw       = $_POST['templates'] ?? array();
        $templates = array();
        foreach ( $raw as $tpl ) {
            $name    = sanitize_text_field(    $tpl['name']    ?? '' );
            $content = sanitize_textarea_field( $tpl['content'] ?? '' );
            $color   = sanitize_key(            $tpl['color']   ?? 'default' );
            if ( $name && $content ) {
                $templates[] = array(
                    'id'      => sanitize_key( $name ),
                    'name'    => $name,
                    'content' => $content,
                    'color'   => $color,
                );
            }
        }
        update_option( 'qcn_templates', $templates );

        wp_redirect( admin_url( 'admin.php?page=qcn-settings&saved=1' ) );
        exit;
    }
}
