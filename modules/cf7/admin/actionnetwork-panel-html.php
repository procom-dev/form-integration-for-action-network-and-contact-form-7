<?php

// If this file is called directly, call the cops.
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * $contactform is 'WPCF7_ContactForm' from 'CFAN_CF7_Module::html_template_panel_html'
 */

$activate = '0';
$hook_url = [];
$special_mail_tags = '';
$custom_headers = '';
$auto_detect_country = '1';
$source_name = 'contact-form-7';
$enable_autoresponse = '1';
$add_tags = [];
$remove_tags = [];

if ( is_a( $contactform, 'WPCF7_ContactForm' ) ) {
    $properties = $contactform->prop( CFAN_CF7_Module::METADATA );

    if ( isset( $properties['activate'] ) ) {
        $activate = $properties['activate'];
    }

    if ( isset( $properties['hook_url'] ) ) {
        $hook_url = (array) $properties['hook_url'];
    }

    if ( isset( $properties['send_mail'] ) ) {
        $send_mail = $properties['send_mail'];
    }

    if ( isset( $properties['special_mail_tags'] ) ) {
        $special_mail_tags = $properties['special_mail_tags'];
    }

    if ( isset( $properties['custom_headers'] ) ) {
        $custom_headers = $properties['custom_headers'];
    }

    if ( isset( $properties['auto_detect_country'] ) ) {
        $auto_detect_country = $properties['auto_detect_country'];
    }

    if ( isset( $properties['source_name'] ) ) {
        $source_name = $properties['source_name'];
    }

    if ( isset( $properties['enable_autoresponse'] ) ) {
        $enable_autoresponse = $properties['enable_autoresponse'];
    }

    if ( isset( $properties['add_tags'] ) && is_array( $properties['add_tags'] ) ) {
        $add_tags = $properties['add_tags'];
    }

    if ( isset( $properties['remove_tags'] ) && is_array( $properties['remove_tags'] ) ) {
        $remove_tags = $properties['remove_tags'];
    }
}

?>

<div class="cfan-panel">
    <?php wp_nonce_field( 'cfan_save_settings', 'cfan_nonce' ); ?>

    <h2>
        <?php esc_html_e( 'ActionNetwork Integration', 'contact-form-7-to-action-network-integration' ); ?>
    </h2>

    <div class="cfan-section">
        <h3><?php esc_html_e( 'Configuration', 'contact-form-7-to-action-network-integration' ); ?></h3>

        <div class="cfan-field-group">
            <label for="cfan-actionnetwork-hook-url">
                <?php esc_html_e( 'ActionNetwork API Endpoint URL', 'contact-form-7-to-action-network-integration' ); ?>
            </label>
            <input type="url" id="cfan-actionnetwork-hook-url" name="cfan-actionnetwork-hook-url" value="<?php echo esc_attr( implode( PHP_EOL, $hook_url ) ); ?>" placeholder="https://actionnetwork.org/api/v2/forms/your-form-id">
            <?php if ( $activate && empty( $hook_url ) ): ?>
                <div class="cfan-url-validation error">
                    <?php esc_html_e( 'You must enter an ActionNetwork API Endpoint URL', 'contact-form-7-to-action-network-integration' ); ?>
                </div>
            <?php endif; ?>
            <p class="description">
                <?php esc_html_e( 'Copy the API Endpoint URL from your ActionNetwork action page. You can find it in the right sidebar when editing your form, petition, or event.', 'contact-form-7-to-action-network-integration' ); ?>
            </p>
        </div>


        <div class="cfan-field-group">
            <label for="cfan-source-name">
                <?php esc_html_e( 'Source Name', 'contact-form-7-to-action-network-integration' ); ?>
            </label>
            <input type="text" id="cfan-source-name" name="cfan-source-name" value="<?php echo esc_attr( $source_name ); ?>" placeholder="contact-form-7">
            <p class="description">
                <?php esc_html_e( 'This will be sent as the source identifier to ActionNetwork. The website URL where the form is submitted will be sent as the website referrer.', 'contact-form-7-to-action-network-integration' ); ?>
            </p>
        </div>

        <div class="cfan-field-group">
            <div class="cfan-checkbox-field">
                <input type="checkbox" id="cfan-enable-autoresponse" name="cfan-enable-autoresponse" value="1" <?php checked( $enable_autoresponse, '1' ); ?>>
                <label for="cfan-enable-autoresponse">
                    <?php esc_html_e( 'Enable ActionNetwork autoresponse email', 'contact-form-7-to-action-network-integration' ); ?>
                </label>
            </div>
            <p class="description">
                <?php esc_html_e( 'When enabled, ActionNetwork will send its configured autoresponse email to the person who submitted the form.', 'contact-form-7-to-action-network-integration' ); ?>
            </p>
        </div>
    </div>

    <div class="cfan-section">
        <h3><?php esc_html_e( 'Field Mapping', 'contact-form-7-to-action-network-integration' ); ?></h3>
        
        <div class="cfan-field-group">
            <div class="cfan-checkbox-field">
                <input type="checkbox" id="cfan-auto-detect-country" name="cfan-auto-detect-country" value="1" <?php checked( $auto_detect_country, '1' ); ?>>
                <label for="cfan-auto-detect-country">
                    <?php esc_html_e( 'Auto-detect user country when not provided in form', 'contact-form-7-to-action-network-integration' ); ?>
                </label>
            </div>
            <p class="description">
                <?php esc_html_e( 'Automatically detects the user\'s country based on their IP address when no country field is filled in the form.', 'contact-form-7-to-action-network-integration' ); ?>
            </p>
        </div>
        
        <div class="cfan-core-fields">
            <h4><?php esc_html_e( 'Automatic field mappings:', 'contact-form-7-to-action-network-integration' ); ?></h4>
            <p><?php esc_html_e( 'The plugin automatically recognizes common form field names and maps them to ActionNetwork\'s person structure. If your CF7 form uses any of these field names, they will be mapped correctly:', 'contact-form-7-to-action-network-integration' ); ?></p>
            <ul>
                <li><strong>your-email, email, email-address</strong> → Email address</li>
                <li><strong>your-name, first-name, firstname, given-name, given_name</strong> → First name</li>
                <li><strong>your-last-name, last-name, lastname, family-name, family_name, surname</strong> → Last name</li>
                <li><strong>your-phone, phone, phone-number, telephone, mobile</strong> → Phone number</li>
                <li><strong>your-address, address, street-address, street_address, address1</strong> → Street address</li>
                <li><strong>your-city, city, locality, town</strong> → City</li>
                <li><strong>your-state, state, province, region</strong> → State/Region</li>
                <li><strong>your-zip, zip, zipcode, zip-code, zip_code, postal-code, postal_code, postcode</strong> → Postal code</li>
                <li><strong>your-country, country</strong> → Country</li>
            </ul>
        </div>
        
        <div class="cfan-help-text" style="background: #fff3cd; border-left-color: #ffc107;">
            <h4 style="color: #856404;"><?php esc_html_e( 'Important: Custom Fields', 'contact-form-7-to-action-network-integration' ); ?></h4>
            <p style="color: #856404;"><?php esc_html_e( 'Any form fields that don\'t match the patterns above will be sent as custom fields to ActionNetwork. Make sure your ActionNetwork action is configured to accept custom fields if you use non-standard field names.', 'contact-form-7-to-action-network-integration' ); ?></p>
        </div>
    </div>

    <div class="cfan-section">
        <h3><?php esc_html_e( 'ActionNetwork Tags', 'contact-form-7-to-action-network-integration' ); ?></h3>
        
        <div class="cfan-field-group">
            <label for="cfan-add-tags">
                <?php esc_html_e( 'Add Tags', 'contact-form-7-to-action-network-integration' ); ?>
            </label>
            <textarea id="cfan-add-tags" name="cfan-add-tags" rows="3" placeholder="<?php esc_attr_e( 'volunteer, member, subscriber', 'contact-form-7-to-action-network-integration' ); ?>"><?php echo esc_textarea( implode( ', ', $add_tags ) ); ?></textarea>
            <p class="description">
                <?php esc_html_e( 'Tags to add to the person\'s record in ActionNetwork. Separate multiple tags with commas. These tags must already exist in your ActionNetwork account.', 'contact-form-7-to-action-network-integration' ); ?>
            </p>
        </div>

        <div class="cfan-field-group">
            <label for="cfan-remove-tags">
                <?php esc_html_e( 'Remove Tags', 'contact-form-7-to-action-network-integration' ); ?>
            </label>
            <textarea id="cfan-remove-tags" name="cfan-remove-tags" rows="3" placeholder="<?php esc_attr_e( 'inactive, unsubscribed', 'contact-form-7-to-action-network-integration' ); ?>"><?php echo esc_textarea( implode( ', ', $remove_tags ) ); ?></textarea>
            <p class="description">
                <?php esc_html_e( 'Tags to remove from the person\'s record in ActionNetwork. Separate multiple tags with commas. Tag addition runs before tag removal.', 'contact-form-7-to-action-network-integration' ); ?>
            </p>
        </div>
    </div>


</div>