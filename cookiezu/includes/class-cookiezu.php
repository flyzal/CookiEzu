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

    /**
     * Singleton instance.
     *
     * @var CookiEzu
     */
    private static $instance = null;

    /**
     * Default settings.
     *
     * @var array
     */
    public static $defaults = array(
        'banner_title'           => 'We value your privacy 🍪',
        'banner_message'         => 'We use cookies to enhance your browsing experience, serve personalized ads or content, and analyze our traffic. By clicking "Accept All", you consent to our use of cookies.',
        'accept_all_text'        => 'Accept All',
        'accept_necessary_text'  => 'Necessary Only',
        'customize_text'         => 'Customize',
        'save_preferences_text'  => 'Save Preferences',
        'privacy_policy_url'     => '',
        'privacy_policy_text'    => 'Privacy Policy',
        'position'               => 'bottom',
        'layout'                 => 'bar',
        'theme'                  => 'light',
        'primary_color'          => '#3b82f6',
        'text_color'             => '#1f2937',
        'bg_color'               => '#ffffff',
        'border_radius'          => '8',
        'auto_accept_days'       => '0',
        'necessary_cookies'      => true,
        'analytics_cookies'      => true,
        'marketing_cookies'      => true,
        'functional_cookies'     => true,
        'show_cookie_table'      => true,
        'record_consent'         => true,
        'consent_expiry_days'    => '365',
        'gtm_id'                 => '',
        'ga_id'                  => '',
        'custom_css'             => '',
    );

    /**
     * Get singleton instance.
     */
    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor.
     */
    private function __construct() {
        $this->init_hooks();
    }

    /**
     * Init hooks.
     */
    private function init_hooks() {
        add_action( 'init', array( $this, 'load_textdomain' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_public_assets' ) );
        add_action( 'wp_footer', array( $this, 'render_banner' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
        add_action( 'admin_menu', array( $this, 'admin_menu' ) );
        add_action( 'wp_ajax_cookiezu_save_consent', array( $this, 'ajax_save_consent' ) );
        add_action( 'wp_ajax_nopriv_cookiezu_save_consent', array( $this, 'ajax_save_consent' ) );
        add_action( 'wp_ajax_cookiezu_get_log', array( $this, 'ajax_get_log' ) );
        add_filter( 'plugin_action_links_' . COOKIEZU_PLUGIN_BASENAME, array( $this, 'plugin_action_links' ) );
    }

    /**
     * Load plugin text domain.
     */
    public function load_textdomain() {
        load_plugin_textdomain( 'cookiezu', false, dirname( COOKIEZU_PLUGIN_BASENAME ) . '/languages' );
    }

    /**
     * Enqueue public-facing scripts and styles.
     */
    public function enqueue_public_assets() {
        $options = $this->get_options();

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
            'ajaxUrl'           => admin_url( 'admin-ajax.php' ),
            'nonce'             => wp_create_nonce( 'cookiezu_consent' ),
            'options'           => $options,
            'cookieName'        => 'cookiezu_consent',
            'expiryDays'        => intval( $options['consent_expiry_days'] ),
            'version'           => COOKIEZU_VERSION,
            'strings'           => array(
                'necessary'   => __( 'Necessary', 'cookiezu' ),
                'analytics'   => __( 'Analytics', 'cookiezu' ),
                'marketing'   => __( 'Marketing', 'cookiezu' ),
                'functional'  => __( 'Functional', 'cookiezu' ),
            ),
        ) );
    }

    /**
     * Enqueue admin scripts and styles.
     */
    public function enqueue_admin_assets( $hook ) {
        if ( strpos( $hook, 'cookiezu' ) === false ) {
            return;
        }

        wp_enqueue_style(
            'cookiezu-admin',
            COOKIEZU_PLUGIN_URL . 'admin/css/cookiezu-admin.css',
            array(),
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

    /**
     * Register admin menu.
     */
    public function admin_menu() {
        add_menu_page(
            __( 'CookiEzu', 'cookiezu' ),
            __( 'CookiEzu', 'cookiezu' ),
            'manage_options',
            'cookiezu',
            array( $this, 'admin_page' ),
            'dashicons-privacy',
            81
        );

        add_submenu_page(
            'cookiezu',
            __( 'Settings', 'cookiezu' ),
            __( 'Settings', 'cookiezu' ),
            'manage_options',
            'cookiezu',
            array( $this, 'admin_page' )
        );

        add_submenu_page(
            'cookiezu',
            __( 'Consent Log', 'cookiezu' ),
            __( 'Consent Log', 'cookiezu' ),
            'manage_options',
            'cookiezu-log',
            array( $this, 'log_page' )
        );
    }

    /**
     * Render the admin settings page.
     */
    public function admin_page() {
        require_once COOKIEZU_PLUGIN_DIR . 'admin/views/settings-page.php';
    }

    /**
     * Render the consent log page.
     */
    public function log_page() {
        require_once COOKIEZU_PLUGIN_DIR . 'admin/views/log-page.php';
    }

    /**
     * Render cookie banner in footer.
     */
    public function render_banner() {
        $options = $this->get_options();
        require COOKIEZU_PLUGIN_DIR . 'public/views/banner.php';
    }

    /**
     * AJAX: Save consent record.
     */
    public function ajax_save_consent() {
        check_ajax_referer( 'cookiezu_consent', 'nonce' );

        $options = $this->get_options();
        if ( empty( $options['record_consent'] ) ) {
            wp_send_json_success();
        }

        global $wpdb;
        $table = $wpdb->prefix . 'cookiezu_consent_log';

        $wpdb->insert( $table, array(
            'ip_address'     => sanitize_text_field( $_SERVER['REMOTE_ADDR'] ?? '' ),
            'user_agent'     => sanitize_text_field( $_SERVER['HTTP_USER_AGENT'] ?? '' ),
            'necessary'      => isset( $_POST['necessary'] ) ? 1 : 0,
            'analytics'      => isset( $_POST['analytics'] ) ? 1 : 0,
            'marketing'      => isset( $_POST['marketing'] ) ? 1 : 0,
            'functional'     => isset( $_POST['functional'] ) ? 1 : 0,
            'consent_date'   => current_time( 'mysql' ),
        ) );

        wp_send_json_success();
    }

    /**
     * AJAX: Get consent log.
     */
    public function ajax_get_log() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( -1 );
        }

        global $wpdb;
        $table = $wpdb->prefix . 'cookiezu_consent_log';
        $rows  = $wpdb->get_results( "SELECT * FROM $table ORDER BY consent_date DESC LIMIT 200" );

        wp_send_json_success( $rows );
    }

    /**
     * Add plugin action links.
     */
    public function plugin_action_links( $links ) {
        $links[] = '<a href="' . admin_url( 'admin.php?page=cookiezu' ) . '">' . __( 'Settings', 'cookiezu' ) . '</a>';
        $links[] = '<a href="https://github.com/cookiezu/cookiezu" target="_blank">' . __( 'GitHub', 'cookiezu' ) . '</a>';
        return $links;
    }

    /**
     * Get plugin options merged with defaults.
     *
     * @return array
     */
    public function get_options() {
        $saved = get_option( 'cookiezu_settings', array() );
        return wp_parse_args( $saved, self::$defaults );
    }
}
