<?php
// Set default config on plugin load if not set

function flying_images_set_default_config() {
    if (FLYING_IMAGES_VERSION !== get_option('FLYING_IMAGES_VERSION')) {
        if (get_option('flying_images_lazymethod') === false)
            update_option('flying_images_lazymethod', "nativejavascript");
        if (get_option('flying_images_exclude_keywords') === false)
            update_option('flying_images_exclude_keywords', ['data:image','logo']);
        update_option('FLYING_IMAGES_VERSION', FLYING_IMAGES_VERSION);
    }
}

add_action('plugins_loaded', 'flying_images_set_default_config');