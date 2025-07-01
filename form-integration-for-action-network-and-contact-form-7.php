<?php
/**
 *
 * @package           Form_Integration_for_Action_Network_and_Contact_Form_7
 * @since             1.0.0
 *
 * Plugin Name:       Form Integration for Action Network and Contact Form 7
 * Plugin URI:        https://github.com/procom-dev/form-integration-for-action-network-and-contact-form-7
 * Description:       Use Contact Form 7 to send data directly to ActionNetwork with automatic field mapping!
 * Version:           1.0.0
 * Author:            procom.dev
 * Author URI:        https://procom.dev
 * Text Domain:       form-integration-for-action-network-and-contact-form-7
 * Domain Path:       /languages
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Requires Plugins:  contact-form-7
 *
 */

// If this file is called directly, call the cops.
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

if ( ! class_exists( 'CFAN_Core' ) ) {

    class CFAN_Core {

        /**
         * The array of actions registered with WordPress.
         *
         * @since    1.0.0
         * @access   protected
         * @var      array    $actions    The actions registered with WordPress to fire when the plugin loads.
         */
        protected $actions = array();

        /**
         * The array of filters registered with WordPress.
         *
         * @since    1.0.0
         * @access   protected
         * @var      array    $filters    The filters registered with WordPress to fire when the plugin loads.
         */
        protected $filters = array();

        /**
         * The array of modules of plugin.
         *
         * @since    1.0.0
         * @access   protected
         * @var      array    $modules    The modules to be used in this plugin.
         */
        protected $modules = array();

        /**
         * Define the core functionality of the plugin.
         *
         * @since    1.0.0
         */
        public function __construct() {
            $this->include_functions_file();
            $this->define_hooks();
            $this->add_modules();
        }

        /**
         * Define things to run when activate plugin
         *
         * @since    1.0.0
         */
        public function on_activation() {
            flush_rewrite_rules();
        }

        /**
         * Register the hooks for Core
         *
         * @since    1.0.0
         * @access   private
         */
        private function define_hooks() {
            // Internationalization
            $this->add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );

            // Activation Hook
            register_activation_hook( __FILE__, array( $this, 'on_activation' ) );
        }

        /**
         * Load all the plugins modules.
         *
         * @since    1.0.0
         * @access   private
         */
        private function add_modules() {
            // Require module files:
            require_once plugin_dir_path( __FILE__ ) . 'modules/cf7/class-module-cf7.php';
            require_once plugin_dir_path( __FILE__ ) . 'modules/actionnetwork/class-module-actionnetwork.php';

            // Instantiate the Module's classes:
            $this->modules['cf7'] = new CFAN_CF7_Module( $this );
            $this->modules['actionnetwork'] = new CFAN_ActionNetwork_Module( $this );
        }

        /**
         * Load the core functions file
         *
         * @since    1.0.0
         * @access   private
         */
        private function include_functions_file() {
            require_once plugin_dir_path( __FILE__ ) . 'includes/functions-debug.php';
            require_once plugin_dir_path( __FILE__ ) . 'includes/class-cfan-logger.php';
        }

        /**
         * A utility function that is used to register the actions and hooks into a single
         * collection.
         *
         * @since    1.0.0
         * @access   private
         * @param    array      $hooks              The collection of hooks that is being registered (that is, actions or filters).
         * @param    string     $hook               The name of the WordPress filter that is being registered.
         * @param    string     $callback           The callback function or a array( $obj, 'method' ) to public method of a class.
         * @param    int        $priority           The priority at which the function should be fired.
         * @param    int        $accepted_args      The number of arguments that should be passed to the $callback.
         * @return   array                          The collection of actions and filters registered with WordPress.
         */
        private function add_hook( $hooks, $hook, $callback, $priority, $accepted_args ) {
            $hooks[] = array(
                'hook'          => $hook,
                'callback'      => $callback,
                'priority'      => $priority,
                'accepted_args' => $accepted_args
            );

            return $hooks;
        }

        /**
         * Add a new action to the collection to be registered with WordPress.
         *
         * @since    1.0.0
         * @param    string     $hook             The name of the WordPress action that is being registered.
         * @param    string     $callback         The callback function or a array( $obj, 'method' ) to public method of a class.
         * @param    int        $priority         Optional. he priority at which the function should be fired. Default is 10.
         * @param    int        $accepted_args    Optional. The number of arguments that should be passed to the $callback. Default is 1.
         */
        public function add_action( $hook, $callback, $priority = 10, $accepted_args = 1 ) {
            $this->actions = $this->add_hook( $this->actions, $hook, $callback, $priority, $accepted_args );
        }

        /**
         * Add a new filter to the collection to be registered with WordPress.
         *
         * @since    1.0.0
         * @param    string     $hook             The name of the WordPress filter that is being registered.
         * @param    string     $callback         The callback function or a array( $obj, 'method' ) to public method of a class.
         * @param    int        $priority         Optional. he priority at which the function should be fired. Default is 10.
         * @param    int        $accepted_args    Optional. The number of arguments that should be passed to the $callback. Default is 1
         */
        public function add_filter( $hook, $callback, $priority = 10, $accepted_args = 1 ) {
            $this->filters = $this->add_hook( $this->filters, $hook, $callback, $priority, $accepted_args );
        }

        /**
         * Define the locale for this plugin for internationalization.
         *
         *
         * @since    1.0.0
         * @access   private
         */
        public function load_plugin_textdomain() {
            load_plugin_textdomain( CFAN_TEXTDOMAIN, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
        }

        /**
         * Run the plugin.
         *
         * @since    1.0.0
         */
        public function run() {
            // Definitions to plugin
            define( 'CFAN_VERSION', '1.0.0' );
            define( 'CFAN_PLUGIN_FILE', __FILE__ );
            define( 'CFAN_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
            define( 'CFAN_PLUGIN_PATH', WP_PLUGIN_DIR . '/' . dirname( CFAN_PLUGIN_BASENAME ) );
            define( 'CFAN_PLUGIN_DIR', dirname( CFAN_PLUGIN_BASENAME ) );
            define( 'CFAN_PLUGIN_URL', plugins_url( '', __FILE__ ) );

            // Definition of upload_dir
            if ( ! defined( 'CFAN_UPLOAD_DIR' ) ) {
                define( 'CFAN_UPLOAD_DIR', 'cf7-actionnetwork-uploads' );
            }

            // Definition of text domain
            if ( ! defined( 'CFAN_TEXTDOMAIN' ) ) {
                define( 'CFAN_TEXTDOMAIN', 'form-integration-for-action-network-and-contact-form-7' );
            }

            // Running Modules (first of all)
            foreach ( $this->modules as $module ) {
                $module->run();
            }

            // Running Filters
            foreach ( $this->filters as $hook ) {
                add_filter( $hook['hook'], $hook['callback'], $hook['priority'], $hook['accepted_args'] );
            }

            // Running Actions
            foreach ( $this->actions as $hook ) {
                add_action( $hook['hook'], $hook['callback'], $hook['priority'], $hook['accepted_args'] );
            }
        }
    }
}

/**
 * Making things happening
 */
$cfan_core = new CFAN_Core();
$cfan_core->run();

