<?php

// Register settings menu

function flying_images_register_settings_menu() {
    add_options_page('Flying Images', 'Flying Images', 'manage_options', 'flying-images', 'flying_images_settings_view');
}
add_action('admin_menu', 'flying_images_register_settings_menu');

// Settings page

function flying_images_settings_view() {
    // Validate nonce
    if(isset($_POST['submit']) && !wp_verify_nonce($_POST['flying-images-settings-form'], 'flying-images')) {
        echo '<div class="notice notice-error"><p>Nonce verification failed</p></div>';
        exit;
    }

    // Update config in database after form submission
    if (isset($_POST['lazymethod'])) {
        
        $lazymethod = sanitize_text_field($_POST['lazymethod']);
        update_option('flying_images_lazymethod', $lazymethod);

        $keywords = array_map('trim', explode("\n", str_replace("\r", "", $_POST['exclude_keywords'])));
        update_option('flying_images_exclude_keywords', $keywords);

    }

    // Get config from db for displaying in the form
    $lazymethod = get_option('flying_images_lazymethod');
    $exclude_keywords = get_option('flying_images_exclude_keywords');
    
    // Settings form
    include 'settings-form.php';
}