<?php
include('lib/dom-parser.php');

function flying_images_add_cdn($images) {
    // Get options
    $exclude_keywords = get_option('flying_images_cdn_exclude_keywords');
    $responsiveness_enabled = get_option('flying_images_enable_responsive_images');
    $compression_enabled = get_option('flying_images_enable_compression');
    $quality = get_option('flying_images_quality');

    // Exclude base64 and svg images
    array_push($exclude_keywords, "data:image", ".svg");

    $statically_url = "https://cdn.statically.io/img/";

    foreach ($images as $image) {
        $available_sizes =  [400, 800, 1400, 2000, 3800];

        // Exclude images
        foreach ($exclude_keywords as $keyword) {
            if (strpos($image->src, $keyword) !== false) continue 2;
        }

        // Generate Statically CDN URL
        $src = preg_replace("/(^\w+:|^)\/\//", $statically_url, $image->src);
        
        // Append quality if needed
        if ($compression_enabled) $src .= "?quality={$quality}";
        
        // Set new src URL
        $image->setAttribute("src", $src);

        // Skip adding responsive images, if not enabled
        if ($responsiveness_enabled){
            // Generate srcset and add it to image
            $srcset = "";
            foreach ($available_sizes as $size) {
                if ($compression_enabled) {
                    $srcset .= "{$src}&w={$size} {$size}w, \n";
                } else {
                    $srcset .= "{$src}?w={$size} {$size}w, \n";
                }
            }
            $image->setAttribute("srcset", $srcset);

            // Find largest size and add it to 'sizes'
            $largest_size = end($available_sizes);
            $sizes = "(max-width: {$largest_size}px) 100vw, {$largest_size}px";
            $image->setAttribute("sizes", $sizes);
        } 
        else if($image->srcset) {
            // Rewrite srcset to add Statically CDN
            $image->srcset = preg_replace("/(\w+:|^)\/\//", $statically_url, $image->srcset);
            // Add quality if compression is enabled
            $image->srcset = preg_replace("/(\.(jpg|jpeg|png|gif))/", "$1?quality={$quality}", $image->srcset);
        }
    }
}

// Rewrite images to add data-src, data-srcset, loading=lazy etc
function flying_images_add_lazy_load($images) {
    // Get options
    $lazymethod = get_option('flying_images_lazymethod');
    $exclude_keywords = get_option('flying_images_exclude_keywords');

    // Transparent placeholder
    $placeholder = "data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==";

    foreach ($images as $image) {
        // Exclude base64 images
        if (strpos($image->src, "data:image") !== false) continue;

        // Skip if the image if matched against exclude keywords
        foreach ($exclude_keywords as $keyword) {
            if (strpos($image->parent()->outertext, $keyword) !== false) continue 2;
        }

        // Add lazy loading attribute
        $image->setAttribute("loading", "lazy");

        // Skip rest if lazy loading method is native only
        if ($lazymethod === "native") continue;
        
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
        if (!isset($html) || trim($html) === '') {
            return $html;
        }
        
        // return if content is XML
        if (strcasecmp(substr($html, 0, 5), '<?xml') === 0) {
            return $html;
        }

        // Check if the code is HTML, otherwise return
        if ($html[0] !== "<") {
            return $html;
        }

        // Parse HTML
        $newHtml = str_get_html($html);

        // Not HTML, return original
        if (!is_object($newHtml)) {
            return $html;
        }

        $cdn_enabled = get_option('flying_images_enable_cdn');
        $lazy_loading_enabled = get_option('flying_images_enable_lazyloading');

        foreach ($newHtml->find('picture') as $picture) {
            $sources = $picture->find('source');
            if($cdn_enabled) flying_images_add_cdn($sources);
            if($lazy_loading_enabled) flying_images_add_lazy_load($sources);
        }

        $images = $newHtml->find('img');
        if($cdn_enabled) flying_images_add_cdn($images);
        if($lazy_loading_enabled) flying_images_add_lazy_load($images);
        
        return $newHtml;
    } catch (Exception $e) {
        return $html;
    }
}

if (!is_admin()) ob_start("flying_images_rewrite_html");
