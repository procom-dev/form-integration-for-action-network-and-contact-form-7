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
$auto_field_mapping = '1';
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

    $auto_field_mapping = CFAN_CF7_Module::is_auto_field_mapping_enabled( $properties ) ? '1' : '0';

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

// Group the recognized CF7 field names by ActionNetwork field, for the tables.
$mapping_targets = CFAN_CF7_Module::get_field_mapping_targets();
$auto_names = array_fill_keys( array_keys( $mapping_targets ), [] );
foreach ( CFAN_CF7_Module::get_auto_field_mapping() as $cf7_name => $target ) {
    $auto_names[ $target ][] = $cf7_name;
}
$exact_names = array_flip( CFAN_CF7_Module::get_exact_field_mapping() );

?>

<div class="cfan-panel">
    <?php wp_nonce_field( 'cfan_save_settings', 'cfan_nonce' ); ?>

    <h2>
        <?php esc_html_e( 'ActionNetwork Integration', 'form-integration-for-action-network-and-contact-form-7' ); ?>
    </h2>

    <div class="cfan-section">
        <h3><?php esc_html_e( 'Configuration', 'form-integration-for-action-network-and-contact-form-7' ); ?></h3>

        <div class="cfan-field-group">
            <label for="cfan-actionnetwork-hook-url">
                <?php esc_html_e( 'ActionNetwork API Endpoint URL', 'form-integration-for-action-network-and-contact-form-7' ); ?>
            </label>
            <input type="url" id="cfan-actionnetwork-hook-url" name="cfan-actionnetwork-hook-url" value="<?php echo esc_attr( implode( PHP_EOL, $hook_url ) ); ?>" placeholder="https://actionnetwork.org/api/v2/forms/your-form-id">
            <?php if ( $activate && empty( $hook_url ) ): ?>
                <div class="cfan-url-validation error">
                    <?php esc_html_e( 'You must enter an ActionNetwork API Endpoint URL', 'form-integration-for-action-network-and-contact-form-7' ); ?>
                </div>
            <?php endif; ?>
            <p class="description">
                <?php esc_html_e( 'Copy the API Endpoint URL from your ActionNetwork action page. You can find it in the right sidebar when editing your form, petition, or event.', 'form-integration-for-action-network-and-contact-form-7' ); ?>
            </p>
        </div>


        <div class="cfan-field-group">
            <label for="cfan-source-name">
                <?php esc_html_e( 'Source Name', 'form-integration-for-action-network-and-contact-form-7' ); ?>
            </label>
            <input type="text" id="cfan-source-name" name="cfan-source-name" value="<?php echo esc_attr( $source_name ); ?>" placeholder="contact-form-7">
            <p class="description">
                <?php esc_html_e( 'This will be sent as the source identifier to ActionNetwork. The website URL where the form is submitted will be sent as the website referrer.', 'form-integration-for-action-network-and-contact-form-7' ); ?>
            </p>
        </div>

        <div class="cfan-field-group">
            <div class="cfan-checkbox-field">
                <input type="checkbox" id="cfan-enable-autoresponse" name="cfan-enable-autoresponse" value="1" <?php checked( $enable_autoresponse, '1' ); ?>>
                <label for="cfan-enable-autoresponse">
                    <?php esc_html_e( 'Enable ActionNetwork autoresponse email', 'form-integration-for-action-network-and-contact-form-7' ); ?>
                </label>
            </div>
            <p class="description">
                <?php esc_html_e( 'When enabled, ActionNetwork will send its configured autoresponse email to the person who submitted the form.', 'form-integration-for-action-network-and-contact-form-7' ); ?>
            </p>
        </div>
    </div>

    <div class="cfan-section">
        <h3><?php esc_html_e( 'Field Mapping', 'form-integration-for-action-network-and-contact-form-7' ); ?></h3>
        <p class="cfan-section-intro">
            <?php esc_html_e( 'How your Contact Form 7 fields become fields on the person\'s record in ActionNetwork.', 'form-integration-for-action-network-and-contact-form-7' ); ?>
        </p>

        <div class="cfan-field-group">
            <div class="cfan-checkbox-field">
                <input type="checkbox" id="cfan-auto-field-mapping" name="cfan-auto-field-mapping" value="1" aria-describedby="cfan-auto-field-mapping-desc" <?php checked( $auto_field_mapping, '1' ); ?>>
                <label for="cfan-auto-field-mapping">
                    <?php esc_html_e( 'Automatically recognize common field names', 'form-integration-for-action-network-and-contact-form-7' ); ?>
                    <span class="cfan-badge"><?php esc_html_e( 'Recommended', 'form-integration-for-action-network-and-contact-form-7' ); ?></span>
                </label>
            </div>
            <p class="description" id="cfan-auto-field-mapping-desc">
                <?php esc_html_e( 'Matches the usual CF7 names, such as your-email or your-name, so most forms work without changes. Turn this off to match only the exact ActionNetwork names.', 'form-integration-for-action-network-and-contact-form-7' ); ?>
            </p>
        </div>

        <table class="cfan-mapping-table" data-cfan-mapping="auto" <?php echo $auto_field_mapping === '1' ? '' : 'hidden'; ?>>
            <caption class="screen-reader-text"><?php esc_html_e( 'Recognized field names', 'form-integration-for-action-network-and-contact-form-7' ); ?></caption>
            <thead>
                <tr>
                    <th scope="col"><?php esc_html_e( 'ActionNetwork field', 'form-integration-for-action-network-and-contact-form-7' ); ?></th>
                    <th scope="col"><?php esc_html_e( 'Recognized CF7 field names', 'form-integration-for-action-network-and-contact-form-7' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $mapping_targets as $target => $label ) : ?>
                    <tr>
                        <th scope="row"><?php echo esc_html( $label ); ?></th>
                        <td>
                            <?php foreach ( $auto_names[ $target ] as $cf7_name ) : ?>
                                <code><?php echo esc_html( $cf7_name ); ?></code>
                            <?php endforeach; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <table class="cfan-mapping-table" data-cfan-mapping="exact" <?php echo $auto_field_mapping === '1' ? 'hidden' : ''; ?>>
            <caption class="screen-reader-text"><?php esc_html_e( 'Exact field names', 'form-integration-for-action-network-and-contact-form-7' ); ?></caption>
            <thead>
                <tr>
                    <th scope="col"><?php esc_html_e( 'ActionNetwork field', 'form-integration-for-action-network-and-contact-form-7' ); ?></th>
                    <th scope="col"><?php esc_html_e( 'Name your CF7 field', 'form-integration-for-action-network-and-contact-form-7' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $mapping_targets as $target => $label ) : ?>
                    <?php if ( ! isset( $exact_names[ $target ] ) ) { continue; } ?>
                    <tr>
                        <th scope="row"><?php echo esc_html( $label ); ?></th>
                        <td><code><?php echo esc_html( $exact_names[ $target ] ); ?></code></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="cfan-callout">
            <p>
                <strong><?php esc_html_e( 'Any other field is sent as a custom field', 'form-integration-for-action-network-and-contact-form-7' ); ?></strong>
                <?php esc_html_e( 'under its CF7 field name.', 'form-integration-for-action-network-and-contact-form-7' ); ?>
            </p>
            <p>
                <?php esc_html_e( 'To keep your own field name but send it to a specific ActionNetwork field, add an actionnetwork option to the form tag:', 'form-integration-for-action-network-and-contact-form-7' ); ?>
            </p>
            <p><code>[text your-nickname actionnetwork:given_name]</code></p>
        </div>

        <div class="cfan-subgroup">
            <h4><?php esc_html_e( 'Country', 'form-integration-for-action-network-and-contact-form-7' ); ?></h4>
            <div class="cfan-field-group">
                <div class="cfan-checkbox-field">
                    <input type="checkbox" id="cfan-auto-detect-country" name="cfan-auto-detect-country" value="1" aria-describedby="cfan-auto-detect-country-desc" <?php checked( $auto_detect_country, '1' ); ?>>
                    <label for="cfan-auto-detect-country">
                        <?php esc_html_e( 'Detect the visitor\'s country when the form has none', 'form-integration-for-action-network-and-contact-form-7' ); ?>
                    </label>
                </div>
                <p class="description" id="cfan-auto-detect-country-desc">
                    <?php esc_html_e( 'Only used when no country field is filled in. Tries your server\'s GeoIP data, then the ipapi.co lookup service (which receives the visitor\'s IP address), then Cloudflare\'s country header, then the browser language.', 'form-integration-for-action-network-and-contact-form-7' ); ?>
                </p>
            </div>
        </div>
    </div>

    <div class="cfan-section">
        <h3><?php esc_html_e( 'ActionNetwork Tags', 'form-integration-for-action-network-and-contact-form-7' ); ?></h3>
        
        <div class="cfan-field-group">
            <label for="cfan-add-tags">
                <?php esc_html_e( 'Add Tags', 'form-integration-for-action-network-and-contact-form-7' ); ?>
            </label>
            <textarea id="cfan-add-tags" name="cfan-add-tags" rows="3" placeholder="<?php esc_attr_e( 'volunteer, member, subscriber', 'form-integration-for-action-network-and-contact-form-7' ); ?>"><?php echo esc_textarea( implode( ', ', $add_tags ) ); ?></textarea>
            <p class="description">
                <?php esc_html_e( 'Tags to add to the person\'s record in ActionNetwork. Separate multiple tags with commas. These tags must already exist in your ActionNetwork account.', 'form-integration-for-action-network-and-contact-form-7' ); ?>
            </p>
        </div>

        <div class="cfan-field-group">
            <label for="cfan-remove-tags">
                <?php esc_html_e( 'Remove Tags', 'form-integration-for-action-network-and-contact-form-7' ); ?>
            </label>
            <textarea id="cfan-remove-tags" name="cfan-remove-tags" rows="3" placeholder="<?php esc_attr_e( 'inactive, unsubscribed', 'form-integration-for-action-network-and-contact-form-7' ); ?>"><?php echo esc_textarea( implode( ', ', $remove_tags ) ); ?></textarea>
            <p class="description">
                <?php esc_html_e( 'Tags to remove from the person\'s record in ActionNetwork. Separate multiple tags with commas. Tag addition runs before tag removal.', 'form-integration-for-action-network-and-contact-form-7' ); ?>
            </p>
        </div>
    </div>


</div>