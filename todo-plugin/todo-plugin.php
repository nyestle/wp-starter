<?php
/**
 * Plugin Name: CRUD Todo List
 * Description: Ein Plugin, das CRUD-Operationen auf einer Todo-Liste demonstriert.
 * Version: 1.0
 * Author: Dein Name
 */

// Sicherheitsmaßnahme: Direkten Zugriff verhindern
defined('ABSPATH') || exit;

// Tabelle erstellen bei Plugin-Aktivierung
register_activation_hook(__FILE__, 'todo_plugin_create_table');
function todo_plugin_create_table() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'todos';
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE $table_name (
        id INT NOT NULL AUTO_INCREMENT,
        task TEXT NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// Admin-Menü hinzufügen
add_action('admin_menu', 'todo_plugin_admin_menu');
function todo_plugin_admin_menu() {
    add_menu_page(
        'Todo List',
        'Todo List',
        'manage_options',
        'todo-plugin',
        'todo_plugin_page',
        'dashicons-list-view'
    );
}

// Admin-Seite anzeigen
function todo_plugin_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'todos';
    $todos = $wpdb->get_results("SELECT * FROM $table_name");
    ?>
    <div class="wrap">
        <h1>Todo Liste</h1>
        <form id="add-task-form">
            <input type="text" name="new_task" placeholder="Neue Aufgabe hinzufügen" required>
            <button type="submit" class="button button-primary">Hinzufügen</button>
        </form>
        <table class="widefat">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Aufgabe</th>
                    <th>Bearbeiten</th>
                    <th>Löschen</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($todos as $todo) : ?>
                    <tr>
                        <td><?php echo $todo->id; ?></td>
                        <td><?php echo esc_html($todo->task); ?></td>
                        <td>
                            <form class="update-form">
                                <input type="hidden" name="update_id" value="<?php echo $todo->id; ?>">
                                <input type="text" name="update_task" value="<?php echo esc_html($todo->task); ?>" required>
                                <button type="submit" class="button button-primary">Speichern</button>
                            </form>
                        </td>
                        <td>
                            <a href="#" class="button delete-button" data-id="<?php echo $todo->id; ?>">Löschen</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
}

// AJAX: Aufgabe hinzufügen
add_action('wp_ajax_add_task', 'todo_plugin_add_task');
function todo_plugin_add_task() {
    global $wpdb;
    $task = sanitize_text_field($_POST['task']);
    $table_name = $wpdb->prefix . 'todos';
    $wpdb->insert($table_name, ['task' => $task]);
    wp_send_json_success(['id' => $wpdb->insert_id, 'task' => $task]);
}

// AJAX: Aufgabe aktualisieren
add_action('wp_ajax_update_task', 'todo_plugin_update_task');
function todo_plugin_update_task() {
    global $wpdb;
    $id = intval($_POST['id']);
    $task = sanitize_text_field($_POST['task']);
    $table_name = $wpdb->prefix . 'todos';
    $updated = $wpdb->update($table_name, ['task' => $task], ['id' => $id]);

    if ($updated !== false) {
        wp_send_json_success(['id' => $id, 'task' => $task]);
    } else {
        wp_send_json_error(['message' => 'Update fehlgeschlagen']);
    }
}

// AJAX: Aufgabe löschen
add_action('wp_ajax_delete_task', 'todo_plugin_delete_task');
function todo_plugin_delete_task() {
    global $wpdb;
    $id = intval($_POST['id']);
    $table_name = $wpdb->prefix . 'todos';
    $wpdb->delete($table_name, ['id' => $id]);
    wp_send_json_success();
}

// JavaScript und CSS einbinden
add_action('admin_enqueue_scripts', 'todo_plugin_enqueue_scripts');
function todo_plugin_enqueue_scripts() {
    wp_enqueue_script('todo-plugin-scripts', plugins_url('js/scripts.js', __FILE__), ['jquery'], null, true);
    wp_localize_script('todo-plugin-scripts', 'ajaxurl', admin_url('admin-ajax.php'));
    wp_enqueue_style('todo-plugin-styles', plugins_url('css/styles.css', __FILE__));
}
