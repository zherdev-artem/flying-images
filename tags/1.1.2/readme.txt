=== Flying Images ===

Contributors: gijo
Donate link: https://www.buymeacoffee.com/gijovarghese
Tags: lazy, lazyload, native, performance, speed, fast
Requires at least: 4.5
Tested up to: 5.2
Requires PHP: 5.6
Stable tag: 1.1.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Lazy load images natively (with an optional tiny JavaScript fallback)

== Description ==

Lazy load images natively (with an optional tiny JavaScript fallback)

**Demo**: [WP Speed Matters](https://wpspeedmatters.com)

## How it works?

- **Native lazy load** - Rewrites HTML to add loading="lazy" to all images and leverage browser native way of loading. Fast, high performance, no JavaScript. Currently supported only in Chrome.

- **JavaScript lazy load** - Injects a tiny JS code (0.8KB, 0.3KB gzipped) which uses intersectionObserver to load images in the viewport, if native lazy load is not supported.

You can also configure the plugin to load images even before entering the viewport in the settings.

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

== Frequently Asked Questions ==

= Does it inject JavaScript? =

If you choose 'Native only' (from settings), no JavaScript will be injected. Otherwise it injects a JS code of 0.8KB  (0.3KB gzipped).

= How is it different from other lazy loading plugins? =

Most of the lazy loading plugins rely on JavaScript lazy loading, which injects a few KBs of JS. Flying Images using browser's native lazy loading. Optionally use JavaScript if browser is not supported.

== Changelog ==

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
