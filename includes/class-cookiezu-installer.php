<?php
/**
 * CookiEzu Installer – handles activation/deactivation.
 *
 * @package CookiEzu
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class CookiEzu_Installer {

    /**
     * Run on plugin activation.
     */
    public static function activate() {
        self::create_tables();
        self::set_default_options();
        flush_rewrite_rules();
    }

    /**
     * Run on plugin deactivation.
     */
    public static function deactivate() {
        flush_rewrite_rules();
    }

    /**
     * Create database tables.
     */
    private static function create_tables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();
        $table           = $wpdb->prefix . 'cookiezu_consent_log';

        $sql = "CREATE TABLE IF NOT EXISTS $table (
            id            BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            ip_address    VARCHAR(45)  NOT NULL DEFAULT '',
            user_agent    TEXT         NOT NULL,
            necessary     TINYINT(1)   NOT NULL DEFAULT 1,
            analytics     TINYINT(1)   NOT NULL DEFAULT 0,
            marketing     TINYINT(1)   NOT NULL DEFAULT 0,
            functional    TINYINT(1)   NOT NULL DEFAULT 0,
            consent_date  DATETIME     NOT NULL,
            PRIMARY KEY (id),
            KEY consent_date (consent_date)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );

        update_option( 'cookiezu_db_version', COOKIEZU_VERSION );
    }

    /**
     * Set default options on first activation.
     */
    private static function set_default_options() {
        if ( ! get_option( 'cookiezu_settings' ) ) {
            add_option( 'cookiezu_settings', CookiEzu::$defaults );
        }
    }
}
