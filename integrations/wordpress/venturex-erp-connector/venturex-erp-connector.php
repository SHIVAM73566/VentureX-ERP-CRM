<?php

/**
 * Plugin Name: VentureX ERP & CRM Connector
 * Description: Connect a WordPress site to VentureX ERP & CRM v1.3.1 over HTTPS using a Sanctum API token.
 * Version: 1.3.1
 * Author: Shivam Chaturvedi — VentureX ERP & CRM
 * Requires at least: 5.8
 * Tested up to: 6.8
 * Requires PHP: 7.4
 * License: MIT
 * License URI: https://opensource.org/licenses/MIT
 * Text Domain: venturex-erp-connector
 */

if (! defined('ABSPATH')) {
    exit;
}

define('VENTUREX_VERSION', '1.3.1');
define('VENTUREX_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('VENTUREX_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once VENTUREX_PLUGIN_DIR.'includes/class-venturex-api.php';
require_once VENTUREX_PLUGIN_DIR.'includes/class-venturex-admin.php';
require_once VENTUREX_PLUGIN_DIR.'includes/class-venturex-forms.php';
require_once VENTUREX_PLUGIN_DIR.'includes/class-venturex-shortcode.php';

new VentureX_Admin;
new VentureX_Forms;
new VentureX_Shortcode;

register_activation_hook(__FILE__, 'venturex_activate');
register_deactivation_hook(__FILE__, 'venturex_deactivate');

function venturex_activate()
{
    add_option('venturex_api_url', '');
    add_option('venturex_api_token', '');
    add_option('venturex_connection_status', 'disconnected');
}

function venturex_deactivate()
{
    // Keep configuration so reactivation does not require re-entering the token.
}

/**
 * Derive an authenticated encryption key from WordPress salt constants.
 * The token is never stored using the home URL as a key/IV.
 */
function venturex_crypto_key()
{
    if (! defined('AUTH_KEY') || ! defined('LOGGED_IN_KEY')) {
        return false;
    }
    if (AUTH_KEY === '' || LOGGED_IN_KEY === '' || ! function_exists('hash_hkdf')) {
        return false;
    }

    return hash_hkdf('sha256', AUTH_KEY.'|'.LOGGED_IN_KEY, 32, 'venturex/api-token/v1');
}

/**
 * AES-256-GCM authenticated encryption. Requires OpenSSL and WordPress salts.
 * Format: vx1:<base64 iv>.<base64 tag>.<base64 ciphertext>
 */
function venturex_encrypt($data)
{
    $key = venturex_crypto_key();
    if (! $key || ! function_exists('openssl_encrypt')) {
        return '';
    }

    try {
        $iv = random_bytes(12);
    } catch (Exception $e) {
        return '';
    }

    $tag = '';
    $ciphertext = openssl_encrypt((string) $data, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);
    if ($ciphertext === false || $tag === '') {
        return '';
    }

    return 'vx1:'.base64_encode($iv).'.'.base64_encode($tag).'.'.base64_encode($ciphertext);
}

/**
 * Decrypt only the authenticated v1 format. Legacy tokens are intentionally
 * not accepted; re-enter the token in the settings page after upgrading.
 */
function venturex_decrypt($data)
{
    $key = venturex_crypto_key();
    if (! $key || ! function_exists('openssl_decrypt') || ! is_string($data) || strpos($data, 'vx1:') !== 0) {
        return '';
    }

    $parts = explode('.', substr($data, 4));
    if (count($parts) !== 3) {
        return '';
    }

    $iv = base64_decode($parts[0], true);
    $tag = base64_decode($parts[1], true);
    $ciphertext = base64_decode($parts[2], true);
    if ($iv === false || $tag === false || $ciphertext === false || strlen($iv) !== 12 || strlen($tag) !== 16) {
        return '';
    }

    $plaintext = openssl_decrypt($ciphertext, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);
    if ($plaintext === false) {
        return '';
    }

    return $plaintext;
}

/**
 * HTTPS is required for remote API URLs. Localhost is allowed for development.
 */
function venturex_is_allowed_api_url($url)
{
    $parts = wp_parse_url((string) $url);
    if (! is_array($parts) || empty($parts['scheme']) || empty($parts['host'])) {
        return false;
    }

    $scheme = strtolower($parts['scheme']);
    $host = strtolower($parts['host']);
    if ($scheme === 'https') {
        return true;
    }

    return in_array($host, ['localhost', '127.0.0.1', '::1'], true);
}
