<?php
/*
Plugin Name: Push to Prod
Description: Run a bash command when a button is clicked in the WordPress admin dashboard.
Version: 1.0
Author: Your Name
*/

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

add_action(
    'admin_enqueue_scripts',
    function ($hook) {
        if ($hook !== 'toplevel_page_push-to-prod') {
            return;
        }

        wp_enqueue_style('push-to-prod', plugin_dir_url(__FILE__) . 'assets/output.min.css');
        // wp_enqueue_script('htmx', plugin_dir_url(__FILE__) . 'assets/htmx.min.js', [], false, true);
        wp_enqueue_script('htmx', plugin_dir_url(__FILE__) . 'assets/htmx.min.js');
    }
);


// Add a custom menu item to the WordPress admin dashboard
add_action(
    'admin_menu',
    function () {
        add_menu_page(
            'Push to Prod',
            'Push to Prod',
            'manage_options',
            'push-to-prod',
            function () {
                ?>
        <div class="wrap">
            <h1>Push to Prod</h1>
            <form hx-post="<?php echo admin_url('admin-ajax.php'); ?>" hx-vals='{"action": "sync_changes"}' hx-trigger="submit"
                hx-target="#output">
                <input type="submit" name="push_button" class="button button-primary" value="Push to Production">
            </form>

            <div id="output">
                <span class="hidden htmx-indicator">
                    Syncing...
                </span>
            </div>
        </div>
        <?php
            },
            'dashicons-upload',
            99
        );
    }
);

add_action(
    'wp_ajax_sync_changes',
    function () {
        if (isset($_POST['push_button'])) {
            date_default_timezone_set('Asia/Kolkata');
            if (1 == 0) {
                // Create log directory if it doesn't exist
                $logDir = WP_CONTENT_DIR . '/rsync-logs/';
                if (!file_exists($logDir)) {
                    mkdir($logDir, 0755, true);
                }

                $source = '/home/rahul/Documents/syncTest/';
                $destination = 'ubuntu@13.232.50.244:/home/ubuntu/syncTest/';

                $logFile = $logDir . date('Y-m-d_H-i-s') . '_rsync.log';
                $user = wp_get_current_user();
                $command = "sudo -u rahul rsync -avz " . escapeshellarg($source) . " " . escapeshellarg($destination);
                $output = shell_exec($command . ' 2>&1');

                if ($output === null) {
                    echo "Error executing rsync command: " . error_get_last()['message'];
                } else {
                    // echo "<pre>$output</pre>";
                }


                $logEntry = date('Y-m-d H:i:s') . " | User: " . $user->user_login . " | Command: " . $command . PHP_EOL . $output . PHP_EOL;

                // echo "<pre>$logEntry</pre>";

                file_put_contents($logFile, $logEntry, FILE_APPEND);

                error_log("Rsync command executed by " . $user->user_login . " at " . date('Y-m-d H:i:s') . " with output: " . $output . PHP_EOL, 3, $logFile);
            }
        }

        ob_start();
        // if ($result_from_pages):

        print_r($_POST);
        ?>

    <?php
        echo ob_get_clean();
        // endif;
        exit();
    }
);
