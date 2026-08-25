<?php
if (!defined('ABSPATH')) exit;

class VentureX_Admin {

    public function __construct() {
        add_action('admin_menu', array($this, 'addMenu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue'));
        add_action('wp_ajax_venturex_test_connection', array($this, 'ajaxTestConnection'));
        add_action('wp_ajax_venturex_save_settings', array($this, 'ajaxSaveSettings'));
    }

    public function addMenu() {
        add_options_page(
            'VentureX ERP',
            'VentureX ERP',
            'manage_options',
            'venturex-erp',
            array($this, 'renderPage')
        );
    }

    public function enqueue($hook) {
        if ($hook !== 'settings_page_venturex-erp') {
            return;
        }
        wp_enqueue_style('venturex-admin', VENTUREX_PLUGIN_URL . 'assets/admin.css', array(), VENTUREX_VERSION);
        wp_enqueue_script('venturex-admin', VENTUREX_PLUGIN_URL . 'assets/admin.js', array('jquery'), VENTUREX_VERSION, true);
        wp_localize_script('venturex-admin', 'venturexAjax', array(
            'url'   => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('venturex_admin_nonce'),
        ));
    }

    public function renderPage() {
        $api_url = get_option('venturex_api_url', '');
        $status  = get_option('venturex_connection_status', 'disconnected');
        ?>
        <div class="wrap">
            <h1>VentureX ERP &amp; CRM Settings</h1>
            <div class="venturex-card">
                <h2>Connection Settings</h2>
                <table class="form-table">
                    <tr>
                        <th><label for="venturex_api_url">API URL</label></th>
                        <td>
                            <input type="url" id="venturex_api_url" name="venturex_api_url"
                                   value="<?php echo esc_attr($api_url); ?>"
                                   class="regular-text"
                                   placeholder="https://your-erp-domain.com/api">
                        </td>
                    </tr>
                    <tr>
                        <th><label for="venturex_api_token">API Token</label></th>
                        <td>
                            <input type="password" id="venturex_api_token" name="venturex_api_token"
                                   value=""
                                   class="regular-text"
                                   placeholder="Enter your API token">
                        </td>
                    </tr>
                    <tr>
                        <th>Connection Status</th>
                        <td>
                            <span id="venturex-status" class="venturex-status venturex-status--<?php echo esc_attr($status); ?>">
                                <?php echo $status === 'connected' ? 'Connected' : 'Disconnected'; ?>
                            </span>
                        </td>
                    </tr>
                </table>
                <p class="submit">
                    <button type="button" id="venturex-save" class="button button-primary">Save Settings</button>
                    <button type="button" id="venturex-test" class="button">Test Connection</button>
                    <span id="venturex-loading" class="venturex-loading" style="display:none;">Working...</span>
                </p>
            </div>
            <div class="venturex-card">
                <h2>Integrations</h2>
                <p>The plugin automatically hooks into:</p>
                <ul>
                    <li>Contact Form 7 submissions</li>
                    <li>Gravity Forms submissions</li>
                    <li>Generic form submissions (WordPress comment forms)</li>
                    <li>WooCommerce order creation (when WooCommerce is active)</li>
                </ul>
                <p>Use the <code>[venturex_form]</code> shortcode to add a lead capture form anywhere.</p>
            </div>
        </div>
        <?php
    }

    public function ajaxTestConnection() {
        check_ajax_referer('venturex_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        $api = new VentureX_API();
        $result = $api->testConnection();

        if (is_wp_error($result)) {
            update_option('venturex_connection_status', 'disconnected');
            wp_send_json_error($result->get_error_message());
        }

        update_option('venturex_connection_status', 'connected');
        wp_send_json_success('Connection successful');
    }

    public function ajaxSaveSettings() {
        check_ajax_referer('venturex_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        $url = isset($_POST['api_url']) ? esc_url_raw($_POST['api_url']) : '';
        $token = isset($_POST['api_token']) ? sanitize_text_field($_POST['api_token']) : '';

        update_option('venturex_api_url', $url);

        if (!empty($token)) {
            update_option('venturex_api_token', venturex_encrypt($token));
        }

        update_option('venturex_connection_status', 'disconnected');

        wp_send_json_success('Settings saved');
    }
}
