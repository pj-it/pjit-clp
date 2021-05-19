<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
if ( ! defined( 'PJIT_CLP_SECRET' ) ) return;
if ( ! is_user_logged_in() ) {
    $url = add_query_arg( 'secret', PJIT_CLP_SECRET, home_url( '/wp-login.php' ) );
    wp_redirect( $url );
    exit;
} else {
    wp_redirect( home_url( '/wp-admin/index.php' ) );
    exit;
}