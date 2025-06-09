<?php
/**
 * CFAN_ActionNetwork_Module
 *
 * @package         CF7_ActionNetwork_Integration
 * @subpackage      CFAN_ActionNetwork_Module
 * @since           1.0.0
 *
 */

// If this file is called directly, call the cops.
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

if ( ! class_exists( 'CFAN_ActionNetwork_Module' ) ) {
    class CFAN_ActionNetwork_Module {

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
        const MODULE_SLUG = 'actionnetwork';

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
            $this->core->add_action( 'cfan_trigger_actionnetwork', array( $this, 'pull_the_trigger' ), 10, 5 );
        }

        /**
         * Send data to ActionNetwork
         *
         * @since    1.0.0
         * @access   private
         */
        public function pull_the_trigger( array $data, $hook_url, $properties, $contact_form ) {
            /**
             * Filter: cfan_ignore_default_actionnetwork
             *
             * The 'cfan_ignore_default_actionnetwork' filter can be used to ignore
             * core request, if you want to trigger your own request.
             *
             * add_filter( 'cfan_ignore_default_actionnetwork', '__return_true' );
             *
             * @since    2.3.0
             */


            // Validate ActionNetwork URL
            if ( ! $this->validate_actionnetwork_url( $hook_url ) ) {
                CFAN_Logger::error( "Invalid ActionNetwork URL: {$hook_url}" );
                throw new Exception( esc_html__( 'Invalid ActionNetwork URL provided', 'action-network-integration-for-contact-form-7' ) );
            }

            // Before modifying hook url logic
            $original_url = $hook_url;
            if (stripos($hook_url, "actionnetwork.org/api/v2/events/") !== false) {
                $hook_url = rtrim($hook_url, '/') . '/attendances';
            } elseif (stripos($hook_url, "actionnetwork.org/api/v2/fundraising_pages/") !== false) {
                $hook_url = rtrim($hook_url, '/') . '/donations';
            } elseif (stripos($hook_url, "actionnetwork.org/api/v2/advocacy_campaigns/") !== false) {
                $hook_url = rtrim($hook_url, '/') . '/outreaches';
            } elseif (stripos($hook_url, "actionnetwork.org/api/v2/petitions/") !== false) {
                $hook_url = rtrim($hook_url, '/') . '/signatures';
            } elseif (stripos($hook_url, "actionnetwork.org/api/v2/forms/") !== false) {
                $hook_url = rtrim($hook_url, '/') . '/submissions';
            }


            if ( apply_filters( 'cfan_ignore_default_actionnetwork', false ) ) {
                return;
            }

            $args = array(
                'method'    => 'POST',
                'body'      => wp_json_encode( $data ),
                'headers'   => $this->create_headers( $properties['custom_headers'] ?? '' ),
                'timeout'   => 30,
            );

            /**
             * Filter: cfan_hook_url
             *
             * The 'cfan_hook_url' filter actionnetwork URL so developers can use form
             * data or other information to change actionnetwork URL.
             *
             * @since    2.1.4
             */
            $hook_url = apply_filters( 'cfan_hook_url', $hook_url, $data );

            /**
             * Filter: cfan_post_request_args
             *
             * The 'cfan_post_request_args' filter POST args so developers
             * can modify the request args if any service demands a particular header or body.
             *
             * @since    1.1.0
             */
            $args = apply_filters( 'cfan_post_request_args', $args, $properties, $contact_form );
            $result = $this->send_to_actionnetwork_with_retry( $hook_url, $args, 3 );

            CFAN_Logger::log_api_request( $hook_url, $data, $result );

            /**
             * Action: cfan_post_request_result
             *
             * You can perform a action with the result of the request.
             * By default we do nothing but you can throw a Exception in actionnetwork errors.
             *
             * @since    1.4.0
             */
            do_action( 'cfan_post_request_result', $result, $hook_url );
        }

        /**
         * Run the module.
         *
         * @since    1.0.0
         */
        public function run() {
            $this->define_hooks();
        }

        /**
         * Get headers to request.
         *
         * @since    2.3.0
         */
        public function create_headers( $custom ) {
            $headers = array( 'Content-Type'  => 'application/json' );
            $blog_charset = get_option( 'blog_charset' );
            if ( ! empty( $blog_charset ) ) {
                $headers['Content-Type'] .= '; charset=' . get_option( 'blog_charset' );
            }

            if ( ! empty( $custom ) ) {
                $custom_lines = explode( "\n", $custom );
                foreach ( $custom_lines as $header_line ) {
                    $header_parts = explode( ':', $header_line, 2 );
                    $header_parts = array_map( 'trim', $header_parts );

                    if ( count( $header_parts ) === 2 && ! empty( $header_parts[0] ) && ! empty( $header_parts[1] ) ) {
                        $headers[ sanitize_text_field( $header_parts[0] ) ] = sanitize_text_field( $header_parts[1] );
                    }
                }
            }

            return $headers;
        }

        /**
         * Validate ActionNetwork URL
         *
         * @since    1.0.0
         * @param    string     $url    The URL to validate
         * @return   bool              True if valid, false otherwise
         */
        private function validate_actionnetwork_url( $url ) {
            // Verify that is a valid URL
            if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
                return false;
            }

            $parsed = wp_parse_url( $url );

            // Verify that is from Action Network
            if ( ! isset( $parsed['host'] ) || $parsed['host'] !== 'actionnetwork.org' ) {
                return false;
            }

            // Verify that has a valid path (optional, to help the user)
            $valid_patterns = [
                '/api/v2/forms/',
                '/api/v2/petitions/',
                '/api/v2/events/',
                '/api/v2/fundraising_pages/',
                '/api/v2/advocacy_campaigns/',
                '/forms/',
                '/petitions/',
                '/events/',
            ];

            foreach ( $valid_patterns as $pattern ) {
                if ( isset( $parsed['path'] ) && strpos( $parsed['path'], $pattern ) !== false ) {
                    return true;
                }
            }

            // If doesn't match known patterns, allow anyway
            // (could be a custom webhook or URL we don't know)
            return true;
        }

        /**
         * Send to ActionNetwork with retry logic
         *
         * @since    1.0.0
         * @param    string     $url        The URL to send to
         * @param    array      $args       The request arguments
         * @param    int        $retries    Number of retries
         * @return   array                  The response
         */
        private function send_to_actionnetwork_with_retry( $url, $args, $retries = 3 ) {
            $last_error = null;

            for ( $i = 0; $i < $retries; $i++ ) {

                $response = wp_remote_post( $url, $args );

                // If successful (no WP error and response code < 400), return
                if ( ! is_wp_error( $response ) ) {
                    $response_code = wp_remote_retrieve_response_code( $response );
                    if ( $response_code < 400 ) {
                        return $response;
                    }
                    $last_error = new Exception( "HTTP {$response_code}: " . wp_remote_retrieve_body( $response ) );
                } else {
                    $last_error = new Exception( $response->get_error_message() );
                }

                // Exponential backoff (but only if we have more retries left)
                if ( $i < $retries - 1 ) {
                    $sleep_time = pow( 2, $i );
                    sleep( $sleep_time );
                }
            }

            // If we get here, all retries failed
            throw $last_error;
        }

    }
}
