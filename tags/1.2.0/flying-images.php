<?php
/**
 * The main file of the Flying Images
 *
 * @package flying-images
 * @version 1.2.0
 *
 * Plugin Name: Flying Images
 * Plugin URI: https://wordpress.org/plugins/nazy-load/
 * Description: High-performance Native Image Lazy Loading
 * Author: Gijo Varghese
 * Author URI: https://wpspeedmatters.com/
 * Version: 1.2.0
 * Text Domain: flying-images
 */
include('dom_parser.php');

// Define constant with current version
if (!defined('FLYING_IMAGES_VERSION'))
    define('FLYING_IMAGES_VERSION', '1.2.0');

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

// Add credit links in plugins list
function flying_images_add_action_links($links) {
    $plugin_shortcuts = array(
        '<a href="'.admin_url('options-general.php?page=flying-images').'">Settings</a>',
        '<a href="https://www.buymeacoffee.com/gijovarghese" target="_blank" style="color:#3db634;">Buy developer a coffee</a>'
    );
    return array_merge($links, $plugin_shortcuts);
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'flying_images_add_action_links');

function flying_images_rewrite_images ($elements) {
    // Transparent placeholder
    $placeholder = "data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==";

    // Get options
    $exclude_keywords = get_option('flying_images_exclude_keywords');
    $lazymethod = get_option('flying_images_lazymethod');

    foreach($elements as $img) {
        // Skip if the image if matched against exclude keywords
        foreach($exclude_keywords as $keyword) {
            if (strpos($img->outertext, $keyword) !== false) continue 2;
        }

        // Add native lazy loading
        $img->setAttribute("loading","lazy");

        // Move src (and srcset) to data attributes
        if($lazymethod === "nativejavascript") {

            if($img->src) {
                $img->setAttribute("data-src", $img->src);
                $img->setAttribute("src", $placeholder);
            }

            if($img->srcset) {
                $img->setAttribute("data-srcset", $img->srcset);
                $img->removeAttribute("srcset");
            }
        }
        
    }
}

// HTML rewrite for lazy load
function flying_images_callback($html) {
  
  // Check if the code is HTML, otherwise return
  if ($html[0] !== "<") return $html;
  
  try {
    // Parse HTML
    $newHtml = str_get_html($html);

    // Not HTML, return original
    if(!is_object($newHtml)) return $html;

    // Find all images, add lazy tag, change src and srcset
    $images = $newHtml->find('img');
    $sources = $newHtml->find('source');
    flying_images_rewrite_images($images);
    flying_images_rewrite_images($sources);

    return $newHtml;
  }
  catch(Exception $e) {
    return $html;
  }
}
if(!is_admin()) ob_start("flying_images_callback");

// Inject JavaScript code for lazy loading
add_action( 'wp_print_footer_scripts', function () {
  if(get_option('flying_images_lazymethod') === "nativejavascript") {
    ?>
<script type="text/javascript">const flyingImages=function(){if("loading" in HTMLImageElement.prototype){document.querySelectorAll('[loading="lazy"]').forEach(function(e){if(e.dataset.srcset)e.srcset=e.dataset.srcset;if(e.dataset.src)e.src=e.dataset.src})}else if(!window.IntersectionObserver){const e=document.querySelectorAll('[loading="lazy"]');for(let i=0;i<e.length;i++){if(e[i].dataset.srcset)e[i].srcset=e[i].dataset.srcset;if(e[i].dataset.src)e[i].src=e[i].dataset.src}}else{const e=new IntersectionObserver(function(e,t){e.forEach(function(e){if(e.isIntersecting){const s=e.target;if(s.dataset.srcset)s.srcset=s.dataset.srcset;if(s.dataset.src)s.src=s.dataset.src;s.removeAttribute("loading");t.unobserve(s)}})},{rootMargin:window.screen.height/2+"px"});document.querySelectorAll('[loading="lazy"]').forEach(function(t){e.observe(t)})}};document.addEventListener("DOMContentLoaded",function(){flyingImages()});const dynamicContentObserver=new MutationObserver(function(mutationsList){for(let i=0;i<mutationsList.length;i++){if(mutationsList[i].type==="childList")flyingImages()}});dynamicContentObserver.observe(document.body,{attributes:!0,childList:!0,subtree:!0})</script>
    <?php
  }
});