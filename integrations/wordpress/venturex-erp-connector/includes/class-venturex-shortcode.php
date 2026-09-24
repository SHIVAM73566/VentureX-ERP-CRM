<?php
if (! defined('ABSPATH')) {
    exit;
}

class VentureX_Shortcode
{
    public function __construct()
    {
        add_shortcode('venturex_form', [$this, 'renderShortcode']);
        add_action('init', [$this, 'registerWidget']);
        add_action('widgets_init', [$this, 'loadWidget']);
        add_action('wp_enqueue_scripts', [$this, 'enqueueFrontend']);
        add_action('rest_api_init', [$this, 'registerRESTRoutes']);
        add_action('wp_ajax_venturex_submit_form', [$this, 'ajaxSubmitForm']);
        add_action('wp_ajax_nopriv_venturex_submit_form', [$this, 'ajaxSubmitForm']);
    }

    public function enqueueFrontend()
    {
        if (shortcode_exists('venturex_form') || is_active_widget(false, false, 'venturex_lead')) {
            wp_enqueue_style('venturex-frontend', VENTUREX_PLUGIN_URL.'assets/frontend.css', [], VENTUREX_VERSION);
            wp_enqueue_script('venturex-frontend', VENTUREX_PLUGIN_URL.'assets/frontend.js', ['jquery'], VENTUREX_VERSION, true);
            wp_localize_script('venturex-frontend', 'venturexFrontend', [
                'url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('venturex_frontend_nonce'),
            ]);
        }
    }

    public function renderShortcode($atts)
    {
        $atts = shortcode_atts([
            'title' => 'Contact Us',
        ], $atts, 'venturex_form');

        ob_start();
        include VENTUREX_PLUGIN_DIR.'templates/lead-form.php';

        return ob_get_clean();
    }

    public function registerWidget()
    {
        register_widget('VentureX_Lead_Widget');
    }

    public function loadWidget()
    {
        // Widget class is registered via registerWidget() in the constructor
    }

    public function ajaxSubmitForm()
    {
        check_ajax_referer('venturex_frontend_nonce', 'nonce');

        $data = [
            'name' => isset($_POST['vx_name']) ? sanitize_text_field($_POST['vx_name']) : '',
            'email' => isset($_POST['vx_email']) ? sanitize_email($_POST['vx_email']) : '',
            'phone' => isset($_POST['vx_phone']) ? sanitize_text_field($_POST['vx_phone']) : '',
            'company' => isset($_POST['vx_company']) ? sanitize_text_field($_POST['vx_company']) : '',
            'message' => isset($_POST['vx_message']) ? sanitize_textarea_field($_POST['vx_message']) : '',
            'source' => 'WordPress Form',
        ];

        if (empty($data['name']) || empty($data['email'])) {
            wp_send_json_error(['message' => 'Name and email are required']);
        }

        $api = new VentureX_API;
        $result = $api->createLead($data);

        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }

        wp_send_json_success(['message' => 'Thank you! We have received your message.']);
    }

    public function registerRESTRoutes()
    {
        register_rest_route('venturex/v1', '/proxy', [
            'methods' => 'POST',
            'callback' => [$this, 'restProxy'],
            'permission_callback' => '__return_true',
            'args' => [
                'endpoint' => [
                    'required' => true,
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'method' => [
                    'required' => false,
                    'default' => 'GET',
                    'sanitize_callback' => 'strtoupper',
                ],
                'data' => [
                    'required' => false,
                    'default' => [],
                ],
            ],
        ]);
    }

    public function restProxy($request)
    {
        $endpoint = $request->get_param('endpoint');
        $method = $request->get_param('method');
        $data = $request->get_param('data');

        // Limit allowed endpoints for security
        $allowed = ['customers', 'leads', 'tickets', 'products', 'orders'];
        $parts = explode('/', trim($endpoint, '/'));
        if (empty($parts[0]) || ! in_array($parts[0], $allowed)) {
            return new WP_Error('forbidden', 'Endpoint not allowed', ['status' => 403]);
        }

        $api = new VentureX_API;
        $result = $api->request($method, '/'.$endpoint, $data);

        if (is_wp_error($result)) {
            return new WP_Error($result->get_error_code(), $result->get_error_message(), ['status' => 500]);
        }

        return rest_ensure_response($result);
    }
}

class VentureX_Lead_Widget extends WP_Widget
{
    public function __construct()
    {
        parent::__construct(
            'venturex_lead',
            'VentureX Lead Form',
            ['description' => 'A lead capture form connected to VentureX ERP']
        );
    }

    public function widget($args, $instance)
    {
        echo $args['before_widget'];
        $title = ! empty($instance['title']) ? $instance['title'] : 'Contact Us';
        echo $args['before_title'].apply_filters('widget_title', $title).$args['after_title'];
        include VENTUREX_PLUGIN_DIR.'templates/lead-form.php';
        echo $args['after_widget'];
    }

    public function form($instance)
    {
        $title = ! empty($instance['title']) ? $instance['title'] : 'Contact Us';
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>">Title:</label>
            <input class="widefat" type="text"
                   id="<?php echo $this->get_field_id('title'); ?>"
                   name="<?php echo $this->get_field_name('title'); ?>"
                   value="<?php echo esc_attr($title); ?>">
        </p>
        <?php
    }

    public function update($new_instance, $old_instance)
    {
        $instance = [];
        $instance['title'] = sanitize_text_field($new_instance['title']);

        return $instance;
    }
}
