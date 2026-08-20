<?php if (!defined('ABSPATH')) exit; ?>
<div class="venturex-lead-form">
    <form id="venturex-lead-form" method="post">
        <p>
            <label for="vx_name">Full Name *</label>
            <input type="text" id="vx_name" name="vx_name" required placeholder="Your name">
        </p>
        <p>
            <label for="vx_email">Email Address *</label>
            <input type="email" id="vx_email" name="vx_email" required placeholder="you@example.com">
        </p>
        <p>
            <label for="vx_phone">Phone Number</label>
            <input type="tel" id="vx_phone" name="vx_phone" placeholder="(555) 123-4567">
        </p>
        <p>
            <label for="vx_company">Company</label>
            <input type="text" id="vx_company" name="vx_company" placeholder="Your company name">
        </p>
        <p>
            <label for="vx_message">Message</label>
            <textarea id="vx_message" name="vx_message" rows="4" placeholder="How can we help you?"></textarea>
        </p>
        <p>
            <button type="submit">Send Message</button>
        </p>
        <div class="venturex-form-message" style="display:none;"></div>
    </form>
</div>
