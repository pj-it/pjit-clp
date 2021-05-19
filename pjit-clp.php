<?php
/*
Plugin Name: Custom login page
Description: Enables a custom page template that substitutes the standard Wordpress login url.
Author: PJ-IT
Author URI: https://github.com/pj-it/
Version: 0.1a
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*
    Plugin setup
*/

function pjit_clp_init() {
    $secret = get_option( 'pjit_clp_secret' );
    if ( ! $secret ) {
        $secret = strtolower( str_shuffle( md5( microtime() ) ) );
        add_option( 'pjit_clp_secret', $secret );
    }
    if ( ! empty( $secret ) ) {
        define( 'PJIT_CLP_SECRET', $secret );
    }
}

pjit_clp_init();

if ( ! defined( 'PJIT_CLP_SECRET' ) ) return;

/*
    Handle activate/uninstall
*/

function pjit_clp_cleanup() {
    $secret = PJIT_CLP_SECRET;
    if ( isset( $_COOKIE[$secret] ) ) {
        setcookie( $secret, '', time() - 3600, '/' );
    }
}

function pjit_clp_uninstall() {
    $secret = get_option( 'pjit_clp_secret' );
    if ( $secret ) {
        pjit_clp_cleanup();    
        delete_option( 'pjit_clp_secret' );
    }
}

register_activation_hook( __FILE__, 'pjit_clp_init' );
register_deactivation_hook( __FILE__, 'pjit_clp_cleanup' );
register_uninstall_hook( __FILE__, 'pjit_clp_uninstall' );

/*
    Add custom login page template to the template select dropdown
*/

function pjit_clp_add_template( $page_templates ) {
    $page_templates['pjit-clp-template.php'] = 'Custom login page';
    return $page_templates;
}

add_filter( 'theme_page_templates', 'pjit_clp_add_template' );

/*
    Load template file from plugin directory
*/

function pjit_clp_load_template( $page_template ) {
    if ( 'pjit-clp-template.php' === get_page_template_slug() ) {
        $page_template = dirname( __FILE__ ) . '/pjit-clp-template.php';
    }
    return $page_template;
}

add_filter( 'page_template', 'pjit_clp_load_template' );

/*
    Filter login url
*/

function pjit_clp_filter_login_url( $login_url ) {
    $key = $_GET['key'];
    $secret = PJIT_CLP_SECRET;
    if ( ! isset( $_COOKIE[$secret] ) && ! is_user_logged_in() ) {
        if ( isset( $key ) && ( $key === $secret ) ) {
            setcookie( $secret, '1', time()+60*60*24*1, '/', $_SERVER['HTTP_HOST'], false, true );
        } else {
            wp_redirect( home_url( '/' ) );
            exit;
        }
    }
    return $login_url;
}

add_filter( 'login_url', 'pjit_clp_filter_login_url' );

/*
    Cleanup after user logout
*/

function pjit_clp_logout() {
    pjit_clp_cleanup();
    wp_redirect( home_url( '/' ) );
    exit;
}

add_action( 'wp_logout', 'pjit_clp_logout' );