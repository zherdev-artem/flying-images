<?php
/**
 * The main file of the Flying Images
 *
 * @package flying-images
 * @version 1.0.1
 *
 * Plugin Name: Flying Images
 * Plugin URI: https://wordpress.org/plugins/nazy-load/
 * Description: Lazy load images natively or using JavaScript
 * Author: Gijo Varghese
 * Author URI: https://wpspeedmatters.com/
 * Version: 1.0.1
 * Text Domain: flying-images
 */
include('simple_html_dom.php');


// Define constant with current version
if (!defined('FLYING_IMAGES_VERSION'))
    define('FLYING_IMAGES_VERSION', '1.0.1');


// Set default config on plugin load if not set
function flying_images_set_default_config() {
    if (FLYING_IMAGES_VERSION !== get_option('FLYING_IMAGES_VERSION')) {
        if (get_option('flying_images_lazymethod') === false)
            update_option('flying_images_lazymethod', "nativejavascript");
        if (get_option('flying_images_margin') === false)
            update_option('flying_images_margin', 200);
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
function flying_images_settings_view()
{
    // Validate nonce
    if(isset($_POST['submit']) && !wp_verify_nonce($_POST['flying-images-settings-form'], 'flying-images')) {
        echo '<div class="notice notice-error"><p>Nonce verification failed</p></div>';
        exit;
    }

    // Update config in database after form submission
    if (isset($_POST['lazymethod'])) {
        
        $lazymethod = sanitize_text_field($_POST['lazymethod']);
        update_option('flying_images_lazymethod', $lazymethod);
        
        $margin = sanitize_text_field($_POST['margin']);
        $margin = is_numeric($margin) ? $margin : 200;
        update_option('flying_images_margin', $margin);

    }

    // Get config from db for displaying in the form
    $lazymethod = get_option('flying_images_lazymethod');
    $margin = get_option('flying_images_margin');
    
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


// Lazy load HTML rewrite
function flying_images_callback($html) {
  
  // Check if the code is HTML, otherwise return
  if ($html[0] !== "<") return $html;

  $html = str_get_html($html);
	
  foreach($html->find('img') as $img) {

    // Skip if the image is base64 image
    if (strpos($img->src, 'data:image') !== false) continue;
      
	// Add native lazy loading
    $img->setAttribute("loading","lazy");
      
	// Transparent placeholder
    $placeholder = "data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==";

    // Move src (and srcset) to data attributes
    if ( get_option('flying_images_lazymethod') === "nativejavascript" ) {

        $img->setAttribute("data-src", $img->src);
        $img->setAttribute("src", $placeholder);

        if($img->srcset) {
            $img->setAttribute("data-srcset", $img->srcset);
            $img->setAttribute("srcset", $placeholder);
        }
    }
    
  }

  return $html;
}
if(!is_admin()) { ob_start("flying_images_callback"); }

// Inject JavaScript code for lazy loading
add_action( 'wp_print_footer_scripts', function () {
  if(get_option('flying_images_lazymethod') === "nativejavascript") {
    ?>
<script type="text/javascript">document.addEventListener("DOMContentLoaded",function(){if("loading" in HTMLImageElement.prototype)
document.querySelectorAll('img[loading="lazy"]').forEach(function(e){(e.src=e.dataset.src),e.dataset.srcset&&(e.srcset=e.dataset.srcset)});else if(!window.IntersectionObserver)
document.querySelectorAll('img[loading="lazy"]').forEach(function(e){(e.src=e.dataset.src),e.dataset.srcset&&(e.srcset=e.dataset.srcset)});else{const e=new IntersectionObserver(function(e,t){e.forEach(function(e){if(e.isIntersecting){const s=e.target;(s.src=s.dataset.src),s.dataset.srcset&&(s.srcset=s.dataset.srcset),s.removeAttribute("loading"),t.unobserve(s)}})},{rootMargin:"<?php echo get_option('flying_images_margin'); ?>px"});document.querySelectorAll('img[loading="lazy"]').forEach(function(t){e.observe(t)})}})</script>
    <?php
  }
} );