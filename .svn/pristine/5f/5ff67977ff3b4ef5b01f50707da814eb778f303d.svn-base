<?php

include('dom-parser.php');

// Get attachment ID from URL
function flying_images_get_attachment_id( $attachment_url = '' ) {
	$attachment_url = preg_replace( '/-\d+x\d+(?=\.(jpg|jpeg|png|gif)$)/i', '', $attachment_url );
	$attachment_id =  attachment_url_to_postid($attachment_url);
	return $attachment_id;
}

// Rewrite images to add data-src, data-srcset, loading=lazy, noscript etc
function flying_images_rewrite_images($elements) {
    // Transparent placeholder
    $placeholder = "data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==";

    // Get options
    $exclude_keywords = get_option('flying_images_exclude_keywords');
    $lazymethod = get_option('flying_images_lazymethod');
    $responsive_images = get_option('flying_images_responsive_images');

    foreach($elements as $img) {

		//Add srcset
        if($responsive_images && $img->src && !$img->srcset) {
			$attachment_id = flying_images_get_attachment_id($img->src);
			$img->setAttribute("srcset", wp_get_attachment_image_srcset($attachment_id, "large"));
			$img->setAttribute("sizes", wp_get_attachment_image_sizes($attachment_id, "large"));
        }

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

        // Find images inside picture, lazy load them
        foreach($newHtml->find('picture') as $picture) {
            $sources = $picture->find('source');
            flying_images_rewrite_images($sources);
        }

        // Find normal images for lazy loading
        $images = $newHtml->find('img');
        flying_images_rewrite_images($images);
        
        return $newHtml;
    }
    catch(Exception $e) {
        return $html;
    }
}
if(!is_admin()) ob_start("flying_images_capture_html");