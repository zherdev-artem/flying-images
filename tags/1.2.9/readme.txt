=== Flying Images ===

Contributors: gijo
Donate link: https://www.buymeacoffee.com/gijovarghese
Tags: lazy, lazyload, native, performance, speed, fast
Requires at least: 4.5
Tested up to: 5.2
Requires PHP: 5.6
Stable tag: 1.2.9
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

High-performance Native Image Lazy Loading (with an optional tiny JavaScript fallback)

== Description ==

High-performance Native Image Lazy Loading (with an optional tiny JavaScript fallback)

## Quick Links

- Demo: [https://wpspeedmatters.com](https://wpspeedmatters.com)
- Join our [Facebook Group](https://www.facebook.com/groups/wpspeedmatters/), a community of WordPress speed enthusiasts
- [Buy me a coffee](https://www.buymeacoffee.com/gijovarghese)

## How it Works?

- **Uses native lazy loading** – Use native lazy loading if available (currently supported only in Chrome), otherwise use JavaScript to lazy load images.
Load images even before entering viewport – While other plugins load images when they’re ‘inside’ the viewport, Flying Images load them when they’re about to enter the viewport.
- **Tiny JavaScript** – Only 0.5KB, gzipped, minified.
- **Optionally use native only** – Only want to support Chrome? You can switch to “native only” which injects zero JavaScript.
- **Rewrites entire HTML** – Never miss an image from lazy loading (even the ones injected by gallery plugins).
- **Transparent placeholder** – A tiny base64 transparent is added to all images. No more flickering while loading images.
- **Exclude keywords** – Almost all lazy loading plugins provide exclude feature, however, Flying Images can also exclude images from the images’ parent node. Helpful if your image doesn’t have a class name.
- **Supports IE and JavaScript disabled browsers** – All images are loaded instantly if is Internet Explorer or even if JavaScript is entirely disabled (using noscript tag).

**Note: Make sure lazy loading is disabled in your cache plugin.**

== Installation ==

#### From within WordPress

1. Visit 'Plugins > Add New'
1. Search for 'Flying Images'
1. Activate Flying Images for WordPress from your Plugins page.
1. Visit 'Settings -> Flying Images' to configure

#### Manually

1. Upload the `flying-images` folder to the `/wp-content/plugins/` directory
1. Activate the Flying Images plugin through the 'Plugins' menu in WordPress
1. Visit 'Settings -> Flying Images' to configure

== Screenshots ==
1. Flying Images Settings

== Changelog ==

= 1.2.9 =
- Bug fix - Prevent videos inside source tag from loading

= 1.2.8 =
- Bug fix in last update

= 1.2.7 =
- Bug fix for Jetpack integration and empty pages

= 1.2.6 =
- Remove styling for JS disabled browsers, for compatibility with Swift Performance

= 1.2.5 =
- Bug fix - Images loading multiple times when cache is disabled in Chrome

= 1.2.4 =
- Prevent conflicts with NextGen gallery plugin

= 1.2.3 =
- Added noscript tag for images (load images when JavaScript is disabled)

= 1.2.2 =
- Exclude keywords now looks from parent node (useful if your images doesn't have a class or anything unique)
- Bug fix for picture tag (webp)

= 1.2.1 =
- Improved lazy loading for dynamic content
- Prevent parser from removing white spaces
- Performance improvements

= 1.2.0 =
- Lazy load images in picture tag (also fixes issues for webp)
- Automatic bottom margin height (removed option for custom margin)
- Performance improvements

= 1.1.2 =
- Bug fix - Lazy load images in dynamically injected content

= 1.1.1 =
- Support for Internet Explorer

= 1.1.0 =
- Rewrote HTML parser (fix breaking sites)
- Exclude images from lazy loading

= 1.0.1 =
- Renamed plugin from **Nazy Load** to **Flying Images**
- Typo fixes

= 1.0.0 =
- Initial release
