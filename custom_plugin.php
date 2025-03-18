<?php
/*
Plugin Name: Custom Contact Form
Description: A simple contact form that stores user submissions in the database.
Version: 1.1
Author: Your Name
*/

// Shortcode to display the contact form
function custom_contact_form() {
    global $wpdb;

    // Initialize a variable to hold the thank-you message
    $thank_you_message = '';

    // Check if form is submitted and handle submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_contact_form'])) {
        $name = sanitize_text_field($_POST['name']);
        $email = sanitize_email($_POST['email']);
        $phone = sanitize_text_field($_POST['phone']);
        $message = sanitize_textarea_field($_POST['message']);

        // Validate inputs
        if (!empty($name) && !empty($email) && !empty($phone)) {
            $table_name = $wpdb->prefix . 'contact_form_submissions';
            $wpdb->show_errors();

            $inserted = $wpdb->insert($table_name, array(
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'message' => $message,
            ));

            if ($inserted) {
                $thank_you_message = '<p class="thank-you">Thank you for your submission!</p>';
            } else {
                $thank_you_message = '<p class="error">Error inserting data: ' . esc_html($wpdb->last_error) . '</p>';
            }
        } else {
            $thank_you_message = '<p class="error">Please fill in all required fields.</p>';
        }
    }

    ob_start();
    ?>
    <div class="contact-form-container">
        <form method="post">
            <label for="name">Name:</label>
            <input type="text" name="name" required>

            <label for="email">Email:</label>
            <input type="email" name="email" required>

            <label for="phone">Phone:</label>
            <input type="tel" name="phone" required>

            <label for="message">Message (Optional):</label>
            <textarea name="message"></textarea>

            <input type="submit" name="submit_contact_form" value="Submit">
        </form>
        <?php echo $thank_you_message; ?>
    </div>
    <?php

    return ob_get_clean();
}
add_shortcode('custom_contact_form', 'custom_contact_form');

// Create database table on plugin activation
function custom_contact_form_install() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'contact_form_submissions';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name tinytext NOT NULL,
        email text NOT NULL,
        phone text NOT NULL,
        message text,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook(_FILE_, 'custom_contact_form_install');

// Enqueue styles for the contact form
function custom_contact_form_styles() {
    echo '
    <style>
        .contact-form-container {
            max-width: 400px;
            margin: auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        input[type="text"], input[type="email"], input[type="tel"], textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }
        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #45a049;
        }
        .thank-you {
            color: green;
            font-weight: bold;
        }
        .error {
            color: red;
        }
    </style>
    ';
}
add_action('wp_head', 'custom_contact_form_styles');~