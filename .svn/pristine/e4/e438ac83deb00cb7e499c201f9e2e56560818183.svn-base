<?php
/**
 * The main file of the Nazy Load
 *
 * @package nazy-load
 * @version 1.0.0
 *
 * Plugin Name: Nazy Load
 * Plugin URI: https://wordpress.org/plugins/nazy-load/
 * Description: Lazy load images natively or using JavaScript
 * Author: Gijo Varghese
 * Author URI: https://wpspeedmatters.com/
 * Version: 1.0.0
 * Text Domain: nazy-load
 */


// Define constant with current version
if (!defined('NAZY_LOAD_VERSION'))
    define('NAZY_LOAD_VERSION', '1.0.0');


// Set default config on plugin load if not set
function nazy_load_set_default_config() {
    if (NAZY_LOAD_VERSION !== get_option('NAZY_LOAD_VERSION')) {
        if (get_option('nazy_load_lazymethod') === false)
            update_option('nazy_load_lazymethod', "nativejavascript");
        if (get_option('nazy_load_margin') === false)
            update_option('nazy_load_margin', 200);
        update_option('NAZY_LOAD_VERSION', NAZY_LOAD_VERSION);
    }
}
add_action('plugins_loaded', 'nazy_load_set_default_config');


// Register settings menu
function nazy_load_register_settings_menu() {
    add_options_page('Nazy Load', 'Nazy Load', 'manage_options', 'nazy-load', 'nazy_load_settings_view');
}
add_action('admin_menu', 'nazy_load_register_settings_menu');

// Settings page
function nazy_load_settings_view()
{
    // Validate nonce
    if(isset($_POST['submit']) && !wp_verify_nonce($_POST['nazy-load-settings-form'], 'nazy-load')) {
        echo '<div class="notice notice-error"><p>Nonce verification failed</p></div>';
        exit;
    }

    // Update config in database after form submission
    if (isset($_POST['lazymethod'])) {
        
        $lazymethod = sanitize_text_field($_POST['lazymethod']);
        update_option('nazy_load_lazymethod', $lazymethod);
        
        $margin = sanitize_text_field($_POST['margin']);
        $margin = is_numeric($margin) ? $margin : 200;
        update_option('nazy_load_margin', $margin);

    }

    // Get config from db for displaying in the form
    $lazymethod = get_option('nazy_load_lazymethod');
    $margin = get_option('nazy_load_margin');
    
    // Settings form
    include 'settings-form.php';
}


// Add credit links in plugins list
function nazy_load_add_action_links($links) {
    $plugin_shortcuts = array(
        '<a href="'.admin_url('options-general.php?page=nazy-load').'">Settings</a>',
        '<a href="https://www.buymeacoffee.com/gijovarghese" target="_blank" style="color:#3db634;">Buy developer a coffee</a>'
    );
    return array_merge($links, $plugin_shortcuts);
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'nazy_load_add_action_links');


// Nazy load HTML rewrite
function nazy_load_callback($html) {
  
  // Check if the code is HTML, otherwise return
  if ($html[0] !== "<") return $html;
  
  // Load the HTML code to parse
  $dom = new DOMDocument();
  $dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_COMPACT | LIBXML_NOENT );
	
  $xpath = new DOMXPath($dom);

  // Find all images
  $images = $xpath->evaluate("//img");

  foreach ($images as $image) {

    // Skip if the image is base64 image
    if (strpos($dom->saveHTML($image), 'data:image') !== false) continue;

    // Transparent placeholder
    $placeholder = "data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==";

    // Add native lazy loading
    $image->setAttribute("loading", "lazy");

    // Move src (and srcset) to data attributes
    if ( get_option('nazy_load_lazymethod') === "nativejavascript" ) {
      $image->setAttribute("data-src", $image->getAttribute('src'));
      $image->setAttribute("src", $placeholder);

      if($image->getAttribute('srcset')) {
        $image->setAttribute("data-srcset", $image->getAttribute('srcset'));
        $image->setAttribute("srcset", $placeholder);
      }
    }

  }

  // return modified HTML
  $html = $dom->saveHTML();  
  return $html;

}
if(!is_admin()) { ob_start("nazy_load_callback"); }

// Inject JavaScript code for lazy loading
add_action( 'wp_print_footer_scripts', function () {
  if(get_option('nazy_load_lazymethod') === "nativejavascript") {
    ?>
<script type="text/javascript">document.addEventListener("DOMContentLoaded",function(){if("loading" in HTMLImageElement.prototype)
document.querySelectorAll('img[loading="lazy"]').forEach(function(e){(e.src=e.dataset.src),e.dataset.srcset&&(e.srcset=e.dataset.srcset)});else if(!window.IntersectionObserver)
document.querySelectorAll('img[loading="lazy"]').forEach(function(e){(e.src=e.dataset.src),e.dataset.srcset&&(e.srcset=e.dataset.srcset)});else{const e=new IntersectionObserver(function(e,t){e.forEach(function(e){if(e.isIntersecting){const s=e.target;(s.src=s.dataset.src),s.dataset.srcset&&(s.srcset=s.dataset.srcset),s.removeAttribute("loading"),t.unobserve(s)}})},{rootMargin:"<?php echo get_option('nazy_load_margin'); ?>px"});document.querySelectorAll('img[loading="lazy"]').forEach(function(t){e.observe(t)})}})</script>
    <?php
  }
} );