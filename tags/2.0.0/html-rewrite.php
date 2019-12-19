<?php
include('lib/dom-parser.php');

function flying_images_get_attachment_width($url) {
    try {
        // Extract width if found the the url. For example something-100x100.jpg
        preg_match('/(.+)-([0-9]+)x([0-9]+)\.(jpg|jpeg|png|gif)$/', $url, $matches);
        if(!empty($matches) && $matches[2] && is_numeric($matches[2])) 
            return $matches[2];

        // Width not found in url. If the image is from WP, try to get the actual size from DB
        $url = preg_replace( '/-\d+x\d+(?=\.(jpg|jpeg|png|gif)$)/i', '', $url);
        $attachment_id = attachment_url_to_postid($url);
        $width = $attachment_id ? wp_get_attachment_image_src($attachment_id, "full")[1] : false;
        return $width;
    }
    catch(Exception $e) {
        return false;
    }
}

function flying_images_add_responsiveness($images) {

    // Set of widths for responsive srcset
    $available_widths =  [400, 800, 1400, 2000, 3800];
    
    foreach ($images as $image) {

        // Skip if images are svg or base64
        if (strpos($image->src, ".svg") !== false) continue;
        if (strpos($image->src, "data:image") !== false) continue;
        
        $srcset = "";
        
        // Get image's width
        $max_width = flying_images_get_attachment_width($image->src);

        foreach ($available_widths as $width) {

            // Exclude available widths greater than the original image's width
            if($max_width && $width >= ($max_width-200)) continue;

            // Append src with width to srcset
            $srcset .= "{$image->src}?w={$width} {$width}w, \n";
        }

        // Add largest width ad the end of srcset
        if($max_width) $srcset .= "{$image->src} {$max_width}w";

        // Apply srcset
        $image->setAttribute("srcset", $srcset);

        // Generate sizes for srcset
        $largest_width = $max_width ? $max_width : end($available_widths);
        $sizes = "(max-width: {$largest_width}px) 100vw, {$largest_width}px";

        // Apply sizes
        $image->setAttribute("sizes", $sizes);
    }
}

function flying_images_add_compression($images) {
    // Get options
    $quality = get_option('flying_images_quality');

    foreach ($images as $image) {
        // Add quality as ? or & based on width query inserted before
        $image->src = preg_replace("/(\.(jpg|jpeg|png|gif))(?!\?)/", "$1?quality={$quality}", $image->src);
        $image->src = preg_replace("/(\.(jpg|jpeg|png|gif)\?w=\d+)/", "$1&quality={$quality}", $image->src);

        // Similarly to srcset
        if($image->srcset) {
            $image->srcset = preg_replace("/(\.(jpg|jpeg|png|gif))(?!\?)/", "$1?quality={$quality}", $image->srcset);
            $image->srcset = preg_replace("/(\.(jpg|jpeg|png|gif)\?w=\d+)/", "$1&quality={$quality}", $image->srcset);
        }
    }
}

function flying_images_add_cdn($images) {
    // Get options
    $exclude_keywords = get_option('flying_images_cdn_exclude_keywords');

    // Exclude base64 and svg images
    array_push($exclude_keywords, "data:image");

    $statically_url = "https://cdn.statically.io/img/";

    foreach ($images as $image) {

        // Exclude images
        foreach ($exclude_keywords as $keyword) {
            if (strpos($image->src, $keyword) !== false) continue 2;
        }
        
        // Add Statically CDN to src and srcset
        $image->src = preg_replace("/(^\w+:|^)\/\//", $statically_url, $image->src);
        if($image->srcset) $image->srcset = preg_replace("/(\w+:|^)\/\//im", $statically_url, $image->srcset);
    }
}

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
        $image->setAttribute("data-loading", "lazy");

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

function flying_images_process_background_images($images, $cdn_enabled, $compression_enabled, $lazy_loading_enabled) {

    $statically_url = "https://cdn.statically.io/img/";
    $quality = get_option('flying_images_quality');

    foreach ($images as $image) {
        // Split inline style to 3 parts, before background image, image url, after background image
        $regex = '/(.*)background.*:\s*url\((?:\'|")*(.*(?:\.(?:jpg|jpeg|png|gif|svg)))(?:\'|")*\)(.*)/';

        if(preg_match($regex, $image->style, $matches)) {
            
            // Add Statically CDN if enabled
            $image_url = $cdn_enabled ? preg_replace("/(^\w+:|^)\/\//", $statically_url, $matches[2]) : $matches[2];
            
            // Add compression if enabled and images are not svg
            if($compression_enabled && strpos($image_url, '.svg') === false)
                $image_url .= "?quality={$quality}";

            // Add lazy loading if enabled
            if($lazy_loading_enabled) {
                $style = "{$matches[1]}lazy-background-image:url('{$image_url}'){$matches[3]}";
                $image->setAttribute("data-loading","lazy-background");
            }
            else {
                $style = "{$matches[1]}background-image:url('{$image_url}'){$matches[3]}";
            }

            // Update style
            $image->style = $style;
        }
    }
}

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
        $responsiveness_enabled = get_option('flying_images_enable_responsive_images') && $cdn_enabled;
        $compression_enabled = get_option('flying_images_enable_compression') && $cdn_enabled;

        // Process picutre->srouce images
        foreach ($newHtml->find('picture') as $picture) {
            $images = $picture->find('source');
            if($responsiveness_enabled) flying_images_add_responsiveness($images);
            if($compression_enabled) flying_images_add_compression($images);
            if($cdn_enabled) flying_images_add_cdn($images);
            if($lazy_loading_enabled) flying_images_add_lazy_load($images);
        }

        // Process normal images with img tag
        $images = $newHtml->find('img');
        if($responsiveness_enabled) flying_images_add_responsiveness($images);
        if($compression_enabled) flying_images_add_compression($images);
        if($cdn_enabled) flying_images_add_cdn($images);
        if($lazy_loading_enabled) flying_images_add_lazy_load($images);

        // Process background images
        $background_images = $newHtml->find('[style*=background]');
        flying_images_process_background_images($background_images, $cdn_enabled, $compression_enabled, $lazy_loading_enabled);
        
        return $newHtml;

    } catch (Exception $e) {
        return $html;
    }
}

if (!is_admin()) ob_start("flying_images_rewrite_html");
