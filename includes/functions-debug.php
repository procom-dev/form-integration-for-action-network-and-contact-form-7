<?php

/**
 * A helper to debug
 *
 * @package         Action_Network_Integration_for_Contact_Form_7
 * @since           2.3.0
 */

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

function cfan_activated_debug_functions() {
    return ( defined( 'WP_DEBUG' ) && WP_DEBUG ) && ! ( defined( 'CFAN_REMOVE_DEBUG_FUNCTIONS' ) && CFAN_REMOVE_DEBUG_FUNCTIONS );
}

/**
 * Emulate dd function from laravel
 *
 * @SuppressWarnings(PHPMD)
 */
if ( ! function_exists( 'cfan_dd' ) && cfan_activated_debug_functions() ) {
    function cfan_dd( $param, $include_pre = true ) {
        echo $include_pre ? '<pre>' : '';
        echo wp_kses_post( wp_json_encode( $param, JSON_PRETTY_PRINT ) );
        echo $include_pre ? '</pre>' : '';
        exit;
    }
}

/**
 * Emulate dump function from
 * laravel but write to logs
 */
if ( ! function_exists( 'cfan_dump' ) && cfan_activated_debug_functions() ) {
    function cfan_dump( $param ) {
        // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
        error_log( '[CF7-AN] ' . wp_json_encode( $param ) );
    }
}

