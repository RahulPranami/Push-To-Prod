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

// enqueue tailwind stylesheet from assets directory
function push_to_prod_enqueue_styles($hook)
{
    if ($hook !== 'toplevel_page_push-to-prod') {
        return;
    }

    wp_enqueue_style('push-to-prod', plugin_dir_url(__FILE__) . 'assets/output.css');
    wp_enqueue_script('htmx', plugin_dir_url(__FILE__) . 'assets/htmx.min.js', [], false, true);
}
add_action('admin_enqueue_scripts', 'push_to_prod_enqueue_styles');


// Add a custom menu item to the WordPress admin dashboard
add_action('admin_menu', 'push_to_prod_menu');

function push_to_prod_menu()
{
    add_menu_page(
        'Push to Prod',
        'Push to Prod',
        'manage_options',
        'push-to-prod',
        'push_to_prod_page',
        'dashicons-upload',
        99
    );
}

// Callback function for the custom menu page
function push_to_prod_page()
{

    date_default_timezone_set('Asia/Kolkata');

    if (isset($_POST['push_button'])) {
        // Run your bash command here
        // $output = shell_exec('sudo -u rahul rsync -avz /home/rahul/Documents/syncTest ubuntu@13.232.50.244:/home/ubuntu/syncTest/ 2>&1');

        // // add below line to sudoers file with same user as above
        // // http ALL=(user_with_keys) NOPASSWD: /usr/bin/rsync

        // if ($output === null) {
        //     echo "Error executing rsync command: " . error_get_last()['message'];
        // } else {
        //     echo "<pre>$output</pre>";
        // }

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
            echo "<pre>$output</pre>";
        }


        $logEntry = date('Y-m-d H:i:s') . " | User: " . $user->user_login . " | Command: " . $command . PHP_EOL . $output . PHP_EOL;

        // echo "<pre>$logEntry</pre>";

        file_put_contents($logFile, $logEntry, FILE_APPEND);

        error_log("Rsync command executed by " . $user->user_login . " at " . date('Y-m-d H:i:s') . " with output: " . $output . PHP_EOL, 3, $logFile);
    }
    // print a year month and date and time string to save as a file
    // print time in a string format
    // echo date('y_M_d-h_i_a');


    // echo date('Y-m-d');


    // print_r();
    // print_r();
    ?>
    <div class="wrap">
        <h1>Push to Prod</h1>
        <form method="post">
            <input type="submit" name="push_button" class="button button-primary" value="Push to Production">
        </form>
    </div>
    <?php
}

