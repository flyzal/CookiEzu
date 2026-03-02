<?php
/**
 * CookiEzu Installer – handles activation/deactivation.
 *
 * @package CookiEzu
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class CookiEzu_Installer {

    public static function activate() {
        self::create_tables();
        self::set_default_options();
        flush_rewrite_rules();
    }

    public static function deactivate() {
        flush_rewrite_rules();
    }

    private static function create_tables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();
        $table           = $wpdb->prefix . 'cookiezu_consent_log';

        $sql = "CREATE TABLE IF NOT EXISTS $table (
            id               BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            ip_address       VARCHAR(45)  NOT NULL DEFAULT '',
            user_agent       TEXT         NOT NULL,
            country_code     VARCHAR(2)   NOT NULL DEFAULT '',
            policy_version   VARCHAR(20)  NOT NULL DEFAULT '1',
            necessary        TINYINT(1)   NOT NULL DEFAULT 1,
            analytics        TINYINT(1)   NOT NULL DEFAULT 0,
            marketing        TINYINT(1)   NOT NULL DEFAULT 0,
            functional       TINYINT(1)   NOT NULL DEFAULT 0,
            consent_date     DATETIME     NOT NULL,
            PRIMARY KEY (id),
            KEY consent_date (consent_date),
            KEY country_code (country_code)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );

        /* v1.2.1 migration: add country_code if missing */
        $columns = $wpdb->get_col( "SHOW COLUMNS FROM $table", 0 );
        if ( ! in_array( 'country_code', $columns ) ) {
            $wpdb->query( "ALTER TABLE $table ADD COLUMN country_code VARCHAR(2) NOT NULL DEFAULT '' AFTER user_agent, ADD KEY country_code (country_code)" );
        }

        /* v1.3.0 migration: add policy_version if missing */
        if ( ! in_array( 'policy_version', $columns ) ) {
            $wpdb->query( "ALTER TABLE $table ADD COLUMN policy_version VARCHAR(20) NOT NULL DEFAULT '1' AFTER country_code" );
        }

        update_option( 'cookiezu_db_version', COOKIEZU_VERSION );
    }

    private static function set_default_options() {
        if ( ! get_option( 'cookiezu_settings' ) ) {
            add_option( 'cookiezu_settings', CookiEzu::$defaults );
        }
    }
}
