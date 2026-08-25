<?php
/**
 * Plugin Name: VentureX ERP & CRM Connector
 * Description: Connect your WordPress site to VentureX ERP & CRM
 * Version: 1.0.0
 * Author: VentureX
 * Requires at least: 5.0
 * Tested up to: 6.6
 * License: Proprietary
 */

if (!defined('ABSPATH')) {
    exit;
}

define('VENTUREX_VERSION', '1.0.0');
define('VENTUREX_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('VENTUREX_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once VENTUREX_PLUGIN_DIR . 'includes/class-venturex-api.php';
require_once VENTUREX_PLUGIN_DIR . 'includes/class-venturex-admin.php';
require_once VENTUREX_PLUGIN_DIR . 'includes/class-venturex-forms.php';
require_once VENTUREX_PLUGIN_DIR . 'includes/class-venturex-shortcode.php';

new VentureX_Admin();
new VentureX_Forms();
new VentureX_Shortcode();

register_activation_hook(__FILE__, 'venturex_activate');
register_deactivation_hook(__FILE__, 'venturex_deactivate');

function venturex_activate() {
    add_option('venturex_api_url', '');
    add_option('venturex_api_token', '');
    add_option('venturex_connection_status', 'disconnected');
}

function venturex_deactivate() {
    // Cleanup if needed
}

function venturex_encrypt($data) {
    $key = home_url();
    $iv = substr(md5($key), 0, 16);
    return base64_encode(openssl_encrypt($data, 'AES-128-CBC', $iv, 0, $iv));
}

function venturex_decrypt($data) {
    $key = home_url();
    $iv = substr(md5($key), 0, 16);
    return openssl_decrypt(base64_decode($data), 'AES-128-CBC', $iv, 0, $iv);
}
