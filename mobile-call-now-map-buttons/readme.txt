=== Mobile Call Now & Map Buttons ===
Contributors: davidsword, vklakkineni, roimediaworks
Tags: mobile, call button, click to call, maps, directions
Requires at least: 5.8
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.6.2
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Adds customizable Call Now and Google Maps Directions buttons for mobile visitors.

== Description ==

Mobile Call Now & Map Buttons adds a sticky mobile contact bar with optional Call Now and Directions buttons. It is useful for local businesses that want mobile visitors to call or open directions quickly.

= Features =

* Add a mobile Call Now button using a phone number.
* Add a Directions button using a street address or GPS coordinates.
* Add an optional Google Place ID for a more precise Google Maps destination.
* Customize bar background, button colors, and button text.
* Choose the maximum device width where the buttons appear.
* Optional z-index and append-to-body settings for theme compatibility.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/mobile-call-now-map-buttons` directory, or install the plugin through the WordPress plugins screen.
2. Activate the plugin through the Plugins screen in WordPress.
3. Go to Settings > Mobile Call Now & Map Buttons to configure the plugin.

== Frequently Asked Questions ==

= How is the maps button created? =

The location is built with a Google Maps URL. Your entered location values are constructed using either the address fields or GPS coordinates. If you add a Google Place ID, the plugin appends it to the Maps URL to help Google identify the exact destination.

= What format should I use for GPS coordinates? =

Use decimal degrees format, such as `50.6745` for latitude and `-120.3273` for longitude. Do not enter degree symbols or N/E/S/W letters.

= Why are the buttons hiding my footer? =

This is usually a theme CSS conflict. Try adjusting the z-index setting or enable the append-to-body option.

= What does the number sanitizing option do? =

By default, the plugin cleans the phone number for safer and more consistent `tel:` links. Enable this option only if your phone number format must be preserved.

== Screenshots ==

1. Settings page.
2. Mobile contact bar with Call Now and Directions buttons.

== Changelog ==

= 1.6.2 =
* Fixed front-end output logic so the plugin can display Call Now, Directions, or both buttons depending on configured settings.
* Improved validation for phone, address, GPS, and Google Place ID values.
* Additional security hardening for CVE-2023-24401 by sanitizing stored settings and escaping admin/front-end output.

= 1.6.1 =
* Added optional Google Place ID support for map destinations.
* Added admin help links for Google Place ID documentation and the Place ID finder.
* Updated Google Maps URL generation to use the Maps Directions URL format.

= 1.6.0 =
* Security hardening release.
* Added settings sanitization callback.
* Escaped admin and frontend output.
* Restricted settings page access to administrators using `manage_options`.
* Updated frontend link handling for `tel:` and Google Maps URLs.
* Updated JavaScript to use current jQuery patterns and removed debug logging.
* Updated plugin headers for current WordPress and PHP compatibility.

= 1.5.0 =
* Readme and documentation change.
* Added PHP version requirement.

= 1.4.2 =
* Minor change to initialization.

= 1.4.1 =
* Fixed CSS where one-button use could result in an off-center button.
* Added direct file access protection.

== Upgrade Notice ==

= 1.6.2 =
Security hardening release for CVE-2023-24401. Existing plugin settings are preserved.

= 1.6.1 =
Adds optional Google Place ID support while preserving existing plugin settings.

= 1.6.0 =
Security hardening release. Existing plugin settings are preserved.
