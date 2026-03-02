<?php
/**
 * Main CookiEzu class.
 *
 * @package CookiEzu
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class CookiEzu {

    private static $instance = null;

    public static $defaults = array(
        'banner_title'             => 'We value your privacy 🍪',
        'banner_message'           => 'We use cookies to enhance your browsing experience, serve personalized ads or content, and analyze our traffic. By clicking "Accept All", you consent to our use of cookies.',
        'accept_all_text'          => 'Accept All',
        'accept_necessary_text'    => 'Necessary Only',
        'customize_text'           => 'Customize',
        'save_preferences_text'    => 'Save Preferences',
        'privacy_policy_url'       => '',
        'privacy_policy_text'      => 'Privacy Policy',
        'position'                 => 'bottom',
        'layout'                   => 'bar',
        'theme'                    => 'light',
        'primary_color'            => '#C17B2F',
        'text_color'               => '#1A1208',
        'bg_color'                 => '#FEFCF8',
        'border_radius'            => '8',
        'auto_accept_days'         => '0',
        'necessary_cookies'        => true,
        'analytics_cookies'        => true,
        'marketing_cookies'        => true,
        'functional_cookies'       => true,
        'show_cookie_table'        => true,
        'record_consent'           => true,
        'consent_expiry_days'      => '365',
        'gtm_id'                   => '',
        'ga_id'                    => '',
        'custom_css'               => '',
        // v1.2.0
        'test_mode'                => false,
        'escape_key_close'         => true,
        'reopen_position'          => 'bottom-left',
        // v1.3.0
        'banner_language'          => 'en',
        'policy_version'           => '1',
        'extended_disclosure'      => '',
        'data_processing_location' => '',
    );

    /**
     * Country → compliance tier map.
     * Tiers: gdpr, pdpl, pdpa, pdpo, other, none
     */
    public static $compliance_tiers = array(
        // EU/EEA — GDPR
        'AT'=>'gdpr','BE'=>'gdpr','BG'=>'gdpr','CY'=>'gdpr','CZ'=>'gdpr',
        'DE'=>'gdpr','DK'=>'gdpr','EE'=>'gdpr','ES'=>'gdpr','FI'=>'gdpr',
        'FR'=>'gdpr','GR'=>'gdpr','HR'=>'gdpr','HU'=>'gdpr','IE'=>'gdpr',
        'IT'=>'gdpr','LT'=>'gdpr','LU'=>'gdpr','LV'=>'gdpr','MT'=>'gdpr',
        'NL'=>'gdpr','PL'=>'gdpr','PT'=>'gdpr','RO'=>'gdpr','SE'=>'gdpr',
        'SI'=>'gdpr','SK'=>'gdpr','GB'=>'gdpr','NO'=>'gdpr','IS'=>'gdpr','LI'=>'gdpr',
        // GCC — PDPL
        'SA'=>'pdpl','AE'=>'pdpl','QA'=>'pdpl','BH'=>'pdpl','OM'=>'pdpl','KW'=>'pdpl',
        // SEA — PDPA
        'MY'=>'pdpa','TH'=>'pdpa','SG'=>'pdpa','PH'=>'pdpa','ID'=>'pdpa',
        // Brunei — PDPO 2025
        'BN'=>'pdpo',
        // Other regulated
        'US'=>'other','CA'=>'other','AU'=>'other','NZ'=>'other',
        'JP'=>'other','KR'=>'other','BR'=>'other','IN'=>'other',
    );

    public static $tier_meta = array(
        'gdpr'  => array( 'label' => 'GDPR',       'color' => '#2563EB' ),
        'pdpl'  => array( 'label' => 'PDPL (GCC)',  'color' => '#7C3AED' ),
        'pdpa'  => array( 'label' => 'PDPA',        'color' => '#059669' ),
        'pdpo'  => array( 'label' => 'PDPO (BN)',   'color' => '#C17B2F' ),
        'other' => array( 'label' => 'Regulated',   'color' => '#0EA5E9' ),
        'none'  => array( 'label' => '—',           'color' => '#9CA3AF' ),
    );

    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->init_hooks();
    }

    private function init_hooks() {
        add_action( 'init',                    array( $this, 'load_textdomain' ) );
        add_action( 'init',                    array( $this, 'maybe_upgrade_db' ) );
        add_action( 'wp_enqueue_scripts',      array( $this, 'enqueue_public_assets' ) );
        add_action( 'wp_footer',               array( $this, 'render_banner' ) );
        add_action( 'admin_enqueue_scripts',   array( $this, 'enqueue_admin_assets' ) );
        add_action( 'admin_menu',              array( $this, 'admin_menu' ) );
        add_action( 'admin_notices',           array( $this, 'compliance_admin_notices' ) );
        add_filter( 'admin_body_class',        array( $this, 'admin_body_class' ) );
        add_action( 'wp_ajax_cookiezu_save_consent',        array( $this, 'ajax_save_consent' ) );
        add_action( 'wp_ajax_nopriv_cookiezu_save_consent', array( $this, 'ajax_save_consent' ) );
        add_action( 'wp_ajax_cookiezu_get_log',             array( $this, 'ajax_get_log' ) );
        add_filter( 'plugin_action_links_' . COOKIEZU_PLUGIN_BASENAME, array( $this, 'plugin_action_links' ) );
    }

    public function load_textdomain() {
        load_plugin_textdomain( 'cookiezu', false, dirname( COOKIEZU_PLUGIN_BASENAME ) . '/languages' );
    }

    /**
     * Load translated strings for the selected banner language.
     * Falls back to English for missing keys.
     */
    public static function get_lang_strings( $lang = 'en' ) {
        $allowed = array( 'en', 'ar', 'ms' );
        $lang    = in_array( $lang, $allowed, true ) ? $lang : 'en';
        $file    = COOKIEZU_PLUGIN_DIR . 'languages/' . $lang . '.php';
        $strings = file_exists( $file ) ? require $file : array();
        if ( $lang !== 'en' ) {
            $en_file = COOKIEZU_PLUGIN_DIR . 'languages/en.php';
            $en      = file_exists( $en_file ) ? require $en_file : array();
            $strings = array_merge( $en, $strings );
        }
        return (array) $strings;
    }

    public function enqueue_public_assets() {
        $options = $this->get_options();
        $lang    = $options['banner_language'] ?? 'en';
        $strings = self::get_lang_strings( $lang );
        $is_rtl  = ( $lang === 'ar' );

        wp_enqueue_style(
            'cookiezu-public',
            COOKIEZU_PLUGIN_URL . 'public/css/cookiezu-public.css',
            array(),
            COOKIEZU_VERSION
        );

        wp_enqueue_script(
            'cookiezu-public',
            COOKIEZU_PLUGIN_URL . 'public/js/cookiezu-public.js',
            array(),
            COOKIEZU_VERSION,
            true
        );

        wp_localize_script( 'cookiezu-public', 'cookiezuSettings', array(
            'ajaxUrl'         => admin_url( 'admin-ajax.php' ),
            'nonce'           => wp_create_nonce( 'cookiezu_consent' ),
            'options'         => $options,
            'cookieName'      => 'cookiezu_consent',
            'expiryDays'      => intval( $options['consent_expiry_days'] ),
            'version'         => COOKIEZU_VERSION,
            'policyVersion'   => sanitize_text_field( $options['policy_version'] ?? '1' ),
            'testMode'        => ! empty( $options['test_mode'] ) && current_user_can( 'manage_options' ),
            'escapeKeyClose'  => ! empty( $options['escape_key_close'] ),
            'isRtl'           => $is_rtl,
            'strings'         => array(
                'necessary'   => $strings['cat_necessary']  ?? 'Necessary',
                'analytics'   => $strings['cat_analytics']  ?? 'Analytics',
                'marketing'   => $strings['cat_marketing']  ?? 'Marketing',
                'functional'  => $strings['cat_functional'] ?? 'Functional',
                'back'        => $strings['back']           ?? '← Back',
            ),
        ) );
    }

    public function admin_body_class( $classes ) {
        $screen = get_current_screen();
        if ( $screen && strpos( $screen->id, 'cookiezu' ) !== false ) {
            $classes .= ' cookiezu-admin-page';
        }
        return $classes;
    }

    public function enqueue_admin_assets( $hook ) {
        if ( strpos( $hook, 'cookiezu' ) === false ) return;

        wp_enqueue_style(
            'cookiezu-fonts',
            'https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700&display=swap',
            array(), null
        );
        wp_enqueue_style(
            'cookiezu-admin',
            COOKIEZU_PLUGIN_URL . 'admin/css/cookiezu-admin.css',
            array( 'cookiezu-fonts' ),
            COOKIEZU_VERSION
        );
        wp_enqueue_script(
            'cookiezu-admin',
            COOKIEZU_PLUGIN_URL . 'admin/js/cookiezu-admin.js',
            array( 'jquery', 'wp-color-picker' ),
            COOKIEZU_VERSION,
            true
        );
        wp_enqueue_style( 'wp-color-picker' );
    }

    public function admin_menu() {
        add_menu_page(
            __( 'CookiEzu', 'cookiezu' ),
            __( 'CookiEzu', 'cookiezu' ),
            'manage_options', 'cookiezu',
            array( $this, 'admin_page' ),
            'dashicons-privacy', 81
        );
        add_submenu_page(
            'cookiezu', __( 'Settings', 'cookiezu' ), __( 'Settings', 'cookiezu' ),
            'manage_options', 'cookiezu', array( $this, 'admin_page' )
        );
        add_submenu_page(
            'cookiezu', __( 'Consent Log', 'cookiezu' ), __( 'Consent Log', 'cookiezu' ),
            'manage_options', 'cookiezu-log', array( $this, 'log_page' )
        );
    }

    /**
     * Show contextual compliance notices when high-attention country codes
     * appear in the log and relevant settings are missing.
     */
    public function compliance_admin_notices() {
        $screen = get_current_screen();
        if ( ! $screen || strpos( $screen->id, 'cookiezu' ) === false ) return;

        $options = $this->get_options();

        global $wpdb;
        $table = $wpdb->prefix . 'cookiezu_consent_log';
        // Check table exists before querying
        if ( $wpdb->get_var( "SHOW TABLES LIKE '$table'" ) !== $table ) return;

        $codes   = $wpdb->get_col( "SELECT DISTINCT country_code FROM $table WHERE country_code != '' LIMIT 500" );
        if ( empty( $codes ) ) return;

        $has_bn  = in_array( 'BN', $codes, true );
        $gcc     = array( 'SA', 'AE', 'QA', 'BH', 'OM', 'KW' );
        $has_gcc = ! empty( array_intersect( $codes, $gcc ) );

        if ( $has_bn && empty( $options['data_processing_location'] ) ) {
            echo '<div class="notice notice-warning is-dismissible"><p>'
                . '<strong>🇧🇳 CookiEzu — Brunei PDPO 2025:</strong> '
                . 'Visitors from Brunei Darussalam detected. The PDPO 2025 is now in effect. Please set your '
                . '<a href="' . esc_url( admin_url( 'admin.php?page=cookiezu#tab-advanced' ) ) . '">Data Processing Location</a>'
                . ' and ensure your Privacy Policy covers data collection purpose, storage location, and third-party sharing.'
                . '</p></div>';
        }

        if ( $has_gcc && empty( $options['data_processing_location'] ) ) {
            $gcc_found = implode( ', ', array_intersect( $codes, $gcc ) );
            echo '<div class="notice notice-warning is-dismissible"><p>'
                . '<strong>🌙 CookiEzu — GCC PDPL:</strong> '
                . 'Visitors from GCC countries (' . esc_html( $gcc_found ) . ') detected. '
                . 'Saudi PDPL and related laws require explicit disclosure of data processing location. '
                . 'Set your <a href="' . esc_url( admin_url( 'admin.php?page=cookiezu#tab-advanced' ) ) . '">Data Processing Location</a>'
                . ' and consider enabling <strong>Arabic</strong> in the '
                . '<a href="' . esc_url( admin_url( 'admin.php?page=cookiezu#tab-content' ) ) . '">Content tab</a>.'
                . '</p></div>';
        }
    }

    public function admin_page() {
        require_once COOKIEZU_PLUGIN_DIR . 'admin/views/settings-page.php';
    }

    public function log_page() {
        require_once COOKIEZU_PLUGIN_DIR . 'admin/views/log-page.php';
    }

    public function render_banner() {
        $options = apply_filters( 'cookiezu_options', $this->get_options() );
        do_action( 'cookiezu_before_banner', $options );
        ob_start();
        require COOKIEZU_PLUGIN_DIR . 'public/views/banner.php';
        $banner_html = ob_get_clean();
        echo apply_filters( 'cookiezu_banner_html', $banner_html, $options );
        do_action( 'cookiezu_after_banner', $options );
    }

    public function maybe_upgrade_db() {
        if ( get_option( 'cookiezu_db_version' ) !== COOKIEZU_VERSION ) {
            CookiEzu_Installer::activate();
        }
    }

    private function detect_country() {
        $ip = sanitize_text_field( $_SERVER['REMOTE_ADDR'] ?? '' );
        if ( ! $ip || $ip === '127.0.0.1' || $ip === '::1' ) return '';

        if ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
            $parts = explode( ',', $_SERVER['HTTP_X_FORWARDED_FOR'] );
            $ip    = trim( $parts[0] );
        }

        if ( ! empty( $_SERVER['HTTP_CF_IPCOUNTRY'] ) ) {
            $code = strtoupper( substr( sanitize_text_field( $_SERVER['HTTP_CF_IPCOUNTRY'] ), 0, 2 ) );
            if ( preg_match( '/^[A-Z]{2}$/', $code ) ) return $code;
        }

        $transient_key = 'cz_country_' . md5( $ip );
        $cached = get_transient( $transient_key );
        if ( false !== $cached ) return $cached;

        $response = wp_remote_get(
            'http://www.geoplugin.net/json.gp?ip=' . rawurlencode( $ip ),
            array( 'timeout' => 3, 'sslverify' => false )
        );

        if ( ! is_wp_error( $response ) ) {
            $body = json_decode( wp_remote_retrieve_body( $response ), true );
            $code = strtoupper( $body['geoplugin_countryCode'] ?? '' );
            if ( preg_match( '/^[A-Z]{2}$/', $code ) ) {
                set_transient( $transient_key, $code, DAY_IN_SECONDS );
                return $code;
            }
        }

        return '';
    }

    public function ajax_save_consent() {
        check_ajax_referer( 'cookiezu_consent', 'nonce' );

        $options = $this->get_options();
        if ( empty( $options['record_consent'] ) ) {
            wp_send_json_success();
        }

        global $wpdb;
        $table = $wpdb->prefix . 'cookiezu_consent_log';

        $wpdb->insert( $table, array(
            'ip_address'      => sanitize_text_field( $_SERVER['REMOTE_ADDR'] ?? '' ),
            'user_agent'      => sanitize_text_field( $_SERVER['HTTP_USER_AGENT'] ?? '' ),
            'country_code'    => $this->detect_country(),
            'policy_version'  => sanitize_text_field( $_POST['policy_version'] ?? $options['policy_version'] ?? '1' ),
            'necessary'       => isset( $_POST['necessary'] )  ? 1 : 0,
            'analytics'       => isset( $_POST['analytics'] )  ? 1 : 0,
            'marketing'       => isset( $_POST['marketing'] )  ? 1 : 0,
            'functional'      => isset( $_POST['functional'] ) ? 1 : 0,
            'consent_date'    => current_time( 'mysql' ),
        ) );

        wp_send_json_success();
    }

    public function ajax_get_log() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( -1 );

        global $wpdb;
        $table = $wpdb->prefix . 'cookiezu_consent_log';
        $rows  = $wpdb->get_results( "SELECT * FROM $table ORDER BY consent_date DESC LIMIT 200" );
        wp_send_json_success( $rows );
    }

    public function plugin_action_links( $links ) {
        $links[] = '<a href="' . admin_url( 'admin.php?page=cookiezu' ) . '">' . __( 'Settings', 'cookiezu' ) . '</a>';
        $links[] = '<a href="https://flyzal.github.io/CookiEzu/docs" target="_blank">' . __( 'Docs', 'cookiezu' ) . '</a>';
        $links[] = '<a href="https://github.com/flyzal/CookiEzu" target="_blank">' . __( 'GitHub', 'cookiezu' ) . '</a>';
        return $links;
    }

    public function get_options() {
        $saved = get_option( 'cookiezu_settings', array() );
        if ( is_array( $saved ) ) {
            $saved = array_map( function( $v ) {
                return is_string( $v ) ? wp_unslash( $v ) : $v;
            }, $saved );
        }
        return wp_parse_args( $saved, self::$defaults );
    }
}
