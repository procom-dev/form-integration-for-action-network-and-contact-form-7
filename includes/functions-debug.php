<?php

/**
 * A helper to debug
 *
 * @package         CF7_ActionNetwork_Integration
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
        print_r( $param );
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
        error_log( '[CF7-AN] ' . print_r( $param, true ) );
    }
}

