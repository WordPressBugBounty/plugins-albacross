=== Albacross – B2B Website Visitor Identification ===
Contributors: albacross
Tags: b2b, lead generation, visitor identification, analytics, abm
Requires at least: 5.7
Requires PHP: 7.4
Tested up to: 7.0.2
Stable tag: 1.6.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

See which companies visit your WordPress site, what they read, and how ready they are to buy. Adds the Albacross script in one click.

== Description ==

**Most of your B2B traffic never fills in a form. Albacross tells you who they were anyway.**
 
Albacross for WordPress adds the Albacross website visitor identification script to your site in one click — no code, no theme edits, no tag manager required. Paste your Client ID, save, and you are tracking.
 
Albacross turns anonymous traffic into a list of real companies. See which businesses visit your site, which pages they read, and how engaged they are — then push that data into your CRM and sales workflows.

== Installation ==

1. Go to Plugins -> Add New, then search and install Albacross plugin
2. Go to Settings -> Albacross
3. Enter your client ID that can be obtained from your settings page on https://app.albacross.com/

== Frequently Asked Questions ==

= What is my client ID? =

Your client ID can be found by signing in on [app.albacross.com](https://app.albacross.com/account/tracking?tab=wp) and going to your settings.

= Do I need an Albacross account to use this plugin? =
 
Yes, an account is required — the plugin only installs the tracking script, while identification, enrichment and integrations happen in your Albacross workspace.
 
= Does the plugin slow down my site? =
 
No. The tracking script is loaded asynchronously and in the footer, so it never blocks rendering of your pages. The plugin itself adds a single database option lookup per page load, loads no CSS or fonts, and adds nothing to your admin screens beyond one settings page.

= Does it work with caching plugins such as WP Rocket, W3 Total Cache or LiteSpeed Cache? =
 
Yes. The script tag is part of the page HTML, so it is cached and served like the rest of your page, and the tracking itself happens in the visitor's browser.
 
Two things to check if you use aggressive optimisation settings:
 
* **Delay JavaScript execution / lazy-load JS** — this can postpone tracking until the visitor interacts with the page. Add `serve.albacross.com` and `track.js` to the exclusion list.
* **Combine or defer JavaScript** — exclude `track.js` so the script keeps its own async loading.

= Can I use this alongside Google Tag Manager? =
 
You can, but do not install the Albacross script twice. Use either this plugin or a GTM tag, not both.
 
= Where do I get help? =
 
For questions about the plugin itself, use the [support forum](https://wordpress.org/support/plugin/albacross/). For questions about identification, data, billing or integrations, see the [Albacross Help Center](https://help.albacross.com/) or contact Albacross support from inside the app.

== Screenshots ==

1. Every company that visits your WordPress site, identified and updated in real time.
2. Company profiles enriched with industry, size, revenue, location and technologies used.
3. Filter and segment visitors by firmographics, behaviour and UTM parameters to find your best-fit accounts.
4. Route high-intent accounts to HubSpot, Salesforce, Pipedrive, Slack, LinkedIn Ads and 30+ other tools.

== Changelog ==

= 1.6.1 =
* Plugin loader fixed.

= 1.6.0 =
* Prevented JavaScript optimization plugins and CDNs from delaying,
  combining, or rewriting the Albacross configuration and tracker.
* Added compatibility with LiteSpeed Cache, WP Rocket, Autoptimize,
  SiteGround Speed Optimizer, Jetpack Boost, Hummingbird, Perfmatters,
  W3 Total Cache, FlyingPress, and Cloudflare Rocket Loader.

= 1.5.0 =
* Updated screenshots

= 1.5.0 =
* Redesigned settings screen: setup steps, a link straight to your Client ID, a status indicator showing whether tracking is live, and built-in troubleshooting.
* Tracking script is now loaded through the WordPress script API, for better compatibility with block themes and JavaScript optimisation plugins.
* Added the `albacross_should_load` filter so consent management plugins can control when the script loads, and the `albacross_client_id` filter for programmatic configuration.
* Updated installation instructions and FAQ.

= 1.4.1 =
* Compatibility update

= 1.4 =
* Compatibility update

= 1.3.3 =
* Compatibility update

= 1.3.2 =
* Resolved issue with blank page

= 1.3.1 =
* Fixed long loading time

= 1.3 =
* Code cleanup and miscellaneous fixes

= 1.2.3 =
* Fixed compatibility problems

= 1.2.2 =
* Fixed and tested for compatibility with latest version of WordPress

= 1.2.1 =
* Fixed the admin notice link

= 1.2 =
* Updated tracking code to reflect the latest changes

= 1.1 =
* Added source for WordPress plugin

= 1.0 =
* First version

== Upgrade Notice ==
