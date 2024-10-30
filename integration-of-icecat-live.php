<?php

/*
Plugin Name: Integration of Icecat Live
Description: Embed the product datasheet into your website.
Version: 1.0
Requires at least: 6.1
Requires PHP: 7.4
Author: Nsweb
Text Domain: integration-of-icecat-live
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

if( !defined('ABSPATH') ){
    die('No direct call');
}

define( 'NSWICLV_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'NSWICLV_TEXTDOMAIN', 'integration-of-icecat-live' );
define( 'NSWICLV_SHORTNAME', 'nswiclv' );
define( 'NSWICLV_PLUGIN_BASENANE', plugin_basename(__FILE__) );

register_activation_hook( __FILE__, array( '\Nswintgricecatlive\Activation', 'activate' ) );
register_deactivation_hook( __FILE__, array( '\Nswintgricecatlive\Activation', 'deactivate' ) );

require_once NSWICLV_PLUGIN_DIR . '/Icecat.php';
require_once NSWICLV_PLUGIN_DIR . '/Shortcode.php';

if( is_admin() ){
    require_once NSWICLV_PLUGIN_DIR . '/Admin.php';
    add_action( 'admin_init', array( '\Nswintgricecatlive\Admin', 'admin_init' ) );
    add_action( 'admin_menu', array( '\Nswintgricecatlive\Admin', 'admin_menu' ) );
}

add_action( 'init', array( '\Nswintgricecatlive\Shortcode', 'init' ) );

