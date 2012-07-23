=== Ubiety ===
Contributors: yoyojamfl
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=GMXNW6GGYR5LG&lc=US&item_name=Payden%20Sutherland&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donate_LG%2egif%3aNonHosted
Tags: chat, ajax, instant, messaging, pusher
Requires at least: 3.4.1
Tested up to: 3.4.1
Stable Tag: 0.0.1
License: GPLv2

This plug-in provides a simple chat system integrated into your blog.

== Description ==

This plugin provides a simple chat system for your blog.  It utilizes the [Pusher](http://pusher.com "Pusher") service to provide real time communications using web sockets.
You must sign up for a free account at [pusher.com](http://pusher.com "Pusher") and configure Ubiety under the WordPress administration panel to use this plugin.

== Installation ==


1. Signup for a free account at [Pusher](http://pusher.com "Pusher").
2. Upload `ubiety` directory to the `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Navigate to Plugins -> Ubiety Configuration under WordPress administration panel and fill in your Pusher details.

== Frequently Asked Questions ==

= The bottom bar doesn't show up at all! =

I've run into this problem with people using themes that don't properly call wp_footer(); in their footer.php
Please ensure you have this line in footer.php in your wp-content/themes/whatever/footer.php file:
`<?php wp_footer(); ?>`

= Is Internet Explorer 6 Supported? =

No.

= Such & such doesn't work =

Email me! payden@paydensutherland.com

== Screenshots ==

1. A view of the integrated chat bar with chat window closed.
2. And with chat window open.

== Changelog ==

= 0.0.1 =
* First version, just a PoC.
