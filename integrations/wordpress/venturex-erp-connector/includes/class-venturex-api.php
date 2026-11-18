<?php

if (! defined('ABSPATH')) {
    exit;
}

class VentureX_API
{
    /** @var string */
    private $api_url;

    /** @var string */
    private $api_token;

    /**
     * Endpoints implemented by routes/api.php. Never expose arbitrary paths.
     */
    private $allowlist = [
        '/user' => ['GET'],
        '/crm/customers' => ['GET', 'POST'],
        '/crm/leads' => ['GET', 'POST'],
        '/support/tickets' => ['GET', 'POST'],
        '/sales/orders' => ['GET', 'POST'],
    ];

    public function __construct()
    {
        $this->api_url = rtrim((string) get_option('venturex_api_url', ''), '/');
        $encrypted_token = (string) get_option('venturex_api_token', '');
        $this->api_token = $encrypted_token ? venturex_decrypt($encrypted_token) : '';
    }

    /**
     * GET /api/user is the authenticated connectivity check. There is no
     * /api/health endpoint in v1.3.1.
     */
    public function testConnection()
    {
        return $this->request('GET', '/user');
    }

    public function getCustomers($params = [])
    {
        return $this->request('GET', '/crm/customers', $params);
    }

    public function createCustomer($data)
    {
        return $this->request('POST', '/crm/customers', $this->sanitizePayload($data));
    }

    public function createLead($data)
    {
        return $this->request('POST', '/crm/leads', $this->sanitizePayload($data));
    }

    public function createTicket($data)
    {
        return $this->request('POST', '/support/tickets', $this->sanitizePayload($data));
    }

    public function createOrder($data)
    {
        return $this->request('POST', '/sales/orders', $this->sanitizePayload($data));
    }

    /**
     * Public so the authenticated admin REST proxy can reuse the same
     * validation and token handling.
     */
    public function request($method, $endpoint, $params = [])
    {
        $method = strtoupper((string) $method);
        $endpoint = '/'.ltrim((string) $endpoint, '/');

        if (empty($this->api_url) || empty($this->api_token)) {
            return new WP_Error('venturex_config', 'VentureX API URL or token is not configured.');
        }
        if (! venturex_is_allowed_api_url($this->api_url)) {
            return new WP_Error('venturex_https', 'The VentureX API URL must use HTTPS (localhost is allowed for development).');
        }
        if (! isset($this->allowlist[$endpoint]) || ! in_array($method, $this->allowlist[$endpoint], true)) {
            return new WP_Error('venturex_endpoint', 'This API endpoint is not supported by the connector.');
        }

        $url = $this->api_url.$endpoint;
        $args = [
            'method' => $method,
            'timeout' => 30,
            'redirection' => 3,
            'headers' => [
                'Authorization' => 'Bearer '.$this->api_token,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'User-Agent' => 'VentureX-WordPress-Connector/'.VENTUREX_VERSION,
            ],
        ];

        if ($method === 'GET' && ! empty($params)) {
            $url = add_query_arg($this->sanitizePayload($params), $url);
        } elseif ($method === 'POST' && ! empty($params)) {
            $args['body'] = wp_json_encode($this->sanitizePayload($params));
        }

        $response = wp_remote_request($url, $args);
        if (is_wp_error($response)) {
            return new WP_Error('venturex_transport', 'Could not reach the VentureX API. Check the API URL and TLS certificate.');
        }

        $code = (int) wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $decoded = json_decode($body, true);

        if ($code >= 400) {
            $message = isset($decoded['message']) ? wp_strip_all_tags((string) $decoded['message']) : 'API error '.$code;

            return new WP_Error('venturex_api_error', $message, ['status' => $code]);
        }

        return is_array($decoded) ? $decoded : ['raw' => $body];
    }

    /**
     * Recursively sanitise request data. Nested arrays are preserved so
     * WooCommerce-style line items can be mapped by the application.
     */
    public function sanitizePayload($data)
    {
        if (! is_array($data)) {
            return is_scalar($data) ? sanitize_text_field((string) $data) : '';
        }

        $clean = [];
        foreach ($data as $key => $value) {
            $clean_key = sanitize_key((string) $key);
            if (is_array($value)) {
                $clean[$clean_key] = $this->sanitizePayload($value);
            } elseif (is_bool($value)) {
                $clean[$clean_key] = $value;
            } elseif (is_numeric($value)) {
                $clean[$clean_key] = $value;
            } else {
                $clean[$clean_key] = sanitize_text_field((string) $value);
            }
        }

        return $clean;
    }
}
