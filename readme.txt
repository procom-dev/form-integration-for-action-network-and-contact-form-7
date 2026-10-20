=== Form Integration for Action Network and Contact Form 7 ===
Contributors: procomdev, mariovalney
Tags: contact form 7, action network, cf7, actionnetwork, integration
Requires at least: 4.7  
Tested up to: 7.1
Stable tag: 1.0.4
Requires PHP: 7.4  
License: GPLv2 or later  
License URI: http://www.gnu.org/licenses/gpl-2.0.html  

Send Contact Form 7 submissions to ActionNetwork, with automatic field mapping, country detection and tags.

== Description ==

Paste an ActionNetwork API endpoint into a [Contact Form 7](https://wordpress.org/plugins/contact-form-7/) form and every submission goes to that ActionNetwork form, petition, event, fundraising page or advocacy campaign.

* Common field names like `your-email`, `your-name` or `your-phone` are mapped to ActionNetwork's person fields. The ActionNetwork tab lists every name it recognizes. Anything else is sent as a custom field.
* Optionally fills in the visitor's country, and adds or removes tags.
* Sends a source name and the referring URL.
* Turns off CF7's own emails by default so people don't get two; ActionNetwork's autoresponse has its own switch.
* Retries failed requests.

ActionNetwork and the CF7 developers had nothing to do with this plugin.

== Installation ==

1. Install and activate [Contact Form 7](https://wordpress.org/plugins/contact-form-7/), then this plugin.
2. In ActionNetwork, open your action and copy the API Endpoint URL from the right sidebar.
3. In CF7, open your form's "ActionNetwork" tab and paste the URL. That turns the integration on.

== Frequently Asked Questions ==

= Can I send a field to a specific ActionNetwork field? =
Yes. Add the `actionnetwork` option to the form tag: `[text your-nickname actionnetwork:given_name]`

= Can one form send to several ActionNetwork actions? =
Yes. Put one URL per line.

= What happens to file uploads? =
The plugin copies uploaded files to a separate directory and sends their URLs to ActionNetwork.

== External Services ==

= ActionNetwork API =
Every submission of a form that has ActionNetwork set up is sent to ActionNetwork: names, email addresses, phone numbers, addresses and any other fields in the form. This is the plugin's whole purpose.
Terms: https://actionnetwork.org/terms  
Privacy: https://actionnetwork.org/privacy

= ipapi.co =
If country detection is on, the form has no country, and the server's GeoIP data can't tell, the visitor's IP address is sent to ipapi.co to look up their country. Turn off country detection to avoid this.
Terms: https://ipapi.co/terms/  
Privacy: https://ipapi.co/privacy/

== Screenshots ==
1. Paste the ActionNetwork API Endpoint and you're set.
2. Field mapping: the CF7 field names the plugin recognizes for each ActionNetwork field.
3. Tags to add to or remove from each person.

== Changelog ==

= 1.0.4 =
* New: you can turn off automatic field mapping per form (it's on by default). With it off, only the exact ActionNetwork field names (`email`, `given_name`, `family_name`, `phone_number`, `address_lines`, `locality`, `region`, `postal_code`, `country`) are mapped, and every other field is sent as a custom field.
* The Field Mapping section in the ActionNetwork tab is easier to read. Its table of recognized field names is generated from the same list the plugin uses, so the two always match.
* Added `Requires at least` and `Requires PHP` to the plugin header. Tested up to WordPress 7.1.3.

= 1.0.3 =
* Fix: the admin styles and scripts now load on every Contact Form 7 editor screen, including "Add New". The ActionNetwork tab used to show up unstyled there because asset loading needed a `post` query argument.

= 1.0.2 =
* New: works with the "Contact Form 7 Multi-Step Forms" (CF7MSM) plugin. Fields from earlier steps are merged into the submission sent to ActionNetwork (ports upstream 5.0.0).
* New filter `cfan_get_data_from_cf7msm_posted_data` to turn off CF7MSM data merging, or decide case by case.

= 1.0.1 =
* Security: outbound requests use the "safe" HTTP API (`wp_safe_remote_post`/`wp_safe_remote_get`), which blocks requests to private, loopback and link-local addresses (SSRF hardening; ports upstream 5.0.1 / CVE-2026-11395).
* Security: upload directories get cryptographically random names instead of guessable `uniqid()` ones (ports upstream 5.1.0).

= 1.0.0 =
* Rewrote the ActionNetwork API integration.
* Automatic mapping for 25+ common field names.
* Country detection, with several fallback methods.
* Add and remove ActionNetwork tags.
* Nonces, sanitization and capability checks for WordPress.org.
* New admin screen with live URL validation.
* The integration turns on as soon as you add a URL.
* Better referrer and source tracking.
* Exponential backoff when requests fail.
* Settings for ActionNetwork's autoresponse and CF7's emails.
* Send one form to several ActionNetwork actions.
* More logging and better error handling.
* Faster data processing.
