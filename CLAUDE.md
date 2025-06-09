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
cf7-actionnetwork-integration/
├── cf7-to-actionnetwork.php (main plugin file - CFAN_Core orchestrator)
├── includes/
│   ├── class-cfan-logger.php (centralized logging system)
│   └── functions-debug.php (cfan_dd/cfan_dump debug helpers)
├── modules/
│   ├── cf7/
│   │   ├── class-module-cf7.php (CF7 hooks and data processing)
│   │   └── admin/ (admin UI and validation assets)
│   └── actionnetwork/
│       └── class-module-actionnetwork.php (API client with retry logic)
├── assets/ (WordPress.org plugin assets - banners, screenshots)
└── languages/ (internationalization support)
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

## Development Workflow

### Setup and Testing
This is a WordPress plugin with no build process - direct PHP development:
- **Local Testing**: Install in WordPress development environment (`/wp-content/plugins/`)
- **Dependencies**: Requires Contact Form 7 plugin to be active
- **Debug Mode**: Enable `WP_DEBUG` in `wp-config.php` for comprehensive logging
- **Error Logs**: Check WordPress error logs or use `cfan_dump()` for debugging

### Development Commands
No build tools are used - this is native WordPress PHP development:
- **Code Validation**: Use WordPress coding standards (WPCS) if available
- **Testing**: Manual testing with Contact Form 7 forms and ActionNetwork API
- **Linting**: Standard PHP linting (`php -l filename.php`)

### Debug Functions (WP_DEBUG only)
- Use `cfan_dd()` for debug dumps (replaces generic `dd()`)
- Use `cfan_dump()` for error log output  
- All logging prefixed with `[CF7-AN]` for easy identification
- Debug functions only available when `WP_DEBUG` is enabled

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

## Critical Development Notes

### Plugin Initialization Flow
1. `CFAN_Core` class auto-instantiates on plugin load
2. Modules are dynamically loaded from `/modules/` directory  
3. Each module registers its own hooks via `CFAN_Core::add_action()`/`add_filter()`
4. CF7 module only activates when Contact Form 7 is detected

### Key Integration Points
- **Data capture**: `wpcf7_mail_sent` hook in CF7 module
- **Field mapping**: `format_data_for_actionnetwork()` method transforms CF7 data
- **API transmission**: ActionNetwork module handles HTTP requests with retry logic
- **Admin interface**: Embedded in CF7's form edit screen as custom tab

### Testing Approach
Manual testing required - no automated tests:
1. **Form submission flow**: Create CF7 form → Configure ActionNetwork URL → Test submission
2. **Field mapping validation**: Test various field name patterns against mapping logic
3. **API integration**: Verify different ActionNetwork action types (forms, petitions, events)
4. **Error scenarios**: Test network failures, invalid URLs, malformed responses
5. **Security verification**: Test nonce validation and capability checks in admin

### WordPress.org Compliance Notes
- All functions/classes use `cfan_`/`CFAN_` prefixing
- Comprehensive input sanitization and output escaping
- Nonce verification on all admin forms
- Text domain consistency throughout
- No external dependencies or build processes required