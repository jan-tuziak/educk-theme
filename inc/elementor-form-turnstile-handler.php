<?php

/**
 * A simple integration of Cloudflare Turnstile with Elementor Forms, following Elementor’s pattern for reCAPTCHA.
 *
 * Instructions:
 * 1. Add this file to your WordPress theme directory.
 * 2. Include the file in your theme's `functions.php` file using:
 *
 *    // For child themes:
 *    require_once get_stylesheet_directory() . '/elementor-form-turnstile-handler.php';
 *
 *    // For parent themes:
 *    require_once get_template_directory() . '/elementor-form-turnstile-handler.php';
 *
 * 3. Go to WordPress Dashboard > Elementor > Settings > Integrations > Cloudflare Turnstile
 * 4. Enter your Turnstile Site Key and Secret Key.
 * 5. Edit your Elementor form:
 *    - Add a new **Cloudflare Turnstile** field to your form (similar to adding a reCAPTCHA field).
 *    - Save the form.
 *
 *
 * @author @DavePodosyan
 * @version 1.0.0
 * @link https://gist.github.com/DavePodosyan/b4e6f0a261ce5c7ed3b30b0734d56291#file-elementor-form-turnstile-handler-php
 */

use Elementor\Settings;
use ElementorPro\Core\Utils;
use ElementorPro\Plugin;

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Integration with Cloudflare Turnstile
 */

class Turnstile_Handler
{
    const OPTION_NAME_SITE_KEY = 'elementor_pro_cf_turnstile_site_key';

    const OPTION_NAME_SECRET_KEY = 'elementor_pro_cf_turnstile_secret_key';

    protected static function get_turnstile_name()
    {
        return 'cf_turnstile';
    }

    public static function get_site_key()
    {
        return get_option(self::OPTION_NAME_SITE_KEY);
    }

    public static function get_secret_key()
    {
        return get_option(self::OPTION_NAME_SECRET_KEY);
    }

    public static function get_turnstile_type()
    {
        return 'managed';
    }

    public static function is_enabled()
    {
        return static::get_site_key() && static::get_secret_key();
    }

    public static function get_setup_message()
    {
        return esc_html__('To use Cloudflare Turnstile, you need to add the API Key and complete the setup process in Dashboard > Elementor > Settings > Integrations > Claudflare Turnstile.', 'elementor-pro');
    }

    public function register_admin_fields(Settings $settings)
    {
        $settings->add_section(Settings::TAB_INTEGRATIONS, static::get_turnstile_name(), [
            'label' => esc_html__('Cloudflare Turnstile', 'elementor-pro'),
            'callback' => function () {
                echo sprintf(
                    /* translators: 1: Link opening tag, 2: Link closing tag. */
                    esc_html__('%1$sCloudflare Turnstile%2$s is Cloudflare\'s CAPTCHA alternative solution where your users don\'t ever have to solve another puzzle to get to your website, no more stop lights and fire hydrants.', 'elementor-pro'),
                    '<a href="https://www.google.com/recaptcha/" target="_blank">',
                    '</a>'
                );
            },
            'fields' => [
                'pro_cf_turnstile_site_key' => [
                    'label' => esc_html__('Site Key', 'elementor-pro'),
                    'field_args' => [
                        'type' => 'text',
                    ],
                ],
                'pro_cf_turnstile_secret_key' => [
                    'label' => esc_html__('Secret Key', 'elementor-pro'),
                    'field_args' => [
                        'type' => 'text',
                    ],
                ],
            ],
        ]);
    }

    public function localize_settings($settings)
    {
        $settings = array_replace_recursive($settings, [
            'forms' => [
                static::get_turnstile_name() => [
                    'enabled' => static::is_enabled(),
                    'type' => static::get_turnstile_type(),
                    'site_key' => static::get_site_key(),
                    'setup_message' => static::get_setup_message(),
                ],
            ],
        ]);

        return $settings;
    }

    protected static function get_script_name()
    {
        return 'elementor-' . static::get_turnstile_name() . '-api';
    }

    public function register_scripts()
    {
        $script_name = static::get_script_name();
        $src = 'https://challenges.cloudflare.com/turnstile/v0/api.js';
        wp_register_script($script_name, $src, [], ELEMENTOR_PRO_VERSION, true);
    }

    public function enqueue_scripts()
    {
        if (Plugin::elementor()->preview->is_preview_mode()) {
            return;
        }
        $script_name = static::get_script_name();
        wp_enqueue_script($script_name);
    }

    /**
     * @param Form_Record  $record
     * @param Ajax_Handler $ajax_handler
     */
    public function validation($record, $ajax_handler)
    {
        $fields = $record->get_field([
            'type' => static::get_turnstile_name(),
        ]);

        if (empty($fields)) {
            return;
        }

        $field = current($fields);

        // PHPCS - response protected by recaptcha secret
        $recaptcha_response = Utils::_unstable_get_super_global_value($_POST, 'cf-turnstile-response'); // phpcs:ignore WordPress.Security.NonceVerification.Missing

        if (empty($recaptcha_response)) {
            $ajax_handler->add_error($field['id'], esc_html__('The Captcha field cannot be blank. Please enter a value.', 'elementor-pro'));

            return;
        }

        $recaptcha_errors = [
            'missing-input-secret' => esc_html__('The secret parameter is missing.', 'elementor-pro'),
            'invalid-input-secret' => esc_html__('The secret parameter is invalid or malformed.', 'elementor-pro'),
            'missing-input-response' => esc_html__('The response parameter is missing.', 'elementor-pro'),
            'invalid-input-response' => esc_html__('The response parameter is invalid or malformed.', 'elementor-pro'),
        ];

        $recaptcha_secret = static::get_secret_key();
        $client_ip = Utils::get_client_ip();

        $request = [
            'body' => [
                'secret' => $recaptcha_secret,
                'response' => $recaptcha_response,
                'remoteip' => $client_ip,
            ],
        ];

        $response = wp_remote_post('https://challenges.cloudflare.com/turnstile/v0/siteverify', $request);

        $response_code = wp_remote_retrieve_response_code($response);

        if (200 !== (int) $response_code) {
            /* translators: %d: Response code. */
            $ajax_handler->add_error($field['id'], sprintf(esc_html__('Can not connect to the Cloudflare Turnstile server (%d).', 'elementor-pro'), $response_code));

            return;
        }

        $body = wp_remote_retrieve_body($response);

        $result = json_decode($body, true);

        if (! $this->validate_result($result, $field)) {
            $message = esc_html__('Invalid form, Cloudflare Turnstile validation failed.', 'elementor-pro');

            if (isset($result['error-codes'])) {
                $result_errors = array_flip($result['error-codes']);

                foreach ($recaptcha_errors as $error_key => $error_desc) {
                    if (isset($result_errors[$error_key])) {
                        $message = $recaptcha_errors[$error_key];
                        break;
                    }
                }
            }

            $this->add_error($ajax_handler, $field, $message);
        }

        // If success - remove the field form list (don't send it in emails and etc )
        $record->remove_field($field['id']);
    }

    /**
     * @param Ajax_Handler $ajax_handler
     * @param $field
     * @param $message
     */
    protected function add_error($ajax_handler, $field, $message)
    {
        $ajax_handler->add_error($field['id'], $message);
    }

    protected function validate_result($result, $field)
    {
        if (! $result['success']) {
            return false;
        }

        return true;
    }

    /**
     * @param $item
     * @param $item_index
     * @param $widget Widget_Base
     */
    public function render_field($item, $item_index, $widget)
    {
        $recaptcha_html = '<div class="elementor-field" id="form-field-' . $item['custom_id'] . '">';

        $recaptcha_name = static::get_turnstile_name();

        if (static::is_enabled()) {
            $this->enqueue_scripts();
            $this->add_render_attributes($item, $item_index, $widget);
            $recaptcha_html .= '<div ' . $widget->get_render_attribute_string($recaptcha_name . $item_index) . '></div>';
        } elseif (current_user_can('manage_options')) {
            $recaptcha_html .= '<div class="elementor-alert elementor-alert-info">';
            $recaptcha_html .= static::get_setup_message();
            $recaptcha_html .= '</div>';
        }

        $recaptcha_html .= '</div>';

        // PHPCS - It's all escaped
        echo $recaptcha_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }

    /**
     * @param $item
     * @param $item_index
     * @param $widget Widget_Base
     */
    protected function add_render_attributes($item, $item_index, $widget)
    {
        $recaptcha_name = static::get_turnstile_name();

        $widget->add_render_attribute([
            $recaptcha_name . $item_index => [
                'class' => 'cf-turnstile',
                'data-sitekey' => static::get_site_key(),
                'data-type' => static::get_turnstile_type(),
            ],
        ]);

        $this->add_version_specific_render_attributes($item, $item_index, $widget);
    }

    /**
     * @param $item
     * @param $item_index
     * @param $widget Widget_Base
     */
    protected function add_version_specific_render_attributes($item, $item_index, $widget)
    {
        $recaptcha_name = static::get_turnstile_name();
        $widget->add_render_attribute($recaptcha_name . $item_index, [
            'data-theme' => 'light',
            'data-size' => 'flexible',
        ]);
    }

    public function add_field_type($field_types)
    {
        $field_types['cf_turnstile'] = esc_html__('Cloudflare Turnstile', 'elementor-pro');

        return $field_types;
    }

    public function filter_field_item($item)
    {
        if (static::get_turnstile_name() === $item['field_type']) {
            $item['field_label'] = false;
        }

        return $item;
    }

    public function __construct()
    {
        $this->register_scripts();

        add_filter('elementor_pro/forms/field_types', [$this, 'add_field_type']);
        add_action('elementor_pro/forms/render_field/' . static::get_turnstile_name(), [$this, 'render_field'], 10, 3);
        add_filter('elementor_pro/forms/render/item', [$this, 'filter_field_item']);
        add_filter('elementor_pro/editor/localize_settings', [$this, 'localize_settings']);

        if (static::is_enabled()) {
            add_action('elementor_pro/forms/validation', [$this, 'validation'], 10, 2);
            add_action('elementor/preview/enqueue_scripts', [$this, 'enqueue_scripts']);
        }

        if (is_admin()) {
            add_action('elementor/admin/after_create_settings/' . Settings::PAGE_ID, [$this, 'register_admin_fields']);
        }
    }
}

add_action('elementor/init', function () {
    new Turnstile_Handler();
});