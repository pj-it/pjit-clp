<?php
/*
Plugin Name: Custom login page
Description: Enables a custom page template that substitutes the standard Wordpress login url.
Author: PJ-IT
Author URI: https://github.com/pj-it/
Version: 0.1a
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Plugin setup

define( 'PJIT_CLP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'PJIT_CLP_FUNCTIONS_FILE', 'pjit-clp-functions.php' );
define( 'PJIT_CLP_TEMPLATE_FILE', 'pjit-clp-template.php' );
define( 'PJIT_CLP_TEMPLATE_NAME', 'Custom login page' );
define( 'PJIT_CLP_OPTION_NAME', 'pjit_clp_secret' );

// Plugin initialize

@require_once PJIT_CLP_PLUGIN_DIR . PJIT_CLP_FUNCTIONS_FILE;
if ( ! pjit_clp_init() ) return; // Do nothing if plugin fails to initialize for some reason

// Handle activate / deactivate / uninstall

register_activation_hook( __FILE__, 'pjit_clp_activate' );
register_deactivation_hook( __FILE__, 'pjit_clp_cleanup' );
register_uninstall_hook( __FILE__, 'pjit_clp_uninstall' );

// Add custom login page template to the template select dropdown

add_filter( 'theme_page_templates', 'pjit_clp_add_template' );

// Load template file from plugin directory

add_filter( 'page_template', 'pjit_clp_load_template' );

// Filter login url

add_filter( 'login_url', 'pjit_clp_filter_login_url' );

// Cleanup when user logs out and goes back to home page

add_action( 'template_redirect', 'pjit_clp_homepage_cleanup' );