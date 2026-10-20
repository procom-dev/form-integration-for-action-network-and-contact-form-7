![Form Integration for Action Network and Contact Form 7](assets/banner-772x250.png)

# Form Integration for Action Network and Contact Form 7

Send [Contact Form 7](https://wordpress.org/plugins/contact-form-7/) submissions to an ActionNetwork form, petition, event, fundraising page or advocacy campaign.

By [procom.dev](https://procom.dev), based on the original plugin by [Mário Valney](https://mariovalney.com/me). Requires WordPress 4.7+, PHP 7.4+ and Contact Form 7. Tested up to WordPress 7.1. GPLv2 or later. ActionNetwork and the CF7 developers had nothing to do with this plugin.

## How to use it

1. Install and activate Contact Form 7, then this plugin.
2. In ActionNetwork, open your action and copy the API Endpoint URL from the right sidebar.
3. In CF7, open your form's "ActionNetwork" tab and paste the URL. That turns the integration on.

Common field names like `your-email`, `your-name` or `your-phone` are mapped to ActionNetwork's person fields; the ActionNetwork tab lists every name it recognizes. Anything else is sent as a custom field. To send a field somewhere specific, add the `actionnetwork` option to its form tag: `[text your-nickname actionnetwork:given_name]`.

The plugin can also fill in the visitor's country, add or remove tags, and send a source name with the referring URL. It turns off CF7's own emails by default so people don't get two; ActionNetwork's autoresponse has its own switch. Put one URL per line to send a form to several actions.

Country detection may send the visitor's IP address to [ipapi.co](https://ipapi.co/privacy/) when the server's GeoIP data can't tell. Turn it off if you don't want that.

## Screenshots

![Configuration](assets/screenshot-1.png)
*Paste the ActionNetwork API Endpoint and you're set.*

![Field mapping](assets/screenshot-2.png)
*Field mapping: the CF7 field names the plugin recognizes for each ActionNetwork field.*

![Tags](assets/screenshot-3.png)
*Tags to add to or remove from each person.*

## Changelog

### 1.0.4
* New: you can turn off automatic field mapping per form (it's on by default). With it off, only the exact ActionNetwork field names (`email`, `given_name`, `family_name`, `phone_number`, `address_lines`, `locality`, `region`, `postal_code`, `country`) are mapped, and every other field is sent as a custom field.
* The Field Mapping section in the ActionNetwork tab is easier to read. Its table of recognized field names is generated from the same list the plugin uses, so the two always match.
* Added `Requires at least` and `Requires PHP` to the plugin header. Tested up to WordPress 7.1.3.

### 1.0.3
* Fix: the admin styles and scripts now load on every Contact Form 7 editor screen, including "Add New". The ActionNetwork tab used to show up unstyled there because asset loading needed a `post` query argument.

### 1.0.2
* New: works with the "Contact Form 7 Multi-Step Forms" (CF7MSM) plugin. Fields from earlier steps are merged into the submission sent to ActionNetwork (ports upstream 5.0.0).
* New filter `cfan_get_data_from_cf7msm_posted_data` to turn off CF7MSM data merging, or decide case by case.

### 1.0.1
* Security: outbound requests use the "safe" HTTP API (`wp_safe_remote_post`/`wp_safe_remote_get`), which blocks requests to private, loopback and link-local addresses (SSRF hardening; ports upstream 5.0.1 / CVE-2026-11395).
* Security: upload directories get cryptographically random names instead of guessable `uniqid()` ones (ports upstream 5.1.0).

### 1.0.0
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
