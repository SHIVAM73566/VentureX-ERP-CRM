<?php

if (! defined('ABSPATH')) {
    exit;
}

class VentureX_Admin
{
    public function __construct()
    {
        add_action('admin_menu', [$this, 'addMenu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue']);
        add_action('admin_notices', [$this, 'renderNotices']);
        add_action('wp_ajax_venturex_test_connection', [$this, 'ajaxTestConnection']);
        add_action('wp_ajax_venturex_save_settings', [$this, 'ajaxSaveSettings']);
    }

    public function addMenu()
    {
        add_options_page(
            'VentureX ERP',
            'VentureX ERP',
            'manage_options',
            'venturex-erp',
            [$this, 'renderPage']
        );
    }

    public function enqueue($hook)
    {
        if ($hook !== 'settings_page_venturex-erp') {
            return;
        }
        wp_enqueue_style('venturex-admin', VENTUREX_PLUGIN_URL.'assets/admin.css', [], VENTUREX_VERSION);
        wp_enqueue_script('venturex-admin', VENTUREX_PLUGIN_URL.'assets/admin.js', ['jquery'], VENTUREX_VERSION, true);
        wp_localize_script('venturex-admin', 'venturexAjax', [
            'url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('venturex_admin_nonce'),
        ]);
    }

    public function renderNotices()
    {
        if (! venturex_crypto_key() && function_exists('get_current_screen') && get_current_screen() && get_current_screen()->parent_base === 'settings') {
            echo '<div class="notice notice-warning"><p>'.esc_html__('VentureX Connector cannot encrypt your API token because AUTH_KEY or LOGGED_IN_KEY is unavailable. Check wp-config.php and the OpenSSL extension.', 'venturex-erp-connector').'</p></div>';
        }
    }

    public function renderPage()
    {
        $api_url = (string) get_option('venturex_api_url', '');
        $status = (string) get_option('venturex_connection_status', 'disconnected');
        $configured = (string) get_option('venturex_api_token', '') !== '';
        $status_label = $status === 'connected' ? __('Connected', 'venturex-erp-connector') : __('Disconnected', 'venturex-erp-connector');
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('VentureX ERP & CRM Settings', 'venturex-erp-connector'); ?></h1>
            <div class="venturex-card">
                <h2><?php echo esc_html__('Connection Settings', 'venturex-erp-connector'); ?></h2>
                <table class="form-table">
                    <tr>
                        <th><label for="venturex_api_url"><?php echo esc_html__('API URL', 'venturex-erp-connector'); ?></label></th>
                        <td>
                            <input type="url" id="venturex_api_url" name="venturex_api_url"
                                   value="<?php echo esc_attr($api_url); ?>"
                                   class="regular-text"
                                   placeholder="https://erp.example.com/api">
                            <p class="description"><?php echo esc_html__('Use the ERP subdomain ending in /api. HTTPS is required; localhost is allowed only for development.', 'venturex-erp-connector'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="venturex_api_token"><?php echo esc_html__('API Token', 'venturex-erp-connector'); ?></label></th>
                        <td>
                            <input type="password" id="venturex_api_token" name="venturex_api_token"
                                   value="" autocomplete="new-password"
                                   class="regular-text"
                                   placeholder="<?php echo $configured ? esc_attr__('Leave blank to keep the current token', 'venturex-erp-connector') : esc_attr__('Paste a Sanctum API token', 'venturex-erp-connector'); ?>">
                            <p class="description"><?php echo esc_html__('The token is encrypted with AES-256-GCM using keys derived from your WordPress salts. Re-enter it after upgrading from a pre-1.3.1 build.', 'venturex-erp-connector'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><?php echo esc_html__('Connection Status', 'venturex-erp-connector'); ?></th>
                        <td>
                            <span id="venturex-status" class="venturex-status venturex-status--<?php echo esc_attr($status); ?>">
                                <?php echo esc_html($status_label); ?>
                            </span>
                        </td>
                    </tr>
                </table>
                <p class="submit">
                    <button type="button" id="venturex-save" class="button button-primary"><?php echo esc_html__('Save Settings', 'venturex-erp-connector'); ?></button>
                    <button type="button" id="venturex-test" class="button"><?php echo esc_html__('Test Connection', 'venturex-erp-connector'); ?></button>
                    <span id="venturex-loading" class="venturex-loading" style="display:none;"><?php echo esc_html__('Working...', 'venturex-erp-connector'); ?></span>
                </p>
            </div>
            <div class="venturex-card">
                <h2><?php echo esc_html__('Supported Integrations', 'venturex-erp-connector'); ?></h2>
                <ul>
                    <li><code>[venturex_form]</code> — <?php echo esc_html__('lead capture through POST /api/crm/leads (supported).', 'venturex-erp-connector'); ?></li>
                    <li><?php echo esc_html__('Authenticated connection test through GET /api/user (supported).', 'venturex-erp-connector'); ?></li>
                </ul>
                <p class="description"><?php echo esc_html__('Contact Form 7, Gravity Forms, WordPress comments and WooCommerce hooks are experimental payload mappings and are not certified for production until verified against your field setup.', 'venturex-erp-connector'); ?></p>
            </div>
        </div>
        <?php
    }

    public function ajaxTestConnection()
    {
        check_ajax_referer('venturex_admin_nonce', 'nonce');

        if (! current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        $api = new VentureX_API;
        $result = $api->testConnection();

        if (is_wp_error($result)) {
            update_option('venturex_connection_status', 'disconnected');
            wp_send_json_error($result->get_error_message());
        }

        update_option('venturex_connection_status', 'connected');
        wp_send_json_success('Connection successful');
    }

    public function ajaxSaveSettings()
    {
        check_ajax_referer('venturex_admin_nonce', 'nonce');

        if (! current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        $url = isset($_POST['api_url']) ? esc_url_raw(wp_unslash($_POST['api_url'])) : '';
        $token = isset($_POST['api_token']) ? sanitize_text_field(wp_unslash($_POST['api_token'])) : '';

        if (! venturex_is_allowed_api_url($url)) {
            wp_send_json_error('The API URL must use HTTPS (localhost is allowed only for development).');
        }
        if (! venturex_crypto_key()) {
            wp_send_json_error('Token encryption is unavailable. Check AUTH_KEY, LOGGED_IN_KEY and the OpenSSL extension.');
        }

        update_option('venturex_api_url', $url);

        if ($token !== '') {
            $encrypted = venturex_encrypt($token);
            if ($encrypted === '') {
                wp_send_json_error('The token could not be encrypted; nothing was saved.');
            }
            update_option('venturex_api_token', $encrypted);
        }

        update_option('venturex_connection_status', 'disconnected');
        wp_send_json_success('Settings saved');
    }
}
