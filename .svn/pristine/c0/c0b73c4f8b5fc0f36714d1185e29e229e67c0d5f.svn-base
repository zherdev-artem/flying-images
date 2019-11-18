<?php
// Inject JavaScript code for lazy loading

function flying_images_inject_js() {

  $lazymethod = get_option('flying_images_lazymethod');
  $margin = get_option('flying_images_margin');

  if($lazymethod === "nativejavascript" || $lazymethod === "javascript") {
    ?>
<script type="text/javascript" id="flying-images">const flyingImages=function(){const lazymethod="<?php echo $lazymethod; ?>";const margin=<?php echo $margin; ?>;if(lazymethod==="nativejavascript"&&"loading" in HTMLImageElement.prototype){document.querySelectorAll('[loading="lazy"]').forEach(function(e){if(e.dataset.srcset&&e.srcset!==e.dataset.srcset)e.srcset=e.dataset.srcset;if(e.dataset.src&&e.src!==e.dataset.src)e.src=e.dataset.src})}else if(window.IntersectionObserver){const e=new IntersectionObserver(function(e,t){e.forEach(function(e){if(e.isIntersecting){const s=e.target;if(s.dataset.srcset)s.srcset=s.dataset.srcset;if(s.dataset.src)s.src=s.dataset.src;s.removeAttribute("loading");t.unobserve(s)}})},{rootMargin:margin+"px"});document.querySelectorAll('[loading="lazy"]').forEach(function(t){e.observe(t)})}else{const e=document.querySelectorAll('[loading="lazy"]');for(let i=0;i<e.length;i++){if(e[i].dataset.srcset)e[i].srcset=e[i].dataset.srcset;if(e[i].dataset.src)e[i].src=e[i].dataset.src}}};document.addEventListener("DOMContentLoaded",function(){flyingImages()});function throttle(callback,limit){var wait=!1;return function(){if(!wait){callback.apply(null,arguments);wait=!0;setTimeout(function(){wait=!1},limit)}}}const dynamicContentObserver=new MutationObserver(throttle(flyingImages,125));window.onload=function(){dynamicContentObserver.observe(document.body,{attributes:!0,childList:!0,subtree:!0})}</script>
    <?php
  }
}

add_action( 'wp_print_footer_scripts', 'flying_images_inject_js');