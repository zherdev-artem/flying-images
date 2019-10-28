<?php

// Hide original images if JavaScript is disabled (and show fallback images)

function flying_images_add_noscript_css() {
  echo '<noscript><style>img[loading="lazy"]{display:none !important}</style></noscript>';
}

add_action( 'wp_head', 'flying_images_add_noscript_css' );