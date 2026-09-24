<?php

if (! defined('ABSPATH')) {
    exit;
}

class VentureX_Forms
{
    public function __construct()
    {
        // Contact Form 7
        add_action('wpcf7_before_created_object', [$this, 'interceptCF7'], 10, 1);

        // Gravity Forms
        add_action('gform_after_submission', [$this, 'interceptGravityForms'], 10, 2);

        // Generic form handler (WP comment form)
        add_action('comment_post', [$this, 'interceptComment'], 10, 3);

        // WooCommerce order hook
        add_action('woocommerce_order_status_processing', [$this, 'interceptWooCommerce'], 10, 1);
        add_action('woocommerce_new_order', [$this, 'interceptWooCommerce'], 10, 1);
    }

    public function interceptCF7($contact_form)
    {
        $data = [
            'name' => isset($_POST['your-name']) ? sanitize_text_field($_POST['your-name']) : '',
            'email' => isset($_POST['your-email']) ? sanitize_email($_POST['your-email']) : '',
            'phone' => isset($_POST['your-phone']) ? sanitize_text_field($_POST['your-phone']) : '',
            'subject' => isset($_POST['your-subject']) ? sanitize_text_field($_POST['your-subject']) : '',
            'message' => isset($_POST['your-message']) ? sanitize_textarea_field($_POST['your-message']) : '',
            'source' => 'Contact Form 7',
        ];

        $this->sendLead($data);
    }

    public function interceptGravityForms($entry, $form)
    {
        $data = [
            'name' => $this->getGFValue($entry, 'Name'),
            'email' => $this->getGFValue($entry, 'Email'),
            'phone' => $this->getGFValue($entry, 'Phone'),
            'subject' => 'Gravity Forms: '.$form['title'],
            'message' => $this->getGFMessage($entry),
            'source' => 'Gravity Forms',
        ];

        $this->sendLead($data);
    }

    public function interceptComment($comment_ID, $comment_approved, $commentdata)
    {
        $data = [
            'name' => isset($commentdata['comment_author']) ? sanitize_text_field($commentdata['comment_author']) : '',
            'email' => isset($commentdata['comment_author_email']) ? sanitize_email($commentdata['comment_author_email']) : '',
            'subject' => isset($commentdata['comment_post_ID']) ? 'Comment on: '.get_the_title($commentdata['comment_post_ID']) : 'Website Comment',
            'message' => isset($commentdata['comment_content']) ? sanitize_textarea_field($commentdata['comment_content']) : '',
            'source' => 'WordPress Comment',
        ];

        $this->sendLead($data);
    }

    public function interceptWooCommerce($order_id)
    {
        if (! class_exists('WooCommerce')) {
            return;
        }

        $order = wc_get_order($order_id);
        if (! $order) {
            return;
        }

        $data = [
            'name' => $order->get_billing_first_name().' '.$order->get_billing_last_name(),
            'email' => $order->get_billing_email(),
            'phone' => $order->get_billing_phone(),
            'address' => $order->get_billing_address_1(),
            'city' => $order->get_billing_city(),
            'state' => $order->get_billing_state(),
            'postcode' => $order->get_billing_postcode(),
            'country' => $order->get_billing_country(),
            'total' => $order->get_total(),
            'items' => $this->getOrderItems($order),
            'source' => 'WooCommerce Order #'.$order_id,
        ];

        $api = new VentureX_API;
        $api->createOrder($data);
    }

    private function sendLead($data)
    {
        $required = ['name', 'email'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return;
            }
        }

        $api = new VentureX_API;
        $api->createLead($data);
    }

    private function getGFValue($entry, $label)
    {
        foreach ($entry['fields'] as $field) {
            if (strtolower($field->label) === strtolower($label)) {
                return $field->value;
            }
        }

        return '';
    }

    private function getGFMessage($entry)
    {
        $parts = [];
        foreach ($entry['fields'] as $field) {
            if (! empty($field->value) && ! in_array(strtolower($field->label), ['name', 'email', 'phone'])) {
                $parts[] = $field->label.': '.$field->value;
            }
        }

        return implode("\n", $parts);
    }

    private function getOrderItems($order)
    {
        $items = [];
        foreach ($order->get_items() as $item) {
            $items[] = [
                'name' => $item->get_name(),
                'quantity' => $item->get_quantity(),
                'total' => $item->get_total(),
            ];
        }

        return $items;
    }
}
