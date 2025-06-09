# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a WordPress plugin that integrates Contact Form 7 (CF7) with ActionNetwork. The plugin automatically maps CF7 form fields to ActionNetwork's API structure, sending form submissions to ActionNetwork endpoints with comprehensive security, validation, and error handling.

## Architecture

The plugin follows a modular architecture with enhanced security and modern WordPress development practices:

### Core Plugin Structure
- **Main Plugin File**: `cf7-to-actionnetwork.php` - Entry point with `CFAN_Core` orchestrator
- **Core Class**: `CFAN_Core` - Manages hooks, filters, module loading, and plugin constants
- **Module System**: Two specialized modules in `/modules/` directory
- **Logging System**: `includes/class-cfan-logger.php` - Comprehensive logging with WP_DEBUG integration

### Modules Architecture
- **CF7 Module** (`modules/cf7/class-module-cf7.php`): Complete CF7 integration
  - Admin interface with modern UI (`admin/actionnetwork-panel-html.php`)
  - CSS/JS assets for real-time validation (`admin/assets/`)
  - Security-first form processing with nonce verification
  - Smart field mapping with `format_data_for_actionnetwork()` method
  - File upload handling and special mail tags support

- **ActionNetwork Module** (`modules/actionnetwork/class-module-actionnetwork.php`): Robust API communication
  - URL validation for ActionNetwork endpoints
  - Exponential backoff retry system for failed requests
  - Automatic endpoint detection (forms, petitions, events, etc.)
  - Comprehensive request/response logging

### Data Flow (Enhanced)
1. User submits CF7 form
2. CF7 module captures data via `wpcf7_mail_sent` hook
3. **Security validation**: Nonce verification + user capability checks
4. **Smart data formatting**: `format_data_for_actionnetwork()` method:
   - Automatic field mapping (e.g., `your-email` → `email_addresses[0].address`)
   - Proper ActionNetwork person object structure
   - Source tracking and referrer data inclusion
   - Custom fields for unmapped data
5. **URL validation**: Ensures valid ActionNetwork endpoint
6. **Retry-enabled transmission**: HTTP POST with exponential backoff
7. **Comprehensive logging**: Request/response details for debugging

### Field Mapping Intelligence
The plugin includes smart mapping for common CF7 field patterns:
- Email fields: `your-email`, `email`, `email-address` → ActionNetwork email address
- Name fields: `your-name`, `first-name` → `given_name`; `your-last-name`, `family_name` → `family_name`
- Contact: `your-phone`, `phone` → `phone_numbers`; `your-address` → `postal_addresses`
- Location: `your-city` → `locality`; `your-state` → `region`; `your-zip` → `postal_code`

## Security Implementation

### WordPress.org Compliance
- **Unique prefixes**: All functions/classes use `cfan_` or `CFAN_` prefix
- **Text domain**: Consistent `cf7-actionnetwork-integration` throughout
- **Nonce verification**: All admin forms protected with `wp_nonce_field()`
- **Capability checks**: `current_user_can('wpcf7_edit_contact_form')` validation
- **Input sanitization**: `sanitize_text_field()`, `sanitize_url()`, `sanitize_email()`
- **Output escaping**: `esc_html()`, `esc_attr()`, `esc_url()` for all output

### Admin Interface Security
- Form field names use `cfan-*` convention
- Real-time JavaScript validation with server-side verification
- Visual feedback for URL validation (success/error/warning states)

## Development Standards

### Naming Conventions
- **Classes**: `CFAN_Core`, `CFAN_CF7_Module`, `CFAN_ActionNetwork_Module`
- **Functions**: `cfan_function_name()`
- **Constants**: `CFAN_VERSION`, `CFAN_PLUGIN_URL`, `CFAN_TEXTDOMAIN`
- **Hooks**: `cfan_trigger_actionnetwork`, `cfan_formatted_data`
- **Form fields**: `cfan-actionnetwork-activate`, `cfan-actionnetwork-hook-url`

### File Organization
```
cf7-to-actionnetwork-master/
├── cf7-to-actionnetwork.php (main plugin file)
├── includes/
│   ├── class-cfan-logger.php (logging system)
│   └── functions-debug.php (debug utilities)
├── modules/
│   ├── cf7/
│   │   ├── class-module-cf7.php (CF7 integration)
│   │   └── admin/ (admin interface + assets)
│   └── actionnetwork/
│       └── class-module-actionnetwork.php (API communication)
└── languages/ (internationalization)
```

## Key Functions & Hooks

### Action Hooks
- `cfan_trigger_actionnetwork` - Main hook for sending data to ActionNetwork
- `cfan_post_request_result` - Post-request processing hook
- `cfan_trigger_actionnetwork_errors` - Error handling hook

### Filter Hooks
- `cfan_formatted_data` - Modify data before sending to ActionNetwork
- `cfan_hook_url` - Modify the ActionNetwork URL
- `cfan_post_request_args` - Modify HTTP request arguments
- `cfan_hook_url_placeholder` - Customize URL placeholder replacement
- `cfan_get_data_from_contact_form` - Filter form data extraction
- `cfan_get_data_from_special_mail_tags` - Filter special mail tag data

### Logging Functions
- `CFAN_Logger::info($message)` - Info level logging
- `CFAN_Logger::warning($message)` - Warning level logging
- `CFAN_Logger::error($message)` - Error level logging
- `CFAN_Logger::log_api_request($url, $data, $response)` - API request logging

## Modern Features

### Admin Interface
- Responsive CSS design (`modules/cf7/admin/assets/admin.css`)
- Real-time URL validation JavaScript (`modules/cf7/admin/assets/admin.js`)
- Visual feedback for ActionNetwork URL validation
- Clear field mapping documentation in admin
- Mobile-friendly interface design

### API Integration
- Automatic ActionNetwork action type detection
- Support for all ActionNetwork endpoints (forms, petitions, events, fundraising, advocacy)
- 30-second HTTP timeout configuration
- Exponential backoff retry (3 attempts with 1s, 2s, 4s delays)
- Comprehensive error handling and user feedback

### Data Processing
- Automatic cleanup of empty data sections
- Proper ActionNetwork person object structure
- Source tracking via URL parameters (`?source=campaign`)
- Referrer data capture for analytics
- Custom field support for non-standard form fields

## Development Configuration

### Debug Mode
- Enable `WP_DEBUG` for comprehensive logging
- Use `cfan_dd()` for debug dumps (replaces generic `dd()`)
- Use `cfan_dump()` for error log output
- All logging prefixed with `[CF7-AN]` for easy identification

### Plugin Constants
- `CFAN_VERSION` - Plugin version
- `CFAN_PLUGIN_FILE` - Main plugin file path
- `CFAN_PLUGIN_URL` - Plugin URL for assets
- `CFAN_UPLOAD_DIR` - Upload directory for file handling
- `CFAN_TEXTDOMAIN` - Internationalization text domain

### Requirements
- WordPress 4.7+
- PHP 7.4+
- Contact Form 7 plugin (required dependency)
- ActionNetwork account with API access

## Testing Considerations

When modifying this plugin, test:
1. **Security**: Nonce verification and capability checks
2. **Field mapping**: Various CF7 field name patterns
3. **API integration**: Different ActionNetwork action types
4. **Error handling**: Network failures and invalid responses
5. **Admin UI**: Real-time validation and responsive design
6. **Logging**: WP_DEBUG output for debugging information

The plugin is production-ready and WordPress.org compliant with modern development practices, comprehensive security measures, and enhanced user experience.