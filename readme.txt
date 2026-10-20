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

[Contact Form 7 (CF7)](https://wordpress.org/plugins/contact-form-7/) is one of the most common form plugins on WordPress. This plugin takes what people submit through a CF7 form and sends it to an ActionNetwork form, petition, event, fundraising page or advocacy campaign. We built it for nonprofits, advocacy groups and campaigns that already collect sign-ups on their WordPress site and want them to land in ActionNetwork.

What it does:

* Recognizes common field names like `your-email` or `first-name` and maps them to ActionNetwork's person fields.
* Fills in the country when your form doesn't ask for it.
* Adds or removes ActionNetwork tags on each submission.
* Lets you turn ActionNetwork's autoresponse on or off, and skips CF7's own emails so people don't get two.
* Records a source name and the referring URL.
* Retries failed requests with exponential backoff.
* Uses nonces, sanitization and capability checks, as WordPress.org requires.
* Checks the ActionNetwork URL as you type it in the admin screen.

ActionNetwork and the CF7 developers had nothing to do with this plugin.

= How to use it =

1. Create a Form, Petition, Event, Fundraising Page or Advocacy Campaign in ActionNetwork.
2. While editing it, copy the API Endpoint URL from the right sidebar.
3. In Contact Form 7, open the "ActionNetwork" tab of your form and paste the URL. That's enough to turn the integration on; there's no separate switch.
4. If you want, set up country detection, tags, a source name and autoresponse.

= Supported ActionNetwork actions =

The plugin works out the action type from the URL and posts to the right endpoint:

* Forms → `/submissions`
* Petitions → `/signatures`
* Events → `/attendances`
* Fundraising pages → `/donations`
* Advocacy campaigns → `/outreaches`

= Field mapping =

If your CF7 fields use one of these names, the plugin maps them for you:

* Email address: `your-email`, `email`, `email-address`
* First name: `your-name`, `first-name`, `firstname`, `given-name`
* Last name: `your-last-name`, `last-name`, `lastname`, `family-name`, `surname`
* Phone number: `your-phone`, `phone`, `phone-number`, `telephone`, `mobile`
* Street address: `your-address`, `address`, `street-address`
* City: `your-city`, `city`, `locality`, `town`
* State/region: `your-state`, `state`, `province`, `region`
* Postal code: `your-zip`, `zip`, `zipcode`, `postal-code`
* Country: `your-country`, `country`

Any other field goes to ActionNetwork as a custom field.

= Other options =

Country detection looks up the visitor's country from their IP address, CloudFlare's country header, or their browser language.

Tags: give a comma-separated list of tags to add to each new contact, and another of tags to remove. Both run on every submission.

Source tracking: set a source name for the campaign. The plugin also sends the referring URL and picks up `?source=` from the page URL.

== Frequently Asked Questions ==

= Does it work with Gutenberg and WordPress 6+? =
Yes. It works with WordPress 5.0 and later, including the block editor.

= How do I know it's working? =
Submit a test form, then look for the new submission in your ActionNetwork action. Failed requests are retried and logged.

= Can I use it with webhooks other than ActionNetwork? =
No, it only speaks ActionNetwork's API. For generic webhooks, try [CF7 to Webhook](https://github.com/mariovalney/cf7-to-zapier).

= What happens to file uploads? =
The plugin copies uploaded files to a separate directory and sends their URLs to ActionNetwork before CF7 deletes the temporary files.

= Can I map a field myself? =
Yes. Add the `actionnetwork` option to the field tag:
`[text your-field actionnetwork:custom_field_name]`

= What happens to CF7's emails? =
Once an ActionNetwork URL is set, CF7's emails are turned off by default so people don't get duplicates. ActionNetwork's autoresponse has its own setting.

= How does country detection work? =
It tries these in order:

1. GeoIP functions, if your server has them
2. The free ipapi.co service
3. CloudFlare's country header
4. The browser's language settings

= Can one form send to several ActionNetwork actions? =
Yes. Put one URL per line and each one gets the submission.

== External Services ==

The plugin talks to two outside services.

= ActionNetwork API =
Sending form submissions to ActionNetwork is the whole point of the plugin.

What gets sent: everything submitted through the Contact Form 7 form. That includes names, email addresses, phone numbers, addresses and any custom fields, formatted as an ActionNetwork person record.

When: every time someone submits a CF7 form that has ActionNetwork set up.

Service provider: ActionNetwork  
Terms of service: https://actionnetwork.org/terms  
Privacy policy: https://actionnetwork.org/privacy

= IP geolocation (ipapi.co) =
Used to look up the visitor's country when country detection is on and the other methods can't find it.

What gets sent: the visitor's IP address.

When: only when country detection is on, and the form was submitted without a country or the other detection methods failed.

Service provider: ipapi.co  
Terms of service: https://ipapi.co/terms/  
Privacy policy: https://ipapi.co/privacy/

The plugin only calls ipapi.co after GeoIP functions, CloudFlare headers and the browser language have all come up empty. If you'd rather not use it, turn off country detection in the plugin settings.

== Installation ==

1. Install and activate [Contact Form 7](https://wordpress.org/plugins/contact-form-7/).
2. Install "Form Integration for Action Network and Contact Form 7" from the Plugins screen, or upload the `form-integration-for-action-network-and-contact-form-7` folder to `/wp-content/plugins/`.
3. Activate it from the Plugins menu.
4. Open any form in CF7 and you'll see an "ActionNetwork" tab.

== Screenshots ==
1. The main settings screen. You only need to paste the Action Network API Endpoint here.
2. The plugin matches your CF7 fields to Action Network's core fields.
3. Adding and removing tags for people who submit the form.

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
