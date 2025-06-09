<?php
/**
 * CFAN_CF7_Module
 *
 * @package         CF7_ActionNetwork_Integration
 * @subpackage      CFAN_CF7_Module
 * @since           1.0.0
 *
 */

// If this file is called directly, call the cops.
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

if ( ! class_exists( 'CFAN_CF7_Module' ) ) {
    class CFAN_CF7_Module {

        /**
         * The Core object
         *
         * @since    1.0.0
         * @var      CFAN_Core    $core   The core class
         */
        private $core;

        /**
         * The Module Indentify
         *
         * @since    1.0.0
         */
        const MODULE_SLUG = 'cf7';

        /**
         * Metadata identifier
         *
         * @since    1.0.0
         */
        const METADATA = 'cfan_actionnetwork';

        /**
         * Define the core functionalities into plugin.
         *
         * @since    1.0.0
         * @param    CFAN_Core      $core   The Core object
         */
        public function __construct( CFAN_Core $core ) {
            $this->core = $core;
        }

        /**
         * Register all the hooks for this module
         *
         * @since    1.0.0
         * @access   private
         */
        private function define_hooks() {
            $this->core->add_filter( 'wpcf7_editor_panels', [ $this, 'wpcf7_editor_panels' ] );
            $this->core->add_action( 'wpcf7_save_contact_form', [ $this, 'wpcf7_save_contact_form' ] );
            $this->core->add_filter( 'wpcf7_skip_mail', [ $this, 'wpcf7_skip_mail' ], 10, 2 );
            $this->core->add_action( 'wpcf7_mail_sent', [ $this, 'wpcf7_mail_sent' ], 10, 1 );

            $this->core->add_filter( 'wpcf7_contact_form_properties', array( $this, 'wpcf7_contact_form_properties' ), 10, 2 );
            $this->core->add_filter( 'wpcf7_pre_construct_contact_form_properties', array( $this, 'wpcf7_contact_form_properties' ), 10, 2 );

            // Admin Hooks
            $this->core->add_action( 'admin_notices', [ $this, 'check_cf7_plugin' ] );
            $this->core->add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );
        }

        /**
         * Check Contact Form 7 Plugin is active
         * It's a dependency in this version
         *
         * @since    1.0.0
         * @access   private
         */
        public function check_cf7_plugin() {
            if ( ! current_user_can( 'activate_plugins' ) ) {
                return;
            }

            if ( is_plugin_active( 'contact-form-7/wp-contact-form-7.php' ) ) {
                return;
            }

            echo '<div class="notice notice-error is-dismissible">';
            // translators: %1$s and %2$s are link tags, %3$s and %4$s are strong tags
            echo '<p>' . sprintf( esc_html__( 'You need to install/activate %1$s Contact Form 7%2$s plugin to use %3$s CF7 to ActionNetwork %4$s', 'action-network-integration-for-contact-form-7' ), '<a href="http://contactform7.com/" target="_blank">', '</a>', '<strong>', '</strong>' );

            $screen = get_current_screen();
            if ( $screen->id == 'plugins' ) {
                echo '.</p></div>';
                return;
            }

            if ( file_exists( WP_PLUGIN_DIR . '/contact-form-7/wp-contact-form-7.php' ) ) {
                $url = 'plugins.php';
            } else {
                $url = 'plugin-install.php?tab=search&s=Contact+form+7';
            }

            echo '. <a href="' . esc_url( admin_url( $url ) ) . '">' . esc_html__( 'Do it now?', 'action-network-integration-for-contact-form-7' ) . '</a></p>';
            echo '</div>';
        }

        /**
         * Filter the 'wpcf7_editor_panels' to add necessary tabs
         *
         * @since    1.0.0
         * @param    array              $panels     Panels in CF7 Administration
         */
        public function wpcf7_editor_panels( $panels ) {
            $panels['actionnetwork-panel'] = array(
                'title'     => __( 'ActionNetwork', 'action-network-integration-for-contact-form-7' ),
                'callback'  => [ $this, 'actionnetwork_panel_html' ],
            );

            return $panels;
        }

        /**
         * Add actionnetwork panel HTML
         *
         * @since    1.0.0
         * @param    WPCF7_ContactForm  $contactform    Current ContactForm Obj
         */
        public function actionnetwork_panel_html( WPCF7_ContactForm $contactform ) {
            require plugin_dir_path( __FILE__ ) . 'admin/actionnetwork-panel-html.php';
        }

        /**
         * Action 'wpcf7_save_contact_form' to save properties do Contact Form Post
         *
         * @since    1.0.0
         * @param    WPCF7_ContactForm  $contactform    Current ContactForm Obj
         */
        public function wpcf7_save_contact_form( $contact_form ) {
            // Verify nonce
            if ( ! isset( $_POST['cfan_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['cfan_nonce'] ) ), 'cfan_save_settings' ) ) {
                return;
            }

            // Check permissions
            if ( ! current_user_can( 'wpcf7_edit_contact_form' ) ) {
                return;
            }

            $new_properties = [];


            if ( isset( $_POST['cfan-actionnetwork-hook-url'] ) ) {
                $hook_urls = array_filter( array_map( function( $hook_url ) {
                    $hook_url = sanitize_url( trim( $hook_url ) );
                    $placeholders = self::get_hook_url_placeholders( $hook_url );

                    foreach ( $placeholders as $key => $placeholder ) {
                        $hook_url = str_replace( $placeholder, '_____' . sanitize_key( $key ) . '_____', $hook_url );
                    }

                    $hook_url = esc_url_raw( $hook_url );

                    foreach ( $placeholders as $key => $placeholder ) {
                        $hook_url = str_replace( '_____' . sanitize_key( $key ) . '_____', $placeholder, $hook_url );
                    }

                    return $hook_url;
                }, explode( PHP_EOL, sanitize_textarea_field( wp_unslash( $_POST['cfan-actionnetwork-hook-url'] ) ) ) ) );
                $new_properties[ 'hook_url' ] = $hook_urls;
            }


            if ( isset( $_POST['cfan-special-mail-tags'] ) ) {
                $new_properties[ 'special_mail_tags' ] = sanitize_textarea_field( wp_unslash( $_POST['cfan-special-mail-tags'] ) );
            }

            if ( isset( $_POST['cfan-custom-headers'] ) ) {
                $new_properties[ 'custom_headers' ] = sanitize_textarea_field( wp_unslash( $_POST['cfan-custom-headers'] ) );
            }

            if ( isset( $_POST['cfan-auto-detect-country'] ) && sanitize_text_field( wp_unslash( $_POST['cfan-auto-detect-country'] ) ) === '1' ) {
                $new_properties[ 'auto_detect_country' ] = '1';
            } else {
                $new_properties[ 'auto_detect_country' ] = '0';
            }

            if ( isset( $_POST['cfan-source-name'] ) ) {
                $source_name = sanitize_text_field( wp_unslash( $_POST['cfan-source-name'] ) );
                $new_properties[ 'source_name' ] = ! empty( $source_name ) ? $source_name : 'contact-form-7';
            } else {
                $new_properties[ 'source_name' ] = 'contact-form-7';
            }

            if ( isset( $_POST['cfan-enable-autoresponse'] ) && sanitize_text_field( wp_unslash( $_POST['cfan-enable-autoresponse'] ) ) === '1' ) {
                $new_properties[ 'enable_autoresponse' ] = '1';
            } else {
                $new_properties[ 'enable_autoresponse' ] = '0';
            }

            // Handle add tags (comma-separated)
            $add_tags = [];
            if ( isset( $_POST['cfan-add-tags'] ) ) {
                $tags_string = sanitize_textarea_field( wp_unslash( $_POST['cfan-add-tags'] ) );
                if ( ! empty( $tags_string ) ) {
                    $tags_array = explode( ',', $tags_string );
                    foreach ( $tags_array as $tag ) {
                        $tag = trim( $tag );
                        if ( ! empty( $tag ) ) {
                            $add_tags[] = $tag;
                        }
                    }
                }
            }
            $new_properties[ 'add_tags' ] = $add_tags;

            // Handle remove tags (comma-separated)
            $remove_tags = [];
            if ( isset( $_POST['cfan-remove-tags'] ) ) {
                $tags_string = sanitize_textarea_field( wp_unslash( $_POST['cfan-remove-tags'] ) );
                if ( ! empty( $tags_string ) ) {
                    $tags_array = explode( ',', $tags_string );
                    foreach ( $tags_array as $tag ) {
                        $tag = trim( $tag );
                        if ( ! empty( $tag ) ) {
                            $remove_tags[] = $tag;
                        }
                    }
                }
            }
            $new_properties[ 'remove_tags' ] = $remove_tags;

            $properties = $contact_form->get_properties();
            $old_properties = $properties[ self::METADATA ];
            $properties[ self::METADATA ] = array_merge( $old_properties, $new_properties );
            $contact_form->set_properties( $properties );
        }

        /**
         * Filter the 'wpcf7_contact_form_properties' to add necessary properties
         *
         * @since    1.0.0
         * @param    array              $properties     ContactForm Obj Properties
         * @param    obj                $instance       ContactForm Obj Instance
         */
        public function wpcf7_contact_form_properties( $properties, $instance ) {
            if ( ! isset( $properties[ self::METADATA ] ) ) {
                $properties[ self::METADATA ] = array(
                    'hook_url'            => [],
                    'special_mail_tags'   => '',
                    'custom_headers'      => '',
                    'auto_detect_country' => '1',
                    'source_name'         => 'contact-form-7',
                    'enable_autoresponse' => '1',
                    'add_tags'            => [],
                    'remove_tags'         => [],
                );
            }

            return $properties;
        }

        /**
         * Filter the 'wpcf7_skip_mail' to skip if necessary
         *
         * @since    1.0.0
         * @param    bool               $skip_mail      true/false
         * @param    obj                $contact_form   ContactForm Obj
         */
        public function wpcf7_skip_mail( $skip_mail, $contact_form ) {
            // Always skip CF7 email when ActionNetwork integration has a URL configured
            if ( $this->can_submit_to_actionnetwork( $contact_form ) ) {
                return true;
            }

            return $skip_mail;
        }

        /**
         * Get reference of previous URL
         * 
         * @return string Referred URL or empty string
         */
        private function get_referrer_data() {
            return isset($_SERVER['HTTP_REFERER']) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) : '';
        }

        /**
         * Action 'wpcf7_mail_sent' to send data to ActionNetwork
         *
         * @since    1.0.0
         * @param    obj                $contact_form   ContactForm Obj
         */
        public function wpcf7_mail_sent( $contact_form ) {
            CFAN_Logger::info( '=== CF7 Form Submitted ===' );
            
            $properties = $contact_form->prop( self::METADATA );
            CFAN_Logger::info( 'Form properties: ' . wp_json_encode( $properties ) );

            if ( ! $this->can_submit_to_actionnetwork( $contact_form ) ) {
                CFAN_Logger::warning( 'Cannot submit to ActionNetwork - integration not properly configured' );
                return;
            }
            
            CFAN_Logger::info( 'ActionNetwork integration is active and properly configured' );

            $smt_data = $this->get_data_from_special_mail_tags( $contact_form );
            $cf_data = $this->get_data_from_contact_form( $contact_form );

            $data = array_merge( $smt_data, $cf_data );
            

            // Capture the referer, which could be the last visited URL
            $current_url = $this->get_referrer_data();
            // Clean the URL to remove parameters
            $clean_url = strtok($current_url, '?');

            // Get the configured source name or use URL parameter
            $properties = $contact_form->prop( self::METADATA );
            $default_source = ! empty( $properties['source_name'] ) ? $properties['source_name'] : 'contact-form-7';
            
            // Extract the "source" parameter using parse_url() and parse_str()
            $source = $default_source;
            $url_components = wp_parse_url($current_url);
            if (isset($url_components['query'])) {
                parse_str($url_components['query'], $params);
                $source = isset($params['source']) ? sanitize_text_field($params['source']) : $default_source;
            }

            $errors = [];

            foreach ( (array) $properties['hook_url'] as $hook_url ) {
                if ( empty( $hook_url ) ) {
                    continue;
                }

                // Try/Catch to support exception on request
                try {
                    $placeholders = CFAN_CF7_Module::get_hook_url_placeholders( $hook_url );
                    foreach ( $placeholders as $key => $placeholder ) {
                        $value = ( $data[ $key ] ?? '' );
                        if ( ! is_scalar( $value ) ) {
                            $value = implode( '|', $value );
                        }

                        /**
                         * Filter: cfan_hook_url_placeholder
                         *
                         * You can change the placeholder replacement in hook_url;
                         *
                         * @param $value        string      Urlencoded replace value.
                         * @param $placeholder  string      The placeholder to be replaced [$key].
                         * @param $key          string      The key of placeholder.
                         * @param $data         string      Data to be sent to actionnetwork.
                         *
                         * @since 3.0.0
                         */
                        $value =  apply_filters( 'cfan_hook_url_placeholder', urlencode( $value ), $placeholder, $key, $data );

                        $hook_url = str_replace( $placeholder, $value, $hook_url );
                    }

                    // Format data for ActionNetwork using improved structure
                    $data = $this->format_data_for_actionnetwork( $data, $source, $clean_url, $contact_form );

                    /**
                     * Action: cfan_trigger_actionnetwork
                     *
                     * You can add your own actions to process the hook.
                     * We send it using CFAN_ActionNetwork_Module::pull_the_trigger().
                     *
                     * @since  1.0.0
                     */
                    
                    do_action( 'cfan_trigger_actionnetwork', $data, $hook_url, $properties, $contact_form );
                } catch (Exception $exception) {
                    $errors[] = array(
                        'actionnetwork'   => $hook_url,
                        'exception' => $exception,
                    );

                    /**
                     * Filter: cfan_trigger_actionnetwork_error_message
                     *
                     * The 'cfan_trigger_actionnetwork_error_message' filter change the message in case of error.
                     * Default is CF7 error message, but you can access exception to create your own.
                     *
                     * You can ignore errors returning false:
                     * add_filter( 'cfan_trigger_actionnetwork_error_message', '__return_empty_string' );
                     *
                     * @since 1.4.0
                     */
                    $error_message =  apply_filters( 'cfan_trigger_actionnetwork_error_message', $contact_form->message( 'mail_sent_ng' ), $exception );

                    // If empty ignore
                    if ( empty( $error_message ) ) continue;

                    // Submission error
                    $submission = WPCF7_Submission::get_instance();
                    $submission->set_status( 'mail_failed' );
                    $submission->set_response( $error_message );
                    break;
                }
            }

            // If empty ignore
            if ( empty( $errors ) ) return;

            /**
             * Action: cfan_trigger_actionnetwork_errors
             *
             * If we have errors, we skiped them in 'cfan_trigger_actionnetwork_error_message' filter.
             * You can now submit your own error.
             *
             * @since  2.4.0
             */
            do_action( 'cfan_trigger_actionnetwork_errors', $errors, $contact_form );
        }

        /**
         * Retrieve a array with data from Contact Form data
         *
         * @since    1.0.0
         * @param    obj                $contact_form   ContactForm Obj
         */
        private function get_data_from_contact_form( $contact_form ) {
            $data = [];

            // Submission
            $submission = WPCF7_Submission::get_instance();
            $uploaded_files = ( ! empty( $submission ) ) ? $submission->uploaded_files() : [];

            // Upload Info
            $wp_upload_dir = wp_get_upload_dir();
            $upload_path = CFAN_UPLOAD_DIR . '/' . $contact_form->id() . '/' . uniqid();

            $upload_url = $wp_upload_dir['baseurl'] . '/' . $upload_path;
            $upload_dir = $wp_upload_dir['basedir'] . '/' . $upload_path;

            $tags = $contact_form->scan_form_tags();
            foreach ( $tags as $tag ) {
                if ( empty( $tag->name ) ) continue;

                // Get submitted data from CF7 submission object (preferred method)
                $raw_value = '';
                if ( ! empty( $submission ) ) {
                    $posted_data = $submission->get_posted_data();
                    $raw_value = isset( $posted_data[ $tag->name ] ) ? $posted_data[ $tag->name ] : '';
                }
                
                $value = ! empty( $raw_value ) ? wp_unslash( $raw_value ) : '';

                if ( is_array( $value ) ) {
                    foreach ( $value as $key => $v ) {
                        $value[ $key ] = sanitize_text_field( stripslashes( $v ) );
                    }
                }

                if ( is_string( $value ) ) {
                    $value = sanitize_text_field( stripslashes( $value ) );
                }

                // Files
                if ( $tag->basetype === 'file' && ! empty( $uploaded_files[ $tag->name ] ) ) {
                    $files = $uploaded_files[ $tag->name ];

                    $copied_files = [];
                    foreach ( (array) $files as $file ) {
                        wp_mkdir_p( $upload_dir );

                        $filename = wp_unique_filename( $upload_dir, $tag->name . '-' . basename( $file ) );

                        if ( ! copy( $file, $upload_dir . '/' . $filename ) ) {
                            $submission = WPCF7_Submission::get_instance();
                            $submission->set_status( 'mail_failed' );
                            $submission->set_response( $contact_form->message( 'upload_failed' ) );

                            continue;
                        }

                        $copied_files[] = $upload_url . '/' . $filename;
                    }

                    $value = $copied_files;

                    if (count($value) === 1) {
                        $value = $value[0];
                    }
                }

                // Support to Pipes
                $pipes = $tag->pipes;
                if ( WPCF7_USE_PIPE && $pipes instanceof WPCF7_Pipes && ! $pipes->zero() ) {
                    if ( is_array( $value) ) {
                        $new_value = [];

                        foreach ( $value as $v ) {
                            $new_value[] = $pipes->do_pipe( wp_unslash( $v ) );
                        }

                        $value = $new_value;
                    } else {
                        $value = $pipes->do_pipe( wp_unslash( $value ) );
                    }
                }

                // Support to Free Text on checkbox and radio
                if ( $tag->has_option( 'free_text' ) && in_array( $tag->basetype, [ 'checkbox', 'radio' ] ) ) {
                    $free_text_label = end( $tag->values );
                    $free_text_name  = $tag->name . '_free_text';
                    // phpcs:ignore WordPress.Security.NonceVerification.Missing -- CF7 handles nonce verification
                    $free_text_value = ( ! empty( $_POST[ $free_text_name ] ) ) ? sanitize_text_field( wp_unslash( $_POST[ $free_text_name ] ) ) : '';

                    if ( is_array( $value ) ) {
                        foreach ( $value as $key => $v ) {
                            if ( $v !== $free_text_label ) {
                                continue;
                            }

                            $value[ $key ] = stripslashes( $free_text_value );
                        }
                    }

                    if ( is_string( $value ) && $value === $free_text_label ) {
                        $value = stripslashes( $free_text_value );
                    }
                }

                // Support to "actionnetwork" option (rename field value)
                $key = $tag->name;
                $actionnetwork_key = $tag->get_option( 'actionnetwork' );

                if ( ! empty( $actionnetwork_key ) && ! empty( $actionnetwork_key[0] ) ) {
                    $key = $actionnetwork_key[0];
                }

                $data[ $key ] = $value;
            }

            /**
             * You can filter data retrieved from Contact Form tags with 'cfan_get_data_from_contact_form'
             *
             * @param $data             Array 'field => data'
             * @param $contact_form     ContactForm obj from 'wpcf7_mail_sent' action
             */
            return apply_filters( 'cfan_get_data_from_contact_form', $data, $contact_form );
        }

        /**
         * Retrieve a array with data from Special Mail Tags
         *
         * @link https://contactform7.com/special-mail-tags
         *
         * @since    1.3.0
         * @param    obj                $contact_form   ContactForm Obj
         */
        private function get_data_from_special_mail_tags( $contact_form ) {
            $tags = [];
            $data = [];

            $properties = $contact_form->prop( self::METADATA );
            if ( ! empty( $properties['special_mail_tags'] ) ) {
                $tags = self::get_special_mail_tags_from_string( $properties['special_mail_tags'] );
            }

            foreach ( $tags as $key => $tag ) {
                $mail_tag = new WPCF7_MailTag( sprintf( '[%s]', $tag ), $tag, '' );
                $value = '';

                // Support to "_raw_" values. @see WPCF7_MailTag::__construct()
                if ( $mail_tag->get_option( 'do_not_heat' ) ) {
                    $value = apply_filters( 'wpcf7_special_mail_tags', '', $mail_tag->tag_name(), false, $mail_tag );
                    // phpcs:ignore WordPress.Security.NonceVerification.Missing -- CF7 handles nonce verification
                    $value = isset( $_POST[ $mail_tag->field_name() ] ) ? sanitize_text_field( wp_unslash( $_POST[ $mail_tag->field_name() ] ) ) : '';
                }

                $value = apply_filters( 'wpcf7_special_mail_tags', $value, $mail_tag->tag_name(), false, $mail_tag );
                $data[ $key ] = $value;
            }

            /**
             * You can filter data retrieved from Special Mail Tags with 'cfan_get_data_from_special_mail_tags'
             *
             * @param $data             Array 'field => data'
             * @param $contact_form     ContactForm obj from 'wpcf7_mail_sent' action
             */
            return apply_filters( 'cfan_get_data_from_special_mail_tags', $data, $contact_form );
        }

        /**
         * Check we can submit a form to ActionNetwork
         *
         * @since    1.0.0
         * @param    obj                $contact_form   ContactForm Obj
         */
        private function can_submit_to_actionnetwork( $contact_form ) {
            $properties = $contact_form->prop( self::METADATA );

            // Simple check: if there's a hook URL, we can submit
            if ( empty( $properties ) || empty( $properties['hook_url'] ) ) {
                return false;
            }

            // Check if hook_url array has at least one non-empty URL
            $hook_urls = (array) $properties['hook_url'];
            foreach ( $hook_urls as $url ) {
                if ( ! empty( trim( $url ) ) ) {
                    return true;
                }
            }

            return false;
        }

        /**
         * Special Mail Tags from a configuration string
         *
         * @since    1.3.1
         * @param    string     $string
         * @return   array      $data       Array { key => tag }
         */
        public static function get_special_mail_tags_from_string( $string ) {
            $data = [];
            $tags = [];

            preg_match_all( '/\[[^\]]*]/', $string, $tags );
            $tags = ( ! empty( $tags[0] ) ) ? $tags[0] : $tags;

            foreach ( $tags as $tag_data ) {
                if ( ! is_string( $tag_data ) || empty( $tag_data ) ) continue;

                $tag_data = substr( $tag_data, 1, -1 );
                $tag_data = explode( ' ', $tag_data );

                if ( empty( $tag_data[0] ) ) continue;

                $tag = $tag_data[0];
                $key = ( ! empty( $tag_data[1] ) ) ? $tag_data[1] : $tag;

                if ( empty( $key ) ) continue;

                $data[ $key ] = $tag;
            }

            return $data;
        }

        /**
         * List placeholders from hook_url
         *
         * @since    3.0.0
         * @param    string     $hook_url
         * @return   array      $placeholders
         */
        public static function get_hook_url_placeholders( $hook_url ) {
            $placeholders = [];

            preg_match_all( '/\[{1}[^\[\]]+\]{1}/', $hook_url, $matches );

            foreach ( $matches[0] as $placeholder ) {
                $placeholder = substr( $placeholder, 1, -1 );
                $placeholders[ $placeholder ] = '[' . $placeholder . ']';
            }

            return $placeholders;
        }

        /**
         * Enqueue admin assets for CF7 edit pages
         *
         * @since    1.0.0
         */
        public function enqueue_admin_assets( $hook ) {
            // Only load on CF7 edit pages
            $screen = get_current_screen();
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin asset loading
            if ( ! $screen || $screen->id !== 'toplevel_page_wpcf7' || ! isset( $_GET['post'] ) ) {
                return;
            }

            $plugin_url = CFAN_PLUGIN_URL;

            // Enqueue CSS
            wp_enqueue_style(
                'cfan-admin-css',
                $plugin_url . '/modules/cf7/admin/assets/admin.css',
                [],
                CFAN_VERSION
            );

            // Enqueue JavaScript
            wp_enqueue_script(
                'cfan-admin-js',
                $plugin_url . '/modules/cf7/admin/assets/admin.js',
                [ 'jquery' ],
                CFAN_VERSION,
                true
            );

            // Localize script with translatable strings
            wp_localize_script( 'cfan-admin-js', 'cfan_admin', [
                'invalid_url_message' => __( 'Please enter a valid URL.', 'action-network-integration-for-contact-form-7' ),
                'not_actionnetwork_message' => __( 'This URL is not from ActionNetwork. Please use an ActionNetwork URL.', 'action-network-integration-for-contact-form-7' ),
                // translators: %s is the ActionNetwork action type (e.g., "form", "petition")
                'detected_type_message' => __( 'ActionNetwork %s detected. This looks correct!', 'action-network-integration-for-contact-form-7' ),
                'unknown_pattern_message' => __( 'This ActionNetwork URL pattern is not recognized, but it may still work.', 'action-network-integration-for-contact-form-7' ),
                'url_required_message' => __( 'Please enter an ActionNetwork URL to activate the integration.', 'action-network-integration-for-contact-form-7' )
            ]);
        }

        /**
         * Format data for ActionNetwork with improved structure
         *
         * @since    1.0.0
         * @param    array      $cf7_data     The CF7 form data
         * @param    string     $source       The source parameter
         * @param    string     $website      The referring website URL
         * @param    object     $contact_form The CF7 contact form object
         * @return   array                    Formatted data for ActionNetwork
         */
        private function format_data_for_actionnetwork( $cf7_data, $source, $website, $contact_form ) {

            // Core ActionNetwork fields mapping
            $core_fields = [
                'family_name' => '',
                'given_name' => '',
                'postal_code' => '',
                'address_lines' => [''],
                'locality' => '',
                'region' => '',
                'country' => '',
                'address' => '', // email address
                'status' => '',  // email status
                'number' => ''   // phone number
            ];

            // Common field name mappings
            $field_mapping = [
                // Email variations
                'email' => 'address',
                'your-email' => 'address',
                'email-address' => 'address',
                // First name variations
                'first-name' => 'given_name',
                'your-name' => 'given_name',
                'first_name' => 'given_name',
                'firstname' => 'given_name',
                'given-name' => 'given_name',
                'given_name' => 'given_name',
                // Last name variations
                'last-name' => 'family_name',
                'your-last-name' => 'family_name',
                'last_name' => 'family_name',
                'lastname' => 'family_name',
                'family-name' => 'family_name',
                'family_name' => 'family_name',
                'surname' => 'family_name',
                // Phone variations
                'phone' => 'number',
                'your-phone' => 'number',
                'phone-number' => 'number',
                'telephone' => 'number',
                'mobile' => 'number',
                // Address variations
                'address' => 'address_lines',
                'your-address' => 'address_lines',
                'street-address' => 'address_lines',
                'street_address' => 'address_lines',
                'address1' => 'address_lines',
                // City variations
                'city' => 'locality',
                'your-city' => 'locality',
                'locality' => 'locality',
                'town' => 'locality',
                // State/Region variations
                'state' => 'region',
                'your-state' => 'region',
                'province' => 'region',
                'region' => 'region',
                // Postal code variations
                'zip' => 'postal_code',
                'your-zip' => 'postal_code',
                'zipcode' => 'postal_code',
                'zip-code' => 'postal_code',
                'zip_code' => 'postal_code',
                'postal-code' => 'postal_code',
                'postal_code' => 'postal_code',
                'postcode' => 'postal_code',
                // Country variations
                'country' => 'country',
                'your-country' => 'country'
            ];

            // Get form properties for autoresponse and tags
            $properties = $contact_form->prop( self::METADATA );
            
            // Initialize person data structure
            $person_data = [
                'person' => [
                    'family_name' => '',
                    'given_name' => '',
                    'custom_fields' => [],
                    'postal_addresses' => [
                        [
                            'postal_code' => '',
                            'address_lines' => [],
                            'locality' => '',
                            'region' => '',
                            'country' => ''
                        ]
                    ],
                    'email_addresses' => [
                        [
                            'address' => '',
                            'status' => 'subscribed'
                        ]
                    ],
                    'phone_numbers' => []
                ],
                'triggers' => [
                    'autoresponse' => [
                        'enabled' => ! empty( $properties['enable_autoresponse'] ) && $properties['enable_autoresponse'] === '1'
                    ]
                ],
                'action_network:referrer_data' => [
                    'source' => $source,
                    'website' => $website
                ]
            ];

            // Add tags if configured
            if ( ! empty( $properties['add_tags'] ) && is_array( $properties['add_tags'] ) ) {
                $person_data['add_tags'] = array_filter( $properties['add_tags'] );
            }
            
            if ( ! empty( $properties['remove_tags'] ) && is_array( $properties['remove_tags'] ) ) {
                $person_data['remove_tags'] = array_filter( $properties['remove_tags'] );
            }

            // Process each field from CF7
            foreach ( $cf7_data as $key => $value ) {
                // Skip empty values
                if ( empty( $value ) && $value !== '0' ) {
                    continue;
                }

                // Map field name if we have a mapping
                $mapped_key = isset( $field_mapping[ $key ] ) ? $field_mapping[ $key ] : $key;

                // Handle core fields
                if ( array_key_exists( $mapped_key, $core_fields ) ) {
                    switch ( $mapped_key ) {
                        case 'address': // email address
                            $person_data['person']['email_addresses'][0]['address'] = sanitize_email( $value );
                            break;
                            
                        case 'status': // email status
                            $person_data['person']['email_addresses'][0]['status'] = sanitize_text_field( $value );
                            break;
                            
                        case 'number': // phone number
                            if ( ! empty( $value ) ) {
                                $person_data['person']['phone_numbers'][] = [
                                    'number' => sanitize_text_field( $value ),
                                    'status' => 'subscribed'
                                ];
                            }
                            break;
                            
                        case 'address_lines': // postal address
                            $address_lines = is_array( $value ) ? $value : [ $value ];
                            $person_data['person']['postal_addresses'][0]['address_lines'] = array_map( 'sanitize_text_field', $address_lines );
                            break;
                            
                        case 'postal_code':
                        case 'locality':
                        case 'region':
                        case 'country':
                            $person_data['person']['postal_addresses'][0][ $mapped_key ] = sanitize_text_field( $value );
                            break;
                            
                        case 'family_name':
                        case 'given_name':
                            $person_data['person'][ $mapped_key ] = sanitize_text_field( $value );
                            break;
                    }
                } else {
                    // Handle as custom field
                    $person_data['person']['custom_fields'][ $key ] = is_array( $value ) ? $value : sanitize_text_field( $value );
                }
            }

            // Clean up empty sections
            if ( empty( $person_data['person']['phone_numbers'] ) ) {
                unset( $person_data['person']['phone_numbers'] );
            }

            // Auto-detect country if not provided and feature is enabled
            if ( empty( $person_data['person']['postal_addresses'][0]['country'] ) ) {
                $detected_country = $this->detect_user_country_if_enabled( $cf7_data, $contact_form );
                if ( ! empty( $detected_country ) ) {
                    $person_data['person']['postal_addresses'][0]['country'] = $detected_country;
                }
            }

            // If no postal address data, remove the section
            $postal_address = $person_data['person']['postal_addresses'][0];
            if ( empty( $postal_address['postal_code'] ) && empty( $postal_address['address_lines'] ) && 
                 empty( $postal_address['locality'] ) && empty( $postal_address['region'] ) && 
                 empty( $postal_address['country'] ) ) {
                unset( $person_data['person']['postal_addresses'] );
            }

            // If no email address, remove the section
            if ( empty( $person_data['person']['email_addresses'][0]['address'] ) ) {
                unset( $person_data['person']['email_addresses'] );
            }

            /**
             * Filter: cfan_detected_country
             *
             * Allow filtering of the detected country before adding to data
             *
             * @param string|null $detected_country The detected country code
             * @param array       $cf7_data         The original CF7 form data
             * @param string      $user_ip          The user's IP address
             *
             * @since 1.0.0
             */
            if ( ! empty( $person_data['person']['postal_addresses'][0]['country'] ) ) {
                $country = $person_data['person']['postal_addresses'][0]['country'];
                $user_ip = $this->get_user_ip();
                $filtered_country = apply_filters( 'cfan_detected_country', $country, $cf7_data, $user_ip );
                $person_data['person']['postal_addresses'][0]['country'] = $filtered_country;
            }

            /**
             * Filter: cfan_formatted_data
             *
             * Allow filtering of the formatted ActionNetwork data before sending
             *
             * @param array $person_data The formatted person data
             * @param array $cf7_data    The original CF7 form data
             * @param string $source     The source parameter
             * @param string $website    The referring website
             *
             * @since 1.0.0
             */
            $person_data = apply_filters( 'cfan_formatted_data', $person_data, $cf7_data, $source, $website );

            return $person_data;
        }

        /**
         * Detect user's country if the feature is enabled
         *
         * @since    1.0.0
         * @param    array      $cf7_data     Original CF7 form data
         * @param    object     $contact_form The CF7 contact form object
         * @return   string|null              Country code or null if detection fails
         */
        private function detect_user_country_if_enabled( $cf7_data, $contact_form ) {
            // Check if auto-detection is enabled for this form
            $properties = $contact_form->prop( self::METADATA );
            
            if ( empty( $properties['auto_detect_country'] ) || $properties['auto_detect_country'] !== '1' ) {
                return null;
            }

            /**
             * Filter: cfan_enable_country_detection
             *
             * Allow developers to programmatically enable/disable country detection
             *
             * @param bool  $enabled   Whether country detection is enabled
             * @param array $cf7_data  The CF7 form data
             *
             * @since 1.0.0
             */
            $detection_enabled = apply_filters( 'cfan_enable_country_detection', true, $cf7_data );
            
            if ( ! $detection_enabled ) {
                return null;
            }

            return $this->detect_user_country();
        }

        /**
         * Detect user's country based on IP address
         *
         * @since    1.0.0
         * @return   string|null    Country code or null if detection fails
         */
        private function detect_user_country() {
            // Get user's IP address
            $user_ip = $this->get_user_ip();
            
            if ( empty( $user_ip ) || $user_ip === '127.0.0.1' || $user_ip === '::1' ) {
                return null;
            }

            // Try multiple methods for country detection
            $country = null;

            // Method 1: Use WordPress geoIP if available
            if ( function_exists( 'geoip_country_code_by_name' ) ) {
                $country = geoip_country_code_by_name( $user_ip );
                if ( ! empty( $country ) ) {
                    return strtoupper( $country );
                }
            }

            // Method 2: Use free ipapi.co service (no API key required)
            $country = $this->detect_country_via_ipapi( $user_ip );
            if ( ! empty( $country ) ) {
                return $country;
            }

            // Method 3: Use CloudFlare country header if available
            if ( isset( $_SERVER['HTTP_CF_IPCOUNTRY'] ) ) {
                $cf_country = sanitize_text_field( wp_unslash( $_SERVER['HTTP_CF_IPCOUNTRY'] ) );
                if ( $cf_country !== 'XX' && strlen( $cf_country ) === 2 ) {
                    return strtoupper( $cf_country );
                }
            }

            // Method 4: Use browser Accept-Language as fallback
            $country = $this->detect_country_from_language();
            if ( ! empty( $country ) ) {
                return $country;
            }

            return null;
        }

        /**
         * Get user's real IP address
         *
         * @since    1.0.0
         * @return   string|null    IP address or null
         */
        private function get_user_ip() {
            // Check for various headers that might contain the real IP
            $ip_headers = [
                'HTTP_CF_CONNECTING_IP',     // CloudFlare
                'HTTP_X_FORWARDED_FOR',      // Load balancer/proxy
                'HTTP_X_FORWARDED',          // Proxy
                'HTTP_X_CLUSTER_CLIENT_IP',  // Cluster
                'HTTP_FORWARDED_FOR',        // Proxy
                'HTTP_FORWARDED',            // Proxy
                'HTTP_CLIENT_IP',            // Proxy
                'REMOTE_ADDR'                // Standard
            ];

            foreach ( $ip_headers as $header ) {
                if ( ! empty( $_SERVER[ $header ] ) ) {
                    $ip = sanitize_text_field( wp_unslash( $_SERVER[ $header ] ) );
                    
                    // Handle comma-separated IPs (X-Forwarded-For can have multiple IPs)
                    if ( strpos( $ip, ',' ) !== false ) {
                        $ip = trim( explode( ',', $ip )[0] );
                    }
                    
                    // Validate IP
                    if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) ) {
                        return $ip;
                    }
                }
            }

            return null;
        }

        /**
         * Detect country using ipapi.co service
         *
         * @since    1.0.0
         * @param    string    $ip    IP address
         * @return   string|null     Country code or null
         */
        private function detect_country_via_ipapi( $ip ) {
            $api_url = "https://ipapi.co/{$ip}/country/";
            
            $response = wp_remote_get( $api_url, [
                'timeout' => 5,
                'user-agent' => 'CF7-ActionNetwork-Integration/' . CFAN_VERSION
            ]);

            if ( is_wp_error( $response ) ) {
                return null;
            }

            $country_code = trim( wp_remote_retrieve_body( $response ) );
            
            if ( strlen( $country_code ) === 2 && ctype_alpha( $country_code ) ) {
                return strtoupper( $country_code );
            }

            return null;
        }

        /**
         * Detect country from browser language as fallback
         *
         * @since    1.0.0
         * @return   string|null    Country code or null
         */
        private function detect_country_from_language() {
            if ( empty( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) ) {
                return null;
            }

            $accept_language = sanitize_text_field( wp_unslash( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) );
            
            // Parse Accept-Language header (e.g., "en-US,en;q=0.9,es;q=0.8")
            if ( preg_match( '/([a-z]{2})-([A-Z]{2})/', $accept_language, $matches ) ) {
                $country_code = $matches[2];
                return $country_code;
            }

            return null;
        }

        /**
         * Run the module.
         *
         * @since    1.0.0
         */
        public function run() {
            $this->define_hooks();
        }
    }
}
