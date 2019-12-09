<?php
include('lib/dom-parser.php');

function flying_images_add_cdn($images) {
    // Get options
    $exclude_keywords = get_option('flying_images_cdn_exclude_keywords');
    $quality = get_option('flying_images_quality');
    $responsive_images = get_option('flying_images_responsive_images');

    // Exclude base64 and svg images
    array_push($exclude_keywords,"data:image",".svg");

    $statically_url = "https://cdn.statically.io/img/";

    foreach($images as $image) {

        $available_sizes =  [400, 800, 1400, 2000, 3800];

        // Exclude images
        foreach($exclude_keywords as $keyword) {
            if (strpos($image->src, $keyword) !== false) continue 2;
        }

        // Generate Statically CDN URL
        $src = $statically_url.preg_replace("/(^\w+:|^)\/\//", "", $image->src);
        
        // Append quality if needed
        if($quality) {
            $src .= "?quality={$quality}";
        }
        
        // Set new src URL
		$image->setAttribute("src", $src);

        // Skip adding responsive images, if not enabled
        //if(!$responsive_images) continue;

        // Generate srcset and add it to image
        $srcset = "";
        foreach($available_sizes as $size) {
            // If quality (or any) query string is added, use '&', other wise '?'
            if (strpos($src, '?') !== false)
                $srcset .= "{$src}&w={$size} {$size}w, \n";
            else
                $srcset .= "{$src}?w={$size} {$size}w, \n";
        }
        $image->setAttribute("srcset", $srcset);

        // Find largest size and add it to 'sizes'
        $largest_size = end($available_sizes);
        $sizes = "(max-width: {$largest_size}px) 100vw, {$largest_size}px";
        $image->setAttribute("sizes", $sizes);
    }
}

// Rewrite images to add data-src, data-srcset, loading=lazy etc
function flying_images_add_lazy_load($images) {
    // Get options
    $exclude_keywords = get_option('flying_images_exclude_keywords');
    $lazymethod = get_option('flying_images_lazymethod');

    // Exclude base64 images
    array_push($exclude_keywords,"data:image");

    // Transparent placeholder
    $placeholder = "data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==";

    foreach($images as $image) {
        // Skip if the image if matched against exclude keywords
        foreach($exclude_keywords as $keyword) {
            if (strpos($image->parent()->outertext, $keyword) !== false) continue 2;
        }

        // Add lazy loading attribute
        $image->setAttribute("loading","lazy");

        // Skip rest if lazy loading method is native only
        if($lazymethod === "native") continue;
        
        // Add data-src and data-srcset
        $image->setAttribute("data-src", $image->src);
        $image->setAttribute("data-srcset", $image->srcset);
        
        // Remove srcset
        $image->removeAttribute("srcset");

        // Apply placeholder
        $image->setAttribute("src", $placeholder);
    }
}

// HTML rewrite for lazy load
function flying_images_rewrite_html($html) {
    try {
        // check empty
         if(!isset($html) || trim($html) === '')return $html;
		
		// return if content is XML
		if (strcasecmp(substr($html, 0, 5), '<?xml') === 0) return $html;

        // Check if the code is HTML, otherwise return
        if ($html[0] !== "<") return $html;

        // Parse HTML
        $newHtml = str_get_html($html);

        // Not HTML, return original
        if(!is_object($newHtml)) return $html;

        foreach($newHtml->find('picture') as $picture) {
            $sources = $picture->find('source');
            flying_images_add_cdn($sources);
            flying_images_add_lazy_load($sources);
        }

        $images = $newHtml->find('img');
        flying_images_add_cdn($images);
        flying_images_add_lazy_load($images);
        
        return $newHtml;
    }
    catch(Exception $e) {
        return $html;
    }
}

if(!is_admin()) ob_start("flying_images_rewrite_html");