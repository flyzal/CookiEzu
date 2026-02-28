<?php
/**
 * CookiEzu Settings – handles saving and sanitizing options.
 *
 * @package CookiEzu
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class CookiEzu_Settings {

    /**
     * Save settings from POST data.
     *
     * @return bool|WP_Error
     */
    public static function save() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return new WP_Error( 'forbidden', __( 'You do not have permission to do this.', 'cookiezu' ) );
        }

        if ( ! isset( $_POST['cookiezu_nonce'] ) || ! wp_verify_nonce( $_POST['cookiezu_nonce'], 'cookiezu_save_settings' ) ) {
            return new WP_Error( 'invalid_nonce', __( 'Security check failed.', 'cookiezu' ) );
        }

        $data = wp_unslash( $_POST['cookiezu'] ?? array() );

        $sanitized = array(
            'banner_title'           => sanitize_text_field( $data['banner_title'] ?? '' ),
            'banner_message'         => wp_kses_post( $data['banner_message'] ?? '' ),
            'accept_all_text'        => sanitize_text_field( $data['accept_all_text'] ?? '' ),
            'accept_necessary_text'  => sanitize_text_field( $data['accept_necessary_text'] ?? '' ),
            'customize_text'         => sanitize_text_field( $data['customize_text'] ?? '' ),
            'save_preferences_text'  => sanitize_text_field( $data['save_preferences_text'] ?? '' ),
            'privacy_policy_url'     => esc_url_raw( $data['privacy_policy_url'] ?? '' ),
            'privacy_policy_text'    => sanitize_text_field( $data['privacy_policy_text'] ?? '' ),
            'position'               => in_array( $data['position'] ?? '', array( 'bottom', 'top', 'bottom-left', 'bottom-right' ) ) ? $data['position'] : 'bottom',
            'layout'                 => in_array( $data['layout'] ?? '', array( 'bar', 'box', 'modal' ) ) ? $data['layout'] : 'bar',
            'theme'                  => in_array( $data['theme'] ?? '', array( 'light', 'dark', 'custom' ) ) ? $data['theme'] : 'light',
            'primary_color'          => sanitize_hex_color( $data['primary_color'] ?? '#C17B2F' ),
            'text_color'             => sanitize_hex_color( $data['text_color'] ?? '#1A1208' ),
            'bg_color'               => sanitize_hex_color( $data['bg_color'] ?? '#FEFCF8' ),
            'border_radius'          => absint( $data['border_radius'] ?? 10 ),
            'auto_accept_days'       => absint( $data['auto_accept_days'] ?? 0 ),
            'necessary_cookies'      => true,
            'analytics_cookies'      => ! empty( $data['analytics_cookies'] ),
            'marketing_cookies'      => ! empty( $data['marketing_cookies'] ),
            'functional_cookies'     => ! empty( $data['functional_cookies'] ),
            'show_cookie_table'      => ! empty( $data['show_cookie_table'] ),
            'record_consent'         => ! empty( $data['record_consent'] ),
            'consent_expiry_days'    => absint( $data['consent_expiry_days'] ?? 365 ),
            'gtm_id'                 => sanitize_text_field( $data['gtm_id'] ?? '' ),
            'ga_id'                  => sanitize_text_field( $data['ga_id'] ?? '' ),
            'custom_css'             => wp_strip_all_tags( $data['custom_css'] ?? '' ),
            // v1.2.0
            'test_mode'              => ! empty( $data['test_mode'] ),
            'escape_key_close'       => ! empty( $data['escape_key_close'] ),
            'reopen_position'        => in_array( $data['reopen_position'] ?? '', array( 'bottom-left', 'bottom-right' ) ) ? $data['reopen_position'] : 'bottom-left',
        );

        update_option( 'cookiezu_settings', $sanitized );

        return true;
    }
}
