<?php
/**
 * QCN_Columns – posts-list columns with quick status toggle.
 *
 * @package QuickContentNotes
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class QCN_Columns {

    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) self::$instance = new self();
        return self::$instance;
    }

    private function __construct() {
        if ( ! QCN_Meta_Box::can() ) return;

        foreach ( QCN_Meta_Box::post_types() as $pt ) {
            add_filter( "manage_{$pt}s_columns",              array( $this, 'add_column' ) );
            add_action( "manage_{$pt}s_custom_column",        array( $this, 'render_column' ), 10, 2 );
            add_filter( "manage_edit-{$pt}_sortable_columns", array( $this, 'sortable' ) );
        }
    }

    public function add_column( $cols ) {
        $new = array();
        foreach ( $cols as $k => $v ) {
            $new[ $k ] = $v;
            if ( 'title' === $k ) {
                $new['qcn_note'] = '<span class="dashicons dashicons-edit-page" title="'
                    . esc_attr__( 'Admin Note', 'quick-content-notes' ) . '"></span> '
                    . esc_html__( 'Admin Note', 'quick-content-notes' );
            }
        }
        return $new;
    }

    public function render_column( $col, $post_id ) {
        if ( 'qcn_note' !== $col ) return;

        $content  = get_post_meta( $post_id, '_qcn_note_content',  true );
        $color    = get_post_meta( $post_id, '_qcn_note_color',    true ) ?: 'default';
        $status   = get_post_meta( $post_id, '_qcn_note_status',   true ) ?: 'active';
        $assigned = (int) get_post_meta( $post_id, '_qcn_note_assigned', true );

        if ( empty( $content ) ) {
            echo '<a href="' . esc_url( get_edit_post_link( $post_id ) . '#qcn_admin_notes' ) . '" class="qcn-add-note-link">+ '
               . esc_html__( 'Add note', 'quick-content-notes' ) . '</a>';
            return;
        }

        $snippet = mb_strlen( $content ) > 80 ? mb_substr( $content, 0, 80 ) . '…' : $content;

        echo '<div class="qcn-col-wrap qcn-color-' . esc_attr( $color ) . ' qcn-status-' . esc_attr( $status ) . '">';

        // Snippet
        echo '<p class="qcn-col-snippet">' . esc_html( $snippet ) . '</p>';

        // Meta line
        echo '<div class="qcn-col-meta">';

        // Assignment
        if ( $assigned ) {
            $u = get_userdata( $assigned );
            if ( $u ) {
                echo '<span class="qcn-assigned-to">👤 ' . esc_html( $u->display_name ) . '</span>';
            }
        }

        // Quick status toggle buttons
        echo '<span class="qcn-quick-status" data-post-id="' . esc_attr( $post_id ) . '" data-current="' . esc_attr( $status ) . '">';
        $statuses = array(
            'active'      => array( 'label' => '📌', 'title' => __( 'Active',      'quick-content-notes' ) ),
            'in-progress' => array( 'label' => '🔄', 'title' => __( 'In Progress', 'quick-content-notes' ) ),
            'completed'   => array( 'label' => '✅', 'title' => __( 'Completed',   'quick-content-notes' ) ),
        );
        foreach ( $statuses as $slug => $info ) {
            $active = ( $status === $slug ) ? ' qcn-btn-active' : '';
            echo '<button class="qcn-stat-btn' . $active . '" data-status="' . esc_attr( $slug ) . '" title="' . esc_attr( $info['title'] ) . '">'
               . $info['label'] . '</button>';
        }
        echo '</span>';

        echo '</div>'; // .qcn-col-meta

        // Edit link
        echo '<a href="' . esc_url( get_edit_post_link( $post_id ) . '#qcn_admin_notes' ) . '" class="qcn-col-edit">'
           . esc_html__( 'Edit note', 'quick-content-notes' ) . '</a>';

        echo '</div>'; // .qcn-col-wrap
    }

    public function sortable( $cols ) {
        $cols['qcn_note'] = 'qcn_note';
        return $cols;
    }
}
