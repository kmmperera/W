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
    $table_name = $wpdb->prefix . 'custom_table';
    $results = $wpdb->get_results("SELECT * FROM $table_name");

    if (!empty($results)) {
        echo '<table>';
        echo '<thead>';
        echo '<tr><th>ID</th><th>Name</th><th>Email</th><th>Actions</th></tr>';
        echo '</thead>';
        echo '<tbody>';
        foreach ($results as $row) {
            echo '<tr>';
            echo '<td>' . esc_html($row->id) . '</td>';
            echo '<td>' . esc_html($row->name) . '</td>';
            echo '<td>' . esc_html($row->email) . '</td>';
            echo '<td>';
            echo '<button class="ceymulticall-open-modal" data-id="' . esc_attr($row->id) . '" data-name="' . esc_attr($row->name) . '" data-email="' . esc_attr($row->email) . '">Update</button>';
            echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '" style="display:inline;">';
            echo '<button type="submit" name="action" value="delete_' . esc_attr($row->id) . '">Delete</button>';
            echo '</form>';
            echo '</td>';
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
    } else {
        echo 'No data found.';
    }

    // Modal HTML
    echo '<div id="ceymulticall-update-modal" title="Update Record" style="display:none;">';
    echo '<form id="ceymulticall-update-form" method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
    echo '<input type="hidden" name="action" value="custom_table_update">';
    echo '<input type="hidden" name="id" id="ceymulticall-modal-id">';
    echo '<label for="ceymulticall-modal-name">Name:</label>';
    echo '<input type="text" name="name" id="ceymulticall-modal-name">';
    echo '<label for="ceymulticall-modal-email">Email:</label>';
    echo '<input type="email" name="email" id="ceymulticall-modal-email">';
    echo '<button type="submit">Save Changes</button>';
    echo '</form>';
    echo '</div>';
}
add_shortcode('custom_table_data', 'display_custom_table_data');

// Handle update actions
function handle_custom_table_update() {
    global $wpdb;

    if (!empty($_POST['id']) && !empty($_POST['name']) && !empty($_POST['email'])) {
        $id = intval($_POST['id']);
        $name = sanitize_text_field($_POST['name']);
        $email = sanitize_email($_POST['email']);

        $wpdb->update(
            $wpdb->prefix . 'custom_table',
            array(
                'name' => $name,
                'email' => $email,
            ),
            array('id' => $id),
            array(
                '%s',
                '%s'
            ),
            array('%d')
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
