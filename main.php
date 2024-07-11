<?php 
/**
 * Plugin Name: Custom Table CRUD
 * Description: A simple plugin to demonstrate CRUD operations on a custom table.
 * Version: 1.0
 * Author: Your Name
 */

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
    $table_name = $wpdb->prefix . 'custom_table';
    $results = $wpdb->get_results("SELECT * FROM $table_name");

    if (!empty($results)) {
        echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
        echo '<table>';
        echo '<thead>';
        echo '<tr><th>ID</th><th>Name</th><th>Email</th><th>Actions</th></tr>';
        echo '</thead>';
        echo '<tbody>';
        foreach ($results as $row) {
            echo '<tr>';
            echo '<td>' . esc_html($row->id) . '</td>';
            echo '<td><input type="text" name="name[' . esc_attr($row->id) . ']" value="' . esc_attr($row->name) . '"></td>';
            echo '<td><input type="email" name="email[' . esc_attr($row->id) . ']" value="' . esc_attr($row->email) . '"></td>';
            echo '<td>';
            echo '<button type="submit" name="action" value="update_' . esc_attr($row->id) . '">Update</button>';
            echo '<button type="submit" name="action" value="delete_' . esc_attr($row->id) . '">Delete</button>';
            echo '</td>';
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
        echo '</form>';
    } else {
        echo 'No data found.';
    }
}
add_shortcode('custom_table_data', 'display_custom_table_data');

// Handle update actions
add_action('admin_post_nopriv_custom_table_update', 'handle_custom_table_update');
add_action('admin_post_custom_table_update', 'handle_custom_table_update');
function handle_custom_table_update() {
    global $wpdb;

    if (!empty($_POST['name']) && !empty($_POST['email'])) {
        foreach ($_POST['name'] as $id => $name) {
            $email = sanitize_email($_POST['email'][$id]);

            $wpdb->update(
                $wpdb->prefix . 'custom_table',
                array(
                    'name' => sanitize_text_field($name),
                    'email' => $email,
                ),
                array('id' => intval($id)),
                array(
                    '%s',
                    '%s'
                ),
                array('%d')
            );
        }
    }

    wp_redirect($_SERVER['HTTP_REFERER']);
    exit;
}

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
