<?php
if (!defined('ABSPATH')) exit;

class VentureX_API {

    private $api_url;
    private $api_token;

    public function __construct() {
        $this->api_url = get_option('venturex_api_url', '');
        $encrypted_token = get_option('venturex_api_token', '');
        $this->api_token = $encrypted_token ? venturex_decrypt($encrypted_token) : '';
    }

    public function testConnection() {
        $response = $this->request('GET', '/health');
        return is_wp_error($response) ? $response : $response;
    }

    public function getCustomers($params = []) {
        return $this->request('GET', '/customers', $params);
    }

    public function createCustomer($data) {
        $sanitized = $this->sanitize($data);
        return $this->request('POST', '/customers', $sanitized);
    }

    public function createLead($data) {
        $sanitized = $this->sanitize($data);
        return $this->request('POST', '/leads', $sanitized);
    }

    public function createTicket($data) {
        $sanitized = $this->sanitize($data);
        return $this->request('POST', '/tickets', $sanitized);
    }

    public function createOrder($data) {
        $sanitized = $this->sanitize($data);
        return $this->request('POST', '/orders', $sanitized);
    }

    private function request($method, $endpoint, $params = []) {
        if (empty($this->api_url) || empty($this->api_token)) {
            return new WP_Error('venturex_config', 'API URL or token not configured');
        }

        $url = rtrim($this->api_url, '/') . $endpoint;

        $args = array(
            'method'  => $method,
            'timeout' => 30,
            'headers' => array(
                'Authorization' => 'Bearer ' . $this->api_token,
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
            ),
        );

        if ($method === 'GET' && !empty($params)) {
            $url = add_query_arg($params, $url);
        } elseif ($method === 'POST' && !empty($params)) {
            $args['body'] = wp_json_encode($params);
        }

        $response = wp_remote_post($url, $args);

        if (is_wp_error($response)) {
            return $response;
        }

        $code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $decoded = json_decode($body, true);

        if ($code >= 400) {
            $message = isset($decoded['message']) ? $decoded['message'] : 'API error ' . $code;
            return new WP_Error('venturex_api_error', $message, array('status' => $code));
        }

        return $decoded ? $decoded : array('raw' => $body);
    }

    private function sanitize($data) {
        $clean = array();
        foreach ($data as $key => $value) {
            $key = sanitize_key($key);
            if (is_string($value)) {
                $clean[$key] = sanitize_text_field($value);
            } elseif (is_array($value)) {
                $clean[$key] = $this->sanitize($value);
            } else {
                $clean[$key] = $value;
            }
        }
        return $clean;
    }
}
