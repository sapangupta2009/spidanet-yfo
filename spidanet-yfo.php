<?php
/**
 * Plugin Name: Spidanet Your Food Order
 * Plugin URI: http://spidanet.mobi
 * Description: Spidanet Your Food Order plugin is a extension of WooCommerce Your Food Order for the super admin reporting of all restaurants.
 * Version: 1.0.0
 * Author: spidanet
 * Author URI: http://spidanet.mobi
 * Developer: Sapan Kumar Gupta
 * Developer URI: http://spidanet.mobi
 * Text Domain: spidanet-yfo
 * Domain Path: /languages
 *
 */
 
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

define("SPIDANET_YFO_VERSION", '1.0.0' );
define("SPIDANET_YFO_DB_VERSION", '1.0' );

/**
 * Check if WooCommerce Your Food Order is active
 **/
if ( !in_array( 'woocommerce-yfo/woocommerce-yfo.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    return;
}

/**
 * Check if table is already created or not
 **/
function spidanet_install() {
	global $wpdb;
	$table_name = $wpdb->prefix . 'stransactions';
	$charset_collate = $wpdb->get_charset_collate();
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	$sql = "CREATE TABLE $table_name (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `merchant_id` int(11) NOT NULL,
	  `order_id` int(11) NOT NULL,
	  `order_type` varchar(255) NOT NULL,
	  `gross_total` float NOT NULL,
	  `commission` float NOT NULL,
	  `merchant_total` float NOT NULL,
	  `delivery_total` float NOT NULL,
	  `fee` float NOT NULL,
	  `desc` longtext NOT NULL,
	  `date` datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
	  `status` tinyint(11) NOT NULL DEFAULT '1',
	  PRIMARY KEY (`id`)
	) $charset_collate;";
	dbDelta( $sql );

	add_option( 'jal_db_version', WC_YFO_DB_VERSION );
}

register_activation_hook( __FILE__, 'spidanet_install' );

/* Spidanet main page */
function spidanet_main_page() {
    add_submenu_page(
        'options-general.php',
        __('Spidanet Transactions', 'spidanet-yfo'),
        __('Spidanet Transactions', 'spidanet-yfo'),
        'manage_options',
        'spidanet-transactions',
        'spidanet_transactions_cb' );
}

add_action('admin_menu', 'spidanet_main_page');

require_once( 'inc/spidanet_config.php' );
require_once( 'inc/spidanet_functions.php' );

function spidanet_admin_scripts($hook) {
    if($hook != 'settings_page_spidanet-transactions') {
    	return;
    }
    wp_enqueue_style( 'jquery-tabs', '//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css' );
    wp_enqueue_style( 'spidanet-admin-custom-style', plugins_url('assets/css/spidanet_admin_custom.css',__FILE__) );
	wp_enqueue_script('spidanet-admin-custom-js', plugins_url('assets/js/spidanet_admin_custom.js',__FILE__), array("jquery", "jquery-ui-tabs", "jquery-ui-datepicker", "jquery-ui-dialog"), true );
}

add_action( 'admin_enqueue_scripts', 'spidanet_admin_scripts' );

add_action( 'wp_ajax_spidanet_send_merchant_amt', 'spidanet_send_merchant_amt_cb' );