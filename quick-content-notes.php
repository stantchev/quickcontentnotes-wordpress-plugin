<?php
/**
 * Plugin Name:       Quick Content Notes
 * Plugin URI:        https://github.com/stantchev/QuickContentNotes-WordPress-Plugin
 * Description:       Admin-only note-taking with admin bar integration, search/filter, history, email notifications, templates, multi-user assignments and more.
 * Version:           1.5.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            Milen Stanchev
 * Author URI:        https://stanchev.bg/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       quick-content-notes
 * Domain Path:       /languages
 *
 * @package QuickContentNotes
 * @author  Milen Stanchev <https://stanchev.bg/>
 * @link    https://github.com/stantchev/QuickContentNotes-WordPress-Plugin
 * @link    https://stantchev.github.io/QuickContentNotes-WordPress-Plugin/
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'QCN_VERSION',     '1.5.0' );
define( 'QCN_PLUGIN_DIR',  plugin_dir_path( __FILE__ ) );
define( 'QCN_PLUGIN_URL',  plugin_dir_url( __FILE__ ) );
define( 'QCN_PLUGIN_FILE', __FILE__ );
define( 'QCN_TABLE_SUFFIX', 'qcn_note_history' );

require_once QCN_PLUGIN_DIR . 'includes/class-qcn-db.php';
require_once QCN_PLUGIN_DIR . 'includes/class-qcn-meta-box.php';
require_once QCN_PLUGIN_DIR . 'includes/class-qcn-admin-bar.php';
require_once QCN_PLUGIN_DIR . 'includes/class-qcn-admin-page.php';
require_once QCN_PLUGIN_DIR . 'includes/class-qcn-columns.php';
require_once QCN_PLUGIN_DIR . 'includes/class-qcn-notifications.php';
require_once QCN_PLUGIN_DIR . 'includes/class-qcn-ajax.php';

function qcn_init() {
    QCN_Meta_Box::get_instance();
    QCN_Admin_Bar::get_instance();
    QCN_Admin_Page::get_instance();
    QCN_Columns::get_instance();
    QCN_Notifications::get_instance();
    QCN_Ajax::get_instance();
}
add_action( 'plugins_loaded', 'qcn_init' );

register_activation_hook( __FILE__, array( 'QCN_DB', 'create_tables' ) );
register_uninstall_hook( __FILE__, array( 'QCN_DB', 'drop_tables' ) );
