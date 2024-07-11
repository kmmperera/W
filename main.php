<?php 
/**
 * Plugin Name: Custom Table CRUD
 * Description: A simple plugin to demonstrate CRUD operations on a custom table.
 * Version: 1.0
 * Author: Your Name
 */
function enqueue_custom_table_scripts() {
    wp_enqueue_script('jquery');
    wp_enqueue_script('jquery-ui-dialog');
    wp_enqueue_style('jquery-ui-css', '//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css');
    wp_enqueue_script('custom-table-crud', plugin_dir_url(__FILE__) . 'custom-table-crud.js', array('jquery', 'jquery-ui-dialog'), null, true);
}
add_action('wp_enqueue_scripts', 'enqueue_custom_table_scripts');
add_action('admin_enqueue_scripts', 'enqueue_custom_table_scripts');

// Create custom table on plugin activation
register_activation_hook(__FILE__, 'create_custom_table');
function create_custom_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'custom_table';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name tinytext NOT NULL,
        email text NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// Display custom table data with update and delete options

function display_custom_table_data() {
    global $wpdb;

    // List of custom tables
    $custom_tables = array('custom_table1', 'custom_table2'); // Add your custom table names here

    foreach ($custom_tables as $table) {
        $table_name = $wpdb->prefix . $table;
        $results = $wpdb->get_results("SELECT * FROM $table_name");

        if (!empty($results)) {
            echo '<h3>' . esc_html($table) . '</h3>';
            echo '<table>';
            echo '<thead>';
            echo '<tr>';

            // Table headers
            $columns = array_keys((array) $results[0]);
            foreach ($columns as $column) {
                echo '<th>' . esc_html($column) . '</th>';
            }
            echo '<th>Actions</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';

            foreach ($results as $row) {
                echo '<tr>';
                foreach ($row as $key => $value) {
                    echo '<td>' . esc_html($value) . '</td>';
                }
                echo '<td>';
                echo '<button class="ceymulticall-open-modal" data-table="' . esc_attr($table_name) . '" data-row="' . htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8') . '">Update</button>';
                echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '" style="display:inline;">';
                echo '<input type="hidden" name="action" value="delete_' . esc_attr($row->id) . '">';
                echo '<input type="hidden" name="table" value="' . esc_attr($table_name) . '">';
                echo '<button type="submit">Delete</button>';
                echo '</form>';
                echo '</td>';
                echo '</tr>';
            }

            echo '</tbody>';
            echo '</table>';
        } else {
            echo '<p>No data found in ' . esc_html($table) . '</p>';
        }
    }

    // Modal HTML
    echo '<div id="ceymulticall-update-modal" title="Update Record" style="display:none;">';
    echo '<form id="ceymulticall-update-form" method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
    echo '<input type="hidden" name="action" value="custom_table_update">';
    echo '<input type="hidden" name="table" id="ceymulticall-modal-table">';
    echo '<div id="ceymulticall-modal-fields"></div>';
    echo '<button type="submit">Save Changes</button>';
    echo '</form>';
    echo '</div>';
}
add_shortcode('custom_table_data', 'display_custom_table_data');


// Handle update actions
function handle_custom_table_update() {
    global $wpdb;

    if (!empty($_POST['table'])) {
        $table_name = sanitize_text_field($_POST['table']);
        $data = $_POST;
        unset($data['table'], $data['action']);

        $id = intval($data['id']);
        unset($data['id']);

        // Sanitize data
        foreach ($data as $key => $value) {
            $data[$key] = sanitize_text_field($value);
        }

        $wpdb->update(
            $table_name,
            $data,
            array('id' => $id)
        );
    }

    wp_redirect($_SERVER['HTTP_REFERER']);
    exit;
}
add_action('admin_post_nopriv_custom_table_update', 'handle_custom_table_update');
add_action('admin_post_custom_table_update', 'handle_custom_table_update');


// Handle delete actions
add_action('admin_post_nopriv_custom_table_delete', 'handle_custom_table_delete');
add_action('admin_post_custom_table_delete', 'handle_custom_table_delete');
function handle_custom_table_delete() {
    global $wpdb;

    if (!empty($_POST['action']) && strpos($_POST['action'], 'delete_') !== false) {
        $id = intval(str_replace('delete_', '', $_POST['action']));

        $wpdb->delete(
            $wpdb->prefix . 'custom_table',
            array('id' => $id),
            array('%d')
        );
    }

    wp_redirect($_SERVER['HTTP_REFERER']);
    exit;
}
