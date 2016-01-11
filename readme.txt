=== Payfort (Start) ===
Contributors: jamesmay
Donate link: https://start.payfort.com/
Tags: payfort, payfort start, payments, middle east payments, credit card, middle east, online payments, credit cards, debit cards, sadad, UAE online payment, payment gateway, payment gateway, start.payfort.com
Requires at least: 3.0.1
Tested up to: 4.3.1
Stable tag: 0.1.5
License: MIT
License URI: http://opensource.org/licenses/MIT

Payfort Start makes it easy (and free) to accept credit cards in the Middle East. You'll be live in 5 minutes.

== Description ==

Payfort is the only payment solution you need to start accepting online payments in the Middle East. That's right, no merchant accounts, no payment gateways and no visits to the bank. Get started online, and in under 5 minutes. (*Currently exclusively available to companies in the Middle East*)

= Using Wordpress + WooCommerce? =

Then you're going to love this!

This WooCommerce plugin makes integrating your store with Payfort a walk in the park! In the same time it would take you to make a cup of some great turkish coffee, you could be up and running with online payments!

Doesn't matter if you're in Bahrain, the UAE or Morocco .. it'll work fine either way!

= Requirements =

In order to start using Payfort, please head on over to [our website](https://start.payfort.com/) and register for an online account (it'll only take a minute; we'll wait..)

**Got the account?** Awesome .. now make sure to note the following details from your Dashboard:

- Test Secret Key
- Test Open Key
- Live Secret Key
- Live Open Key

We'll need these when we're setting up the plugin. Best part is .. you don't even have to be a developer to do the setup. It'll only take a minute.

== Installation ==

1. Install the [Payfort plugin](https://wordpress.org/plugins/payfort) from your Wordpress Dashboard, under Plugins &rarr; Add New section.
2. Activate the plugin
3. Configure the options in the WooCommerce &rarr; Settings &rarr; Checkout screen. This includes the API keys you got from your [Payfort Dashboard](https://dashboard.start.payfort.com/)

== Frequently Asked Questions ==

= What version of PHP is required for this plugin to work? =

Use PHP v5.4 or later.

== Changelog ==

= 0.1.0 =
- Fix the link to our testing cards
- Skip currency check

= 0.0.15 =
- Update charge description to show the Order number

= 0.0.14 =
- Fix link to local dev domain

= 0.0.13 =
- Set label of the button in payment form

= 0.0.12 =
- Use improved checkout flow by entering card details
- Fix double token usage issue
- Update php client library

= 0.0.11 =
- Deploy issue fixed so we can better deploy in the future

= 0.0.9 =
- Fix readme markdown syntax errors (and send the dev back to markdown-school)

= 0.0.8 =
- Fix a broken link in the plugin description page

= 0.0.7 =
- Yaay for plugin icons!

= 0.0.6 =
- Nothing serious .. Just prettied up the ReadMe and removed old references to White.

= 0.0.5 =
- You know how you sometimes think you fixed something, but actually didn't. Well, this *really* fixes 0.0.4 (we hope)

= 0.0.4 =
- There was an issue with the vendor/ folder not getting included in the git repo, resulting in an argument between Wordpress and our plugin. This has now been resolved, and we're back to being best friends.
