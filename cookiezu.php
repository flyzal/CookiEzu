<?php
/**
 * Plugin Name:       CookiEzu
 * Plugin URI:        https://flyzal.github.io/CookiEzu/
 * Description:       A lightweight, GDPR-compliant cookie consent manager for WordPress. Open source and free forever.
 * Version:           1.2.0
 * Author:            flyzal
 * Author URI:        https://github.com/flyzal
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       cookiezu
 * Domain Path:       /languages
 * Requires at least: 5.5
 * Requires PHP:      7.4
 *
 * @package CookiEzu
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Plugin constants
define( 'COOKIEZU_VERSION', '1.2.0' );
define( 'COOKIEZU_PLUGIN_FILE', __FILE__ );
define( 'COOKIEZU_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'COOKIEZU_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'COOKIEZU_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

// Load core files
require_once COOKIEZU_PLUGIN_DIR . 'includes/class-cookiezu.php';
require_once COOKIEZU_PLUGIN_DIR . 'includes/class-cookiezu-installer.php';
require_once COOKIEZU_PLUGIN_DIR . 'includes/class-cookiezu-settings.php';

/**
 * Main plugin instance.
 *
 * @return CookiEzu
 */
function cookiezu() {
    return CookiEzu::instance();
}

// Boot plugin
cookiezu();

// Activation / Deactivation hooks
register_activation_hook( __FILE__, array( 'CookiEzu_Installer', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'CookiEzu_Installer', 'deactivate' ) );
