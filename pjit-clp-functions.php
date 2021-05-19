<?php

/*
    Plugin functions
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

function pjit_clp_is_login_page_set() {
    $args = array(
        'post_type' => 'page',
        'meta_value' => PJIT_CLP_TEMPLATE_FILE,
        'numberposts' => 1
    );
    $result = get_posts( $args );
    if ( ! empty( $result ) ) {
        return true;
    }
    return false;
}

function pjit_clp_cleanup() {
    if ( isset( $_COOKIE[PJIT_CLP_SECRET] ) ) {
        setcookie( PJIT_CLP_SECRET, '', time() - 3600, '/' );
    }
}

function pjit_clp_uninstall() {
    if ( get_option( 'pjit_clp_secret' ) ) {
        delete_option( 'pjit_clp_secret' );
    }
    pjit_clp_cleanup();
}

function pjit_clp_add_template( $page_templates ) {
    $page_templates['pjit-clp-template.php'] = 'Custom login page';
    return $page_templates;
}

function pjit_clp_load_template( $page_template ) {
    if ( 'pjit-clp-template.php' === get_page_template_slug() ) {
        $page_template = PJIT_CLP_PLUGIN_DIR . PJIT_CLP_TEMPLATE_FILE;
    }
    return $page_template;
}

function pjit_clp_filter_login_url( $login_url ) {
    $key = $_GET['key'];
    if ( pjit_clp_is_login_page_set() && ! is_user_logged_in() && ! isset( $_COOKIE[PJIT_CLP_SECRET] )  ) {
        if ( isset( $key ) && ( $key === PJIT_CLP_SECRET ) ) {
            setcookie( PJIT_CLP_SECRET, '1', time()+60*60*24*1, '/', $_SERVER['HTTP_HOST'], false, true );
        } else {
            wp_redirect( home_url( '/' ) );
            exit;
        }
    }
    return $login_url;
}

function pjit_clp_homepage_cleanup() {
    if ( ! is_user_logged_in() ) {
        pjit_clp_cleanup();
    }
}