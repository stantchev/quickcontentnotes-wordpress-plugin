<?php
/**
 * QCN_DB – database helper.
 * Creates and manages the note-history custom table.
 *
 * @package QuickContentNotes
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class QCN_DB {

    /** @return string Full table name with WP prefix */
    public static function history_table() {
        global $wpdb;
        return $wpdb->prefix . QCN_TABLE_SUFFIX;
    }

    /** Run on plugin activation */
    public static function create_tables() {
        global $wpdb;
        $table      = self::history_table();
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS {$table} (
            id          BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            post_id     BIGINT(20) UNSIGNED NOT NULL,
            user_id     BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
            content     LONGTEXT             NOT NULL,
            color       VARCHAR(20)          NOT NULL DEFAULT 'default',
            status      VARCHAR(20)          NOT NULL DEFAULT 'active',
            assigned_to BIGINT(20) UNSIGNED           DEFAULT NULL,
            template_id VARCHAR(60)                   DEFAULT NULL,
            changed_at  DATETIME             NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY post_id  (post_id),
            KEY user_id  (user_id)
        ) {$charset_collate};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );

        update_option( 'qcn_db_version', QCN_VERSION );
    }

    /** Run on plugin uninstall */
    public static function drop_tables() {
        global $wpdb;
        $wpdb->query( 'DROP TABLE IF EXISTS ' . self::history_table() );
        // Clean up all post meta.
        $wpdb->query( "DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE '_qcn_%'" );
        delete_option( 'qcn_db_version' );
        delete_option( 'qcn_settings' );
        delete_option( 'qcn_templates' );
    }

    /**
     * Insert a history snapshot.
     *
     * @param int    $post_id
     * @param array  $data  Keys: content, color, status, assigned_to, template_id
     */
    public static function insert_history( $post_id, array $data ) {
        global $wpdb;
        $wpdb->insert(
            self::history_table(),
            array(
                'post_id'     => absint( $post_id ),
                'user_id'     => get_current_user_id(),
                'content'     => wp_kses_post( $data['content']     ?? '' ),
                'color'       => sanitize_key( $data['color']       ?? 'default' ),
                'status'      => sanitize_key( $data['status']      ?? 'active' ),
                'assigned_to' => ! empty( $data['assigned_to'] ) ? absint( $data['assigned_to'] ) : null,
                'template_id' => ! empty( $data['template_id'] ) ? sanitize_key( $data['template_id'] ) : null,
                'changed_at'  => current_time( 'mysql' ),
            ),
            array( '%d', '%d', '%s', '%s', '%s', '%d', '%s', '%s' )
        );
    }

    /**
     * Get history rows for a post.
     *
     * @param  int $post_id
     * @param  int $limit
     * @return array
     */
    public static function get_history( $post_id, $limit = 20 ) {
        global $wpdb;
        $table = self::history_table();
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$table} WHERE post_id = %d ORDER BY changed_at DESC LIMIT %d",
                absint( $post_id ),
                absint( $limit )
            )
        );
    }

    /**
     * Get all notes (post meta) with optional filters.
     *
     * @param  array $args  Keys: search, color, status, assigned_to, orderby, order, per_page, paged
     * @return array { items: array, total: int }
     */
    public static function get_notes( array $args = array() ) {
        global $wpdb;

        $search      = ! empty( $args['search'] )      ? sanitize_text_field( $args['search'] ) : '';
        $color       = ! empty( $args['color'] )        ? sanitize_key( $args['color'] ) : '';
        $status      = ! empty( $args['status'] )       ? sanitize_key( $args['status'] ) : '';
        $assigned_to = ! empty( $args['assigned_to'] )  ? absint( $args['assigned_to'] ) : 0;
        $per_page    = ! empty( $args['per_page'] )     ? absint( $args['per_page'] ) : 20;
        $paged       = ! empty( $args['paged'] )        ? absint( $args['paged'] ) : 1;
        $offset      = ( $paged - 1 ) * $per_page;

        $pm   = $wpdb->postmeta;
        $post = $wpdb->posts;

        $where = "WHERE p.post_status != 'trash'
                    AND pm_content.meta_key  = '_qcn_note_content'
                    AND pm_color.meta_key    = '_qcn_note_color'
                    AND pm_status.meta_key   = '_qcn_note_status'
                    AND pm_assign.meta_key   = '_qcn_note_assigned'
                    AND pm_content.meta_value != ''";

        $params = array();

        if ( $search ) {
            $where   .= " AND pm_content.meta_value LIKE %s";
            $params[] = '%' . $wpdb->esc_like( $search ) . '%';
        }
        if ( $color ) {
            $where   .= " AND pm_color.meta_value = %s";
            $params[] = $color;
        }
        if ( $status ) {
            $where   .= " AND pm_status.meta_value = %s";
            $params[] = $status;
        }
        if ( $assigned_to ) {
            $where   .= " AND pm_assign.meta_value = %d";
            $params[] = $assigned_to;
        }

        $base_sql = "FROM {$post} p
            JOIN {$pm} pm_content ON pm_content.post_id = p.ID
            JOIN {$pm} pm_color   ON pm_color.post_id   = p.ID
            JOIN {$pm} pm_status  ON pm_status.post_id  = p.ID
            JOIN {$pm} pm_assign  ON pm_assign.post_id  = p.ID
            {$where}";

        // Count
        $count_sql = "SELECT COUNT(DISTINCT p.ID) " . $base_sql;
        $total     = $params
            ? (int) $wpdb->get_var( $wpdb->prepare( $count_sql, ...$params ) )
            : (int) $wpdb->get_var( $count_sql );

        // Rows
        $select_sql = "SELECT DISTINCT p.ID as post_id, p.post_title, p.post_type,
                pm_content.meta_value as note_content,
                pm_color.meta_value   as note_color,
                pm_status.meta_value  as note_status,
                pm_assign.meta_value  as note_assigned
            " . $base_sql . "
            ORDER BY p.post_modified DESC
            LIMIT %d OFFSET %d";

        $query_params   = $params;
        $query_params[] = $per_page;
        $query_params[] = $offset;

        $items = $query_params
            ? $wpdb->get_results( $wpdb->prepare( $select_sql, ...$query_params ) )
            : $wpdb->get_results( $select_sql );

        return array( 'items' => $items ?: array(), 'total' => $total );
    }
}
