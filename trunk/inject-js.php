<?php
// Inject JavaScript code for lazy loading

function flying_images_inject_js()
{
    $lazymethod = get_option('flying_images_lazymethod');
    $margin = get_option('flying_images_margin');

    if ($lazymethod === "nativejavascript" || $lazymethod === "javascript") {
        ?>
<script type="text/javascript" id="flying-images">const flyingImages=function(){const a=document.querySelectorAll("[loading=\"lazy\"]");if("<?php echo $lazymethod; ?>"==="nativejavascript"&&"loading"in HTMLImageElement.prototype)a.forEach(function(a){a.dataset.srcset&&(a.srcset=a.dataset.srcset),a.src=a.dataset.src});else if(window.IntersectionObserver){const b=new IntersectionObserver(a=>{a.forEach(a=>{a.isIntersecting&&(b.unobserve(a.target),a.target.dataset.srcset&&(a.target.srcset=a.target.dataset.srcset),a.target.src=a.target.dataset.src,a.target.classList.add("lazyloaded"),a.target.removeAttribute("loading"))})},{rootMargin:<?php echo $margin; ?>+"px"});a.forEach(a=>{b.observe(a)})}else for(let b=0;b<a.length;b++)a[b].dataset.srcset&&(a[b].srcset=a[b].dataset.srcset),a[b].src=a[b].dataset.src};flyingImages();function throttle(a,b){var c=!1;return function(){c||(a.apply(null,arguments),c=!0,setTimeout(function(){c=!1},b))}}const dynamicContentObserver=new MutationObserver(throttle(flyingImages,125));dynamicContentObserver.observe(document.body,{attributes:!0,childList:!0,subtree:!0});</script>
    <?php
    }
}

add_action('wp_print_footer_scripts', 'flying_images_inject_js');
