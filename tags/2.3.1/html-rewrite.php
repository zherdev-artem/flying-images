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

function flying_images_add_query_string($url, $name, $value) {
    return strpos($url, '?') !== false ? "{$url}&$name=$value" : "{$url}?$name=$value";
}

function flying_images_add_responsiveness($images) {

    $enable_container_responsive_images = get_option('flying_images_enable_container_responsive_images');

    // Set of widths for responsive srcset
    $available_device_widths =  [400, 800, 1400, 2000, 3800];

    
    foreach ($images as $image) {

        // Skip generating srcset if theme already added it
        if($image->srcset) continue;

        // Skip if images are svg or base64
        if (strpos($image->src, ".svg") !== false) continue;
        if (strpos($image->src, "data:image") !== false) continue;
        
        $srcset = "";
        
        // Get image width
        $max_image_width = $enable_container_responsive_images && $image->width && is_numeric($image->width) ? $image->width : flying_images_get_attachment_width($image->src);

        // Generate srcset
        foreach ($available_device_widths as $device_width) {
            $image_width = $device_width;

            // Exclude available widths greater than the original image's width
            if($max_image_width && $device_width >= $max_image_width) $image_width = $max_image_width;;

            // Append src with width to srcset
            $url = flying_images_add_query_string($image->src, "w", $image_width);
            $srcset .= "{$url} {$device_width}w, \n";
        }

        // Apply srcset
        $image->setAttribute("srcset", $srcset);

        // Set max image width to src
        if($max_image_width) $image->src = flying_images_add_query_string($image->src, "w", $max_image_width);

        // Generate sizes for srcset
        $largest_width = $max_image_width ? $max_image_width : end($available_device_widths);
        $sizes = "(max-width: {$largest_width}px) 100vw, {$largest_width}px";

        // Apply sizes
        $image->setAttribute("sizes", $sizes);
    }
}

function flying_images_add_compression($images, $quality) {
    foreach ($images as $image) {
        // Add quality as ? or & based on width query inserted before
        $image->src = flying_images_add_query_string($image->src, "quality", $quality);

        // Similarly to srcset
        if($image->srcset) {
            $srcset = "";
            preg_match_all('/(https?:\/\/\S+)\s(\d+w)/', $image->srcset, $matches);
            $images_urls = $matches[1];
            $sizes = $matches[2];
            foreach($images_urls as $index=>$image_url) {
                $image_url = flying_images_add_query_string($image_url, "quality", $quality);
                $srcset .= "{$image_url} {$sizes[$index]},\n";
            }
            $image->srcset = $srcset;
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
            if ($keyword && strpos($image->src, $keyword) !== false) continue 2;
        }
        
        // Rewrite relative urls
        $image->src = preg_replace("/(?:^|\s)(\/)(?!\/)/", site_url()."/", $image->src);
        if($image->srcset) $image->srcset = preg_replace("/(?:^|\s)(\/)(?!\/)/im", site_url()."/", $image->srcset);

        // Add Statically CDN to src and srcset
        $image->src = preg_replace("/(^\w+:|^)\/\//", $statically_url, $image->src);
        if($image->srcset) $image->srcset = preg_replace("/(\w+:|^)\/\//im", $statically_url, $image->srcset);
    }
}

function flying_images_add_cdn_to_styles($styles, $compression_enabled, $quality) {

    $statically_url = "https://cdn.statically.io/img/";

    $exclude_keywords = get_option('flying_images_cdn_exclude_keywords');

    foreach ($styles as $style) {
        // Split inline style to 3 parts, before background image, image url, after background image
        $regex = '/(.*background.*:\s*url\((?:\'|")*)(.*(?:\.(?:jpg|jpeg|png|gif|svg)))((?:\'|")*\).*)/s';

        if(preg_match($regex, $style->innertext, $matches)) {
            
            // Add Statically CDN if enabled
            $image_url = preg_replace("/(^\w+:|^)\/\//", $statically_url, $matches[2]);

            // Exclude image if needed
            foreach ($exclude_keywords as $keyword) {
                if ($keyword && strpos($iimage_url, $keyword) !== false) continue 2;
            }
            
            // Add compression if enabled and images are not svg
            if($compression_enabled && strpos($image_url, '.svg') === false)
                $image_url = flying_images_add_query_string($image_url, "quality", $quality);

            // Update style
            $style->innertext = "{$matches[1]}{$image_url}{$matches[3]}";
        }
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
            if ($keyword && strpos($image->parent()->outertext, $keyword) !== false) continue 2;
        }

        if($lazymethod === "native") {
            // Add browsers native lazy loading
            $image->setAttribute("loading", "lazy");
        }
        else {
            // Native or JS+Native lazy loading            

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
}

function flying_images_lazy_load_elementor_bg_images($divs) {

    $exclude_keywords = get_option('flying_images_exclude_keywords');

    foreach($divs as $div) {
        // Skip if the image if matched against exclude keywords
        foreach ($exclude_keywords as $keyword) {
            if ($keyword && $div->class && strpos($div->class, $keyword) !== false) continue 2;
        }

        $div->setAttribute("data-loading", "lazy-background");

        if($div->style)
            $div->style = "background:none;{$div->style}";
        else
            $div->setAttribute("style", "background:none;");
    }
}

function flying_images_process_background_images($images, $cdn_enabled, $compression_enabled, $quality, $lazy_loading_enabled) {

    $statically_url = "https://cdn.statically.io/img/";

    foreach ($images as $image) {
        // Split inline style to 3 parts, before background image, image url, after background image
        $regex = '/(.*background.*:\s*url\((?:\'|")*)(.*(?:\.(?:jpg|jpeg|png|gif|svg)))((?:\'|")*\).*)/';

        if(preg_match($regex, $image->style, $matches)) {
            
            // Add Statically CDN if enabled
            $image_url = $cdn_enabled ? preg_replace("/(^\w+:|^)\/\//", $statically_url, $matches[2]) : $matches[2];
            
            // Add compression if enabled and images are not svg
            if($compression_enabled && strpos($image_url, '.svg') === false)
                $image_url = flying_images_add_query_string($image_url, "quality", $quality);
            
            // Add lazy loading if enabled
            if($lazy_loading_enabled) {
                $image->setAttribute("data-loading","lazy-background");
                $style = "background:none;{$matches[1]}{$image_url}{$matches[3]}";
            }
            else {
                $style = "{$matches[1]}{$image_url}{$matches[3]}";
            }

            // Update style
            $image->style = $style;
        }
    }
}

function flying_images_process_woocommerce_thumbnails($images, $compression_enabled, $quality) {

    $statically_url = "https://cdn.statically.io/img/";

    foreach ($images as $image) {
        $src = $image->getAttribute("data-thumb");

        // Remove relative URLs
        $src = preg_replace("/(?:^|\s)(\/)/", site_url()."/", $src);

        // Add Statically CDN
        $src = preg_replace("/(^\w+:|^)\/\//", $statically_url, $src);

        // Append quality if compression is enabled
        if($compression_enabled)
            $src = flying_images_add_query_string($src, "quality", $quality);

        $image->setAttribute("data-thumb", $src);
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

        // Get options
        $cdn_enabled = get_option('flying_images_enable_cdn');
        $lazy_loading_enabled = get_option('flying_images_enable_lazyloading');
        $responsiveness_enabled = get_option('flying_images_enable_responsive_images') && $cdn_enabled;
        $compression_enabled = get_option('flying_images_enable_compression') && $cdn_enabled;
        $quality = get_option('flying_images_quality');

        // Remove picture tag
        foreach ($newHtml->find('picture') as $picture) {
            $picture->outertext = $picture->find('img', 0);
        }

        // Process normal images with img tag
        $images = $newHtml->find('img');

        if($responsiveness_enabled) flying_images_add_responsiveness($images);

        if($compression_enabled) flying_images_add_compression($images, $quality);

        if($cdn_enabled) {
            flying_images_add_cdn($images);

            // Process WooCommerce thumbnails
            $woocommerce_thumbnails = $newHtml->find('div[data-thumb]');
            flying_images_process_woocommerce_thumbnails($woocommerce_thumbnails, $compression_enabled, $quality);

            // Process background images in style tags
            $styles = $newHtml->find('style');
            if($cdn_enabled) flying_images_add_cdn_to_styles($styles, $compression_enabled, $quality);
        }

        if($lazy_loading_enabled) {
            flying_images_add_lazy_load($images);

            $elementor_background_divs = $newHtml->find('[data-settings*=background_background]');
            flying_images_lazy_load_elementor_bg_images($elementor_background_divs);
        }

        // Process background images
        $background_images = $newHtml->find('[style*=background]');
        flying_images_process_background_images($background_images, $cdn_enabled, $compression_enabled, $quality, $lazy_loading_enabled);
        
        return $newHtml;

    } catch (Exception $e) {
        return $html;
    }
}

if (!is_admin()) ob_start("flying_images_rewrite_html");
