<?php
/*
    Plugin functions
*/

// Plugin initialize, create secret identifier if not already set

function pjit_clp_init() {
    $secret = get_option( PJIT_CLP_OPTION_NAME );
    if ( empty( $secret ) ) {
        $secret = strtolower( str_shuffle( md5( microtime() ) ) );
        if ( ! add_option( PJIT_CLP_OPTION_NAME, $secret ) ) {
            return false;
        }
    }
    define( 'PJIT_CLP_SECRET', $secret );
    return true;
}

// Check if new login page is set

function pjit_clp_is_login_page_set() {
    $args = array(
        'post_type' => 'page',
        'meta_value' => PJIT_CLP_TEMPLATE_FILE,
        'numberposts' => 1
    );
    if ( empty( get_posts( $args ) ) ) {
        return false;
    }
    return true;
}

// Set plugin cookie

function pjit_clp_set_cookie() {
    setcookie( PJIT_CLP_SECRET, '1', time() + 60*60*24*1, '/', $_SERVER['HTTP_HOST'], false, true );
}

// Remove plugin cookie

function pjit_clp_remove_cookie() {
    if ( isset( $_COOKIE[PJIT_CLP_SECRET] ) ) {
        setcookie( PJIT_CLP_SECRET, '', time() - 3600, '/', $_SERVER['HTTP_HOST'], false, true );
    }
}

// Plugin activate / deactivate

function pjit_clp_activate() {
    pjit_clp_set_cookie();
}

function pjit_clp_deactivate() {
    pjit_clp_remove_cookie();
}

// Remove options from db on plugin uninstall

function pjit_clp_uninstall() {
    if ( get_option( PJIT_CLP_OPTION_NAME ) ) {
        delete_option( PJIT_CLP_OPTION_NAME );
    }
    pjit_clp_remove_cookie();
}

// Add custom login page template to the template select dropdown

function pjit_clp_add_template( $page_templates ) {
    $page_templates[PJIT_CLP_TEMPLATE_FILE] = PJIT_CLP_TEMPLATE_NAME;
    return $page_templates;
}

// Load template file from plugin directory

function pjit_clp_load_template( $page_template ) {
    if ( PJIT_CLP_TEMPLATE_FILE === get_page_template_slug() ) {
        $page_template = PJIT_CLP_PLUGIN_DIR . PJIT_CLP_TEMPLATE_FILE;
    }
    return $page_template;
}

// Filter login url

function pjit_clp_filter_login_url( $login_url ) {
    $secret = $_GET['secret'];
    if ( pjit_clp_is_login_page_set() && ! is_user_logged_in() && ! isset( $_COOKIE[PJIT_CLP_SECRET] )  ) {
        if ( isset( $secret ) && ( $secret === PJIT_CLP_SECRET ) ) {
            pjit_clp_set_cookie();
        } else {
            wp_redirect( home_url( '/' ) );
            exit;
        }
    }
    return $login_url;
}

// Cleanup when user logs out and leaves the login page

function pjit_clp_homepage_cleanup() {
    if ( ! is_user_logged_in() && ( is_front_page() || is_singular() ) ) {
        pjit_clp_remove_cookie();
    }
}