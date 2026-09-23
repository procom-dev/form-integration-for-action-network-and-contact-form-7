# Upstream reconciliation ledger

This plugin began as a rewrite of **CF7 to Zapier** (now published as
**"CF7 to Webhook"**) by mariovalney:

- WordPress.org: https://wordpress.org/plugins/cf7-to-zapier/
- Source: https://github.com/mariovalney/cf7-to-zapier
- readme (canonical changelog): https://plugins.svn.wordpress.org/cf7-to-zapier/trunk/readme.txt

Because this is a rewrite (renamed prefixes `ctz_`/`CTZ_` → `cfan_`/`CFAN_`,
restructured modules, and ActionNetwork-specific data formatting instead of a
generic webhook body), upstream changes **cannot be merged or cherry-picked**.
Each upstream release is reviewed entry by entry and relevant changes are
re-implemented by hand.

## How to reconcile a new upstream release

1. Read upstream's changelog (link above) down to the last version marked
   "Reconciled" in the table below.
2. For each newer entry classify it:
   - **Security** → port unless clearly N/A to our architecture.
   - **Generic feature** (data-source / CF7 behavior) → evaluate, port if useful.
   - **Webhook-specific** (custom body, templates, base64 files, Slack, generic
     header/method handling) → skip; does not fit ActionNetwork's fixed API.
3. Record the decision in the table and bump `UPSTREAM_RECONCILED_TO` below.

**Last reconciled upstream version:** `5.1.0` (as of 2026-09-23)

## Reconciliation table

| Upstream | Change | Class | Decision |
|---|---|---|---|
| 5.1.0 | XSS fixes in admin output helpers | Security | N/A — our panel escapes all echoed values (`esc_attr`/`esc_textarea`); `custom_headers`/`special_mail_tags` are never echoed |
| 5.1.0 | Capability check (`wpcf7_edit_contact_form`) on save | Security | Already present (CF7 module save handler) |
| 5.1.0 | HTTP method whitelist | Security | N/A — method is hardcoded to `POST` |
| 5.1.0 | Redact sensitive headers in notification emails | Security | N/A — no error-notification-email feature |
| 5.1.0 | `random_bytes()` instead of `uniqid()` for upload dirs | Security | **Ported** in 1.0.1 |
| 5.1.0 | `wp_add_inline_script()` instead of raw `<script>` echo | Security | N/A — already using `wp_localize_script` |
| 5.0.1 | SSRF: `wp_safe_remote_request()` (CVE-2026-11395) | Security | **Ported** in 1.0.1 (`wp_safe_remote_post`/`wp_safe_remote_get`) |
| 5.0.0 | CF7 Multi-Step Forms support + `*_cf7msm_posted_data` filter | Feature | **Ported** in 1.0.2 (`cfan_get_data_from_cf7msm_posted_data`) |
| 5.0.0 | Mail tags in headers / headers replaceable by data | Feature | Deferred — evaluate with the header feature |
| 4.0.x | Templates, advanced custom body, base64 files, Slack template, error notifications | Webhook-specific | Skip — does not fit ActionNetwork's structured person API |
| ≤3.x | Placeholders, multiple URLs, raw values, custom header option | Mixed | Already covered by the rewrite / not applicable |
