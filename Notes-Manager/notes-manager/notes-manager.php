<?php
/*
Plugin Name: Notes Manager
Description: A simple admin panel to add and delete personal notes.
Version: 1.0
Author: Bommi Yaswanth
*/

defined('ABSPATH') or die('Unauthorized access');

register_activation_hook(__FILE__, 'nm_create_notes_table');
function nm_create_notes_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . "notes";
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id INT NOT NULL AUTO_INCREMENT,
        note TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

add_action('admin_menu', 'nm_admin_menu');
function nm_admin_menu() {
    add_menu_page('Notes Manager', 'Notes', 'manage_options', 'notes-manager', 'nm_notes_page');
}

function nm_notes_page() {
    ?>
    <div class="wrap">
        <h2>Notes Manager</h2>
        <form id="note-form">
            <textarea name="note" rows="3" cols="60" placeholder="Enter your note here"></textarea><br><br>
            <button type="submit" class="button button-primary">Add Note</button>
        </form>
        <ul id="notes-list"></ul>
    </div>
    <style>
        #notes-list li {
            padding: 5px;
            margin: 5px 0;
            background: #f9f9f9;
        }
    </style>
    <?php
}

add_action('admin_enqueue_scripts', 'nm_enqueue_assets');
function nm_enqueue_assets() {
    wp_enqueue_script('nm-admin-js', plugin_dir_url(__FILE__) . 'js/admin.js', array('jquery'), false, true);
    wp_enqueue_style('nm-style', plugin_dir_url(__FILE__) . 'css/style.css');
    
    wp_localize_script('nm-admin-js', 'nm_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('nm_nonce')
    ));
}


add_action('wp_ajax_add_note', 'nm_add_note');
function nm_add_note() {
    check_ajax_referer('nm_nonce');

    global $wpdb;
    $note = sanitize_textarea_field($_POST['note']);
    $table = $wpdb->prefix . "notes";

    $wpdb->insert($table, array('note' => $note));
    $note_id = $wpdb->insert_id;

    wp_send_json_success(array('id' => $note_id, 'note' => $note));
}

add_action('wp_ajax_delete_note', 'nm_delete_note');
function nm_delete_note() {
    check_ajax_referer('nm_nonce');
    
    global $wpdb;
    $id = intval($_POST['id']);
    $wpdb->delete($wpdb->prefix . "notes", array('id' => $id));

    wp_send_json_success();
}
add_action('wp_ajax_get_notes', 'nm_get_notes');
function nm_get_notes() {
    global $wpdb;
    $table = $wpdb->prefix . "notes";
    $results = $wpdb->get_results("SELECT * FROM $table ORDER BY created_at DESC");

    wp_send_json_success($results);
}
