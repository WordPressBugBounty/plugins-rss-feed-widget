=== RSS Feed Widget ===
Contributors: fahadmahmood, invoicepress
Tags: rss, feed, widget, slider, shortcode
Requires at least: 5.8
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 4.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Show any RSS or Atom feed as a clean list or accessible slider with a widget, a block or a shortcode. Cached, secure and lightweight.

== Description ==

RSS Feed Widget turns any RSS or Atom feed into a tidy, fast and accessible block of content on your site. Use it as a **widget**, a **Gutenberg block** or a **shortcode**.

**Highlights**

* Widget, "RSS Feed" block and `[rfw-feed-widget]` shortcode, all with the same options
* Layouts: list or slider (a small dependency-free script, keyboard accessible, respects "reduce motion")
* Show or hide titles, images, text, date and feed name; limit the words shown; open links in a new tab
* Smart image detection from enclosures, media thumbnails and the item content
* Sort by feed order, newest, oldest, A-Z, Z-A or random
* Built-in caching with a "last good copy" fallback if a feed goes down
* Mute words and phrases site-wide, or hide words per widget
* Shortcode builder and a feed tester in Settings > RSS Feed Widget
* `[rfw-youtube-videos]` shortcode for privacy-friendly YouTube embeds (youtube-nocookie.com)
* No jQuery, no Bootstrap, no tracking, nothing loaded on pages that do not show a feed

**Shortcode example**

`[rfw-feed-widget rss_url="https://wordpress.org/news/feed/" number="5" list_type="list" show_feed_title="true" keep_feed_link="true" show_date="true"]`

**Pro (optional add-on)**

Pro is plug and play: copy its `pro` folder into the plugin folder and the extra features switch on. Remove the folder and the free plugin keeps working.

* Combine up to 10 feeds in one widget, merged by date, duplicates removed
* Include / exclude keyword filters and "only the last N days"
* Grid and news-ticker layouts
* Auto-import feed items as posts on a schedule, with a link to the source
* Custom image tag support for unusual feeds

**External services**

* The feed URLs you enter are fetched by your server (and cached). Nothing is sent to us.
* The optional YouTube shortcode embeds videos from `youtube-nocookie.com` (Google LLC, [privacy policy](https://policies.google.com/privacy)). If you add a Google API key and a channel ID, your server calls the YouTube Data API (`googleapis.com`) to find the latest videos ([terms](https://developers.google.com/youtube/terms/api-services-terms-of-service)).

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/`, or install it from the Plugins screen.
2. Activate it.
3. Go to Appearance > Widgets, add "RSS Feed Widget" and paste a feed URL. Or add the "RSS Feed" block in the editor. Or use Settings > RSS Feed Widget > Shortcode builder.

== Frequently Asked Questions ==

= Is it safe to update from 3.x? =
Yes. Existing widgets and shortcodes keep working: the widget ID, shortcode names and attributes are unchanged. Feed output now uses the active theme's widget wrapper, and the cache is rebuilt after the update.

= What happened to Facebook Page IDs? =
Facebook no longer allows reading page posts without your own app credentials, so Page IDs are not supported. Use a feed URL instead.

= A feed does not show. What can I check? =
Use Settings > RSS Feed Widget > Tools > Test feed. Feeds that point to private or local addresses are blocked on purpose.

= How long are feeds cached? =
One hour by default. Change it in Settings > General, or per widget with the "Cache" field. The minimum is 60 seconds.

= Can I style it? =
Yes. The markup uses simple classes such as `.rfw-feed`, `.rfw-item`, `.rfw-item-title` and `.rfw-item-text`. The older class names (`rfw-class`, `rfw_dock`, `feed_img`, `text_div`) are kept, so existing custom CSS keeps working.

= How do I get Pro? =
Download the Pro package from the product page and copy its `pro` folder into `wp-content/plugins/rss-feed-widget/`. WordPress.org updates replace the plugin folder, so re-add the `pro` folder (or use the Pro package) after an update.

== Changelog ==

= 4.0.0 =
* Rebuilt from the ground up for current WordPress (tested with 7.1) and PHP 7.4 to 8.x.
* Security: removed embedded third-party API tokens; replaced unserialize() of stored data; escaped all output; all feed content is reduced to plain text and cached as plain data; feed fetching uses WordPress' safe HTTP API and blocks private and reserved addresses; admin actions are capability- and nonce-checked; removed the public debug parameters.
* New: "RSS Feed" Gutenberg block.
* New: lightweight accessible slider replaces bxSlider; jQuery, Bootstrap and jquery.form are no longer loaded.
* New: date and feed-name display, "open in new tab" option, autoplay and timing controls.
* New: shortcode builder, feed tester and cache tools in Settings.
* New: cached feeds fall back to the last good copy when a feed is down.
* New: widget now uses the theme's widget wrapper and title markup.
* Changed: Facebook Page IDs are no longer supported (they relied on embedded tokens).
* Changed: shortcode default for number of items is 6.
* New (Pro): plug-and-play "pro" folder with multi-feed, filters, grid and ticker layouts, auto-import to posts and custom image tag.

= 3.0.3 =
* Maintenance release.

= 3.0.2 =
* New: content_display="linked_title" shortcode option.

= 3.0.1 =
* Fix: escape REQUEST_URI to prevent reflected XSS in old browsers.

= 3.0.0 =
* Fix: stored XSS in a shortcode.

== Upgrade Notice ==

= 4.0.0 =
Major security and compatibility release. Facebook Page IDs are no longer supported. Existing widgets and shortcodes keep working.
