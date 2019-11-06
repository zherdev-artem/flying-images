<?php

include('dom-parser.php');

// Rewrite images to add data-src, data-srcset, loading=lazy, noscript etc
function flying_images_rewrite_images($elements) {
    // Transparent placeholder
    $placeholder = "data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==";

    // Get options
    $exclude_keywords = get_option('flying_images_exclude_keywords');
    $lazymethod = get_option('flying_images_lazymethod');

    foreach($elements as $img) {
        // Skip if the image if matched against exclude keywords
        foreach($exclude_keywords as $keyword) {
            if (strpos($img->parent()->outertext, $keyword) !== false) continue 2;
        }

        $original = $img->outertext;

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

            // add noscript tag if JavaScript is not supported or disabled
            $img->outertext = $img->outertext.'<noscript>'.$original.'</noscript>';
        }
    }
}

// HTML rewrite for lazy load
function flying_images_capture_html($html) {
    try {
        // check array
        if(!is_array($html))return $html;

        // check empty
        if(!count($html))return $html;

        // Check if the code is HTML, otherwise return
        if ($html[0] !== "<") return $html;

        // Parse HTML
        $newHtml = str_get_html($html);

        // Not HTML, return original
        if(!is_object($newHtml)) return $html;

        // Find all images, add lazy tag, change src and srcset
        $sources = $newHtml->find('source');
        flying_images_rewrite_images($sources);
        $images = $newHtml->find('img');
        flying_images_rewrite_images($images);
        
        return $newHtml;
    }
    catch(Exception $e) {
        return $html;
    }
}
if(!is_admin()) ob_start("flying_images_capture_html");