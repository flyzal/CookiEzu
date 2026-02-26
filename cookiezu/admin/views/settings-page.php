<?php
/**
 * Admin settings page view.
 *
 * @package CookiEzu
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Handle form save
$saved   = false;
$error   = null;
if ( isset( $_POST['cookiezu_nonce'] ) ) {
    $result = CookiEzu_Settings::save();
    if ( is_wp_error( $result ) ) {
        $error = $result->get_error_message();
    } else {
        $saved = true;
    }
}

$options = cookiezu()->get_options();
?>
<div class="wrap cookiezu-wrap">
    <div class="cookiezu-header">
        <div class="cookiezu-header-brand">
            <div class="cookiezu-logo-icon">🍪</div>
            <h1>Cooki<span>Ezu</span></h1>
        </div>
        <p class="cookiezu-tagline"><?php esc_html_e( 'Lightweight, GDPR-compliant cookie consent — open source & free forever.', 'cookiezu' ); ?></p>
        <div class="cookiezu-header-badges">
            <span class="cookiezu-badge cookiezu-badge-gpl">✓ GPL v2</span>
            <a class="cookiezu-badge cookiezu-badge-github" href="https://github.com/flyzal/CookiEzu" target="_blank">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.37 0 0 5.37 0 12c0 5.31 3.435 9.795 8.205 11.385.6.105.825-.255.825-.57 0-.285-.015-1.23-.015-2.235-3.015.555-3.795-.735-4.035-1.41-.135-.345-.72-1.41-1.23-1.695-.42-.225-1.02-.78-.015-.795.945-.015 1.62.87 1.845 1.23 1.08 1.815 2.805 1.305 3.495.99.105-.78.42-1.305.765-1.605-2.67-.3-5.46-1.335-5.46-5.925 0-1.305.465-2.385 1.23-3.225-.12-.3-.54-1.53.12-3.18 0 0 1.005-.315 3.3 1.23.96-.27 1.98-.405 3-.405s2.04.135 3 .405c2.295-1.56 3.3-1.23 3.3-1.23.66 1.65.24 2.88.12 3.18.765.84 1.23 1.905 1.23 3.225 0 4.605-2.805 5.625-5.475 5.925.435.375.81 1.095.81 2.22 0 1.605-.015 2.895-.015 3.3 0 .315.225.69.825.57A12.02 12.02 0 0024 12c0-6.63-5.37-12-12-12z"/></svg>
                ⭐ Star on GitHub
            </a>
        </div>
    </div>

    <?php if ( $saved ) : ?>
        <div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Settings saved successfully!', 'cookiezu' ); ?></p></div>
    <?php endif; ?>
    <?php if ( $error ) : ?>
        <div class="notice notice-error"><p><?php echo esc_html( $error ); ?></p></div>
    <?php endif; ?>

    <form method="post" action="">
        <?php wp_nonce_field( 'cookiezu_save_settings', 'cookiezu_nonce' ); ?>

        <div class="cookiezu-tabs">
            <button type="button" class="cookiezu-tab active" data-tab="content"><?php esc_html_e( 'Content', 'cookiezu' ); ?></button>
            <button type="button" class="cookiezu-tab" data-tab="design"><?php esc_html_e( 'Design', 'cookiezu' ); ?></button>
            <button type="button" class="cookiezu-tab" data-tab="categories"><?php esc_html_e( 'Categories', 'cookiezu' ); ?></button>
            <button type="button" class="cookiezu-tab" data-tab="integrations"><?php esc_html_e( 'Integrations', 'cookiezu' ); ?></button>
            <button type="button" class="cookiezu-tab" data-tab="advanced"><?php esc_html_e( 'Advanced', 'cookiezu' ); ?></button>
        </div>

        <!-- CONTENT TAB -->
        <div class="cookiezu-tab-content active" id="tab-content">
            <div class="cookiezu-card">
                <h2><?php esc_html_e( 'Banner Text', 'cookiezu' ); ?></h2>
                <table class="form-table">
                    <tr>
                        <th><label for="banner_title"><?php esc_html_e( 'Banner Title', 'cookiezu' ); ?></label></th>
                        <td><input type="text" id="banner_title" name="cookiezu[banner_title]" value="<?php echo esc_attr( $options['banner_title'] ); ?>" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th><label for="banner_message"><?php esc_html_e( 'Banner Message', 'cookiezu' ); ?></label></th>
                        <td><textarea id="banner_message" name="cookiezu[banner_message]" rows="4" class="large-text"><?php echo esc_textarea( $options['banner_message'] ); ?></textarea></td>
                    </tr>
                    <tr>
                        <th><label for="accept_all_text"><?php esc_html_e( 'Accept All Button', 'cookiezu' ); ?></label></th>
                        <td><input type="text" id="accept_all_text" name="cookiezu[accept_all_text]" value="<?php echo esc_attr( $options['accept_all_text'] ); ?>" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th><label for="accept_necessary_text"><?php esc_html_e( 'Necessary Only Button', 'cookiezu' ); ?></label></th>
                        <td><input type="text" id="accept_necessary_text" name="cookiezu[accept_necessary_text]" value="<?php echo esc_attr( $options['accept_necessary_text'] ); ?>" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th><label for="customize_text"><?php esc_html_e( 'Customize Button', 'cookiezu' ); ?></label></th>
                        <td><input type="text" id="customize_text" name="cookiezu[customize_text]" value="<?php echo esc_attr( $options['customize_text'] ); ?>" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th><label for="save_preferences_text"><?php esc_html_e( 'Save Preferences Button', 'cookiezu' ); ?></label></th>
                        <td><input type="text" id="save_preferences_text" name="cookiezu[save_preferences_text]" value="<?php echo esc_attr( $options['save_preferences_text'] ); ?>" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th><label for="privacy_policy_url"><?php esc_html_e( 'Privacy Policy URL', 'cookiezu' ); ?></label></th>
                        <td><input type="url" id="privacy_policy_url" name="cookiezu[privacy_policy_url]" value="<?php echo esc_url( $options['privacy_policy_url'] ); ?>" class="regular-text" placeholder="https://"></td>
                    </tr>
                    <tr>
                        <th><label for="privacy_policy_text"><?php esc_html_e( 'Privacy Policy Link Text', 'cookiezu' ); ?></label></th>
                        <td><input type="text" id="privacy_policy_text" name="cookiezu[privacy_policy_text]" value="<?php echo esc_attr( $options['privacy_policy_text'] ); ?>" class="regular-text"></td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- DESIGN TAB -->
        <div class="cookiezu-tab-content" id="tab-design">
            <div class="cookiezu-card">
                <h2><?php esc_html_e( 'Appearance', 'cookiezu' ); ?></h2>
                <table class="form-table">
                    <tr>
                        <th><label for="position"><?php esc_html_e( 'Position', 'cookiezu' ); ?></label></th>
                        <td>
                            <select id="position" name="cookiezu[position]">
                                <option value="bottom" <?php selected( $options['position'], 'bottom' ); ?>><?php esc_html_e( 'Bottom (full width)', 'cookiezu' ); ?></option>
                                <option value="top" <?php selected( $options['position'], 'top' ); ?>><?php esc_html_e( 'Top (full width)', 'cookiezu' ); ?></option>
                                <option value="bottom-left" <?php selected( $options['position'], 'bottom-left' ); ?>><?php esc_html_e( 'Bottom Left', 'cookiezu' ); ?></option>
                                <option value="bottom-right" <?php selected( $options['position'], 'bottom-right' ); ?>><?php esc_html_e( 'Bottom Right', 'cookiezu' ); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="layout"><?php esc_html_e( 'Layout', 'cookiezu' ); ?></label></th>
                        <td>
                            <select id="layout" name="cookiezu[layout]">
                                <option value="bar" <?php selected( $options['layout'], 'bar' ); ?>><?php esc_html_e( 'Bar', 'cookiezu' ); ?></option>
                                <option value="box" <?php selected( $options['layout'], 'box' ); ?>><?php esc_html_e( 'Box', 'cookiezu' ); ?></option>
                                <option value="modal" <?php selected( $options['layout'], 'modal' ); ?>><?php esc_html_e( 'Modal', 'cookiezu' ); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="theme"><?php esc_html_e( 'Theme', 'cookiezu' ); ?></label></th>
                        <td>
                            <select id="theme" name="cookiezu[theme]">
                                <option value="light" <?php selected( $options['theme'], 'light' ); ?>><?php esc_html_e( 'Light', 'cookiezu' ); ?></option>
                                <option value="dark" <?php selected( $options['theme'], 'dark' ); ?>><?php esc_html_e( 'Dark', 'cookiezu' ); ?></option>
                                <option value="custom" <?php selected( $options['theme'], 'custom' ); ?>><?php esc_html_e( 'Custom', 'cookiezu' ); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr class="cookiezu-custom-colors" <?php echo $options['theme'] !== 'custom' ? 'style="display:none"' : ''; ?>>
                        <th><?php esc_html_e( 'Primary Color', 'cookiezu' ); ?></th>
                        <td><input type="text" name="cookiezu[primary_color]" value="<?php echo esc_attr( $options['primary_color'] ); ?>" class="cookiezu-color-picker"></td>
                    </tr>
                    <tr class="cookiezu-custom-colors" <?php echo $options['theme'] !== 'custom' ? 'style="display:none"' : ''; ?>>
                        <th><?php esc_html_e( 'Text Color', 'cookiezu' ); ?></th>
                        <td><input type="text" name="cookiezu[text_color]" value="<?php echo esc_attr( $options['text_color'] ); ?>" class="cookiezu-color-picker"></td>
                    </tr>
                    <tr class="cookiezu-custom-colors" <?php echo $options['theme'] !== 'custom' ? 'style="display:none"' : ''; ?>>
                        <th><?php esc_html_e( 'Background Color', 'cookiezu' ); ?></th>
                        <td><input type="text" name="cookiezu[bg_color]" value="<?php echo esc_attr( $options['bg_color'] ); ?>" class="cookiezu-color-picker"></td>
                    </tr>
                    <tr>
                        <th><label for="border_radius"><?php esc_html_e( 'Border Radius (px)', 'cookiezu' ); ?></label></th>
                        <td>
                            <input type="number" id="border_radius" name="cookiezu[border_radius]" value="<?php echo esc_attr( $options['border_radius'] ); ?>" min="0" max="50" class="small-text">
                            <p class="description"><?php esc_html_e( 'Applies to buttons and the box/modal layout.', 'cookiezu' ); ?></p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- CATEGORIES TAB -->
        <div class="cookiezu-tab-content" id="tab-categories">
            <div class="cookiezu-card">
                <h2><?php esc_html_e( 'Cookie Categories', 'cookiezu' ); ?></h2>
                <p class="description"><?php esc_html_e( 'Choose which cookie categories to expose to visitors. Necessary cookies are always enabled.', 'cookiezu' ); ?></p>
                <table class="form-table">
                    <tr>
                        <th><?php esc_html_e( 'Necessary', 'cookiezu' ); ?></th>
                        <td><input type="checkbox" disabled checked> <span class="description"><?php esc_html_e( 'Always enabled – required for the site to function.', 'cookiezu' ); ?></span></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Analytics', 'cookiezu' ); ?></th>
                        <td>
                            <label><input type="checkbox" name="cookiezu[analytics_cookies]" value="1" <?php checked( $options['analytics_cookies'] ); ?>> <?php esc_html_e( 'Show analytics category (e.g. Google Analytics)', 'cookiezu' ); ?></label>
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Marketing', 'cookiezu' ); ?></th>
                        <td>
                            <label><input type="checkbox" name="cookiezu[marketing_cookies]" value="1" <?php checked( $options['marketing_cookies'] ); ?>> <?php esc_html_e( 'Show marketing category (e.g. Facebook Pixel)', 'cookiezu' ); ?></label>
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Functional', 'cookiezu' ); ?></th>
                        <td>
                            <label><input type="checkbox" name="cookiezu[functional_cookies]" value="1" <?php checked( $options['functional_cookies'] ); ?>> <?php esc_html_e( 'Show functional category (e.g. live chat, maps)', 'cookiezu' ); ?></label>
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Show Cookie Table', 'cookiezu' ); ?></th>
                        <td>
                            <label><input type="checkbox" name="cookiezu[show_cookie_table]" value="1" <?php checked( $options['show_cookie_table'] ); ?>> <?php esc_html_e( 'Show a cookie details table in the preference panel', 'cookiezu' ); ?></label>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- INTEGRATIONS TAB -->
        <div class="cookiezu-tab-content" id="tab-integrations">
            <div class="cookiezu-card">
                <h2><?php esc_html_e( 'Integrations', 'cookiezu' ); ?></h2>
                <table class="form-table">
                    <tr>
                        <th><label for="gtm_id"><?php esc_html_e( 'Google Tag Manager ID', 'cookiezu' ); ?></label></th>
                        <td>
                            <input type="text" id="gtm_id" name="cookiezu[gtm_id]" value="<?php echo esc_attr( $options['gtm_id'] ); ?>" class="regular-text" placeholder="GTM-XXXXXXX">
                            <p class="description"><?php esc_html_e( 'CookiEzu will push consent events to GTM\'s dataLayer.', 'cookiezu' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="ga_id"><?php esc_html_e( 'Google Analytics 4 ID', 'cookiezu' ); ?></label></th>
                        <td>
                            <input type="text" id="ga_id" name="cookiezu[ga_id]" value="<?php echo esc_attr( $options['ga_id'] ); ?>" class="regular-text" placeholder="G-XXXXXXXXXX">
                            <p class="description"><?php esc_html_e( 'Automatically loads GA4 and sets consent mode v2.', 'cookiezu' ); ?></p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- ADVANCED TAB -->
        <div class="cookiezu-tab-content" id="tab-advanced">
            <div class="cookiezu-card">
                <h2><?php esc_html_e( 'Advanced Settings', 'cookiezu' ); ?></h2>
                <table class="form-table">
                    <tr>
                        <th><label for="consent_expiry_days"><?php esc_html_e( 'Consent Expiry (days)', 'cookiezu' ); ?></label></th>
                        <td>
                            <input type="number" id="consent_expiry_days" name="cookiezu[consent_expiry_days]" value="<?php echo esc_attr( $options['consent_expiry_days'] ); ?>" min="1" max="730" class="small-text">
                            <p class="description"><?php esc_html_e( 'How long to remember the visitor\'s consent. GDPR recommends 1 year (365 days).', 'cookiezu' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="auto_accept_days"><?php esc_html_e( 'Auto-Accept After (days)', 'cookiezu' ); ?></label></th>
                        <td>
                            <input type="number" id="auto_accept_days" name="cookiezu[auto_accept_days]" value="<?php echo esc_attr( $options['auto_accept_days'] ); ?>" min="0" class="small-text">
                            <p class="description"><?php esc_html_e( 'Auto-accept all cookies after X days of banner being shown. Set 0 to disable. Note: check local regulations before enabling.', 'cookiezu' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Record Consent', 'cookiezu' ); ?></th>
                        <td>
                            <label><input type="checkbox" name="cookiezu[record_consent]" value="1" <?php checked( $options['record_consent'] ); ?>> <?php esc_html_e( 'Log consent records to the database (GDPR audit trail)', 'cookiezu' ); ?></label>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="custom_css"><?php esc_html_e( 'Custom CSS', 'cookiezu' ); ?></label></th>
                        <td>
                            <textarea id="custom_css" name="cookiezu[custom_css]" rows="8" class="large-text code"><?php echo esc_textarea( $options['custom_css'] ); ?></textarea>
                            <p class="description"><?php esc_html_e( 'Add custom CSS to override the banner styles.', 'cookiezu' ); ?></p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="cookiezu-save-bar">
            <?php submit_button( __( 'Save Settings', 'cookiezu' ), 'primary large', 'submit', false ); ?>
        </div>
    </form>
</div>
