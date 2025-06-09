<?php
/**
 * CFAN_Logger
 *
 * @package         Contact_Form_7_to_Action_Network_Integration
 * @subpackage      CFAN_Logger
 * @since           1.0.0
 *
 */

// If this file is called directly, call the cops.
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

if ( ! class_exists( 'CFAN_Logger' ) ) {
    class CFAN_Logger {

        /**
         * Log a message if WP_DEBUG is enabled
         *
         * @since    1.0.0
         * @param    string     $message    The message to log
         * @param    string     $level      The log level (info, warning, error)
         */
        public static function log( $message, $level = 'info' ) {
            if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                $formatted_message = sprintf( '[CF7-AN] [%s] %s', strtoupper( $level ), $message );
                // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
                error_log( $formatted_message );
            }
        }

        /**
         * Log API request details
         *
         * @since    1.0.0
         * @param    string     $url        The API URL
         * @param    array      $data       The data being sent
         * @param    array      $response   The API response
         */
        public static function log_api_request( $url, $data, $response ) {
            if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
                return;
            }

            self::log( "API Request to: {$url}" );
            self::log( "Data sent: " . wp_json_encode( $data ) );
            
            if ( is_wp_error( $response ) ) {
                self::log( "Error: " . $response->get_error_message(), 'error' );
            } else {
                $response_code = wp_remote_retrieve_response_code( $response );
                $response_body = wp_remote_retrieve_body( $response );
                self::log( "Response code: {$response_code}" );
                self::log( "Response body: {$response_body}" );
            }
        }

        /**
         * Log error message
         *
         * @since    1.0.0
         * @param    string     $message    The error message
         */
        public static function error( $message ) {
            self::log( $message, 'error' );
        }

        /**
         * Log warning message
         *
         * @since    1.0.0
         * @param    string     $message    The warning message
         */
        public static function warning( $message ) {
            self::log( $message, 'warning' );
        }

        /**
         * Log info message
         *
         * @since    1.0.0
         * @param    string     $message    The info message
         */
        public static function info( $message ) {
            self::log( $message, 'info' );
        }
    }
}