<?php
/**
 * Admin settings page view.
 * @package CookiEzu
 */
if ( ! defined( 'ABSPATH' ) ) exit;

// Handle save
$saved = false; $error = null;
if ( isset( $_POST['cookiezu_nonce'] ) ) {
    $result = CookiEzu_Settings::save();
    if ( is_wp_error( $result ) ) { $error = $result->get_error_message(); }
    else { $saved = true; }
}
$options = cookiezu()->get_options();
?>

<?php /* Inline page-level overrides — guaranteed to load */ ?>
<style>
#wpcontent, #wpbody-content { background: #F7F2EA !important; }
.cookiezu-wrap .wp-heading-inline { display: none; }
</style>

<div class="wrap cookiezu-wrap">

    <div class="cookiezu-header">
        <div class="cookiezu-header-brand">
            <div class="cookiezu-logo-icon">🍪</div>
            <h1>Cooki<span>Ezu</span></h1>
        </div>
        <p class="cookiezu-tagline"><?php esc_html_e( 'Lightweight, GDPR-compliant cookie consent — open source &amp; free forever.', 'cookiezu' ); ?></p>
        <div class="cookiezu-header-badges">
            <span class="cookiezu-badge cookiezu-badge-gpl">✓ GPL v2</span>
            <a class="cookiezu-badge cookiezu-badge-github" href="https://github.com/flyzal/CookiEzu" target="_blank">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.37 0 0 5.37 0 12c0 5.31 3.435 9.795 8.205 11.385.6.105.825-.255.825-.57 0-.285-.015-1.23-.015-2.235-3.015.555-3.795-.735-4.035-1.41-.135-.345-.72-1.41-1.23-1.695-.42-.225-1.02-.78-.015-.795.945-.015 1.62.87 1.845 1.23 1.08 1.815 2.805 1.305 3.495.99.105-.78.42-1.305.765-1.605-2.67-.3-5.46-1.335-5.46-5.925 0-1.305.465-2.385 1.23-3.225-.12-.3-.54-1.53.12-3.18 0 0 1.005-.315 3.3 1.23.96-.27 1.98-.405 3-.405s2.04.135 3 .405c2.295-1.56 3.3-1.23 3.3-1.23.66 1.65.24 2.88.12 3.18.765.84 1.23 1.905 1.23 3.225 0 4.605-2.805 5.625-5.475 5.925.435.375.81 1.095.81 2.22 0 1.605-.015 2.895-.015 3.3 0 .315.225.69.825.57A12.02 12.02 0 0024 12c0-6.63-5.37-12-12-12z"/></svg>
                ⭐ Star on GitHub
            </a>
        </div>
    </div>

    <?php if ( $saved ) : ?>
        <div class="notice notice-success is-dismissible"><p>✓ <?php esc_html_e( 'Settings saved!', 'cookiezu' ); ?></p></div>
    <?php endif; ?>
    <?php if ( $error ) : ?>
        <div class="notice notice-error"><p><?php echo esc_html( $error ); ?></p></div>
    <?php endif; ?>

    <form method="post" action="">
        <?php wp_nonce_field( 'cookiezu_save_settings', 'cookiezu_nonce' ); ?>

        <div class="cookiezu-tabs">
            <button type="button" class="cookiezu-tab active" data-tab="content">Content</button>
            <button type="button" class="cookiezu-tab" data-tab="design">Design</button>
            <button type="button" class="cookiezu-tab" data-tab="categories">Categories</button>
            <button type="button" class="cookiezu-tab" data-tab="integrations">Integrations</button>
            <button type="button" class="cookiezu-tab" data-tab="advanced">Advanced</button>
        </div>

        <!-- ── CONTENT ── -->
        <div class="cookiezu-tab-content active" id="tab-content">
            <div class="cookiezu-card">
                <h2>Banner Text</h2>
                <table class="form-table">
                    <tr>
                        <th><label for="banner_title">Banner Title</label></th>
                        <td><input type="text" id="banner_title" name="cookiezu[banner_title]" value="<?php echo esc_attr( $options['banner_title'] ); ?>" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th><label for="banner_message">Banner Message</label></th>
                        <td><textarea id="banner_message" name="cookiezu[banner_message]" rows="4" class="large-text"><?php echo esc_textarea( $options['banner_message'] ); ?></textarea></td>
                    </tr>
                    <tr>
                        <th><label for="accept_all_text">Accept All Button</label></th>
                        <td><input type="text" id="accept_all_text" name="cookiezu[accept_all_text]" value="<?php echo esc_attr( $options['accept_all_text'] ); ?>" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th><label for="accept_necessary_text">Necessary Only Button</label></th>
                        <td><input type="text" id="accept_necessary_text" name="cookiezu[accept_necessary_text]" value="<?php echo esc_attr( $options['accept_necessary_text'] ); ?>" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th><label for="customize_text">Customize Button</label></th>
                        <td><input type="text" id="customize_text" name="cookiezu[customize_text]" value="<?php echo esc_attr( $options['customize_text'] ); ?>" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th><label for="save_preferences_text">Save Preferences Button</label></th>
                        <td><input type="text" id="save_preferences_text" name="cookiezu[save_preferences_text]" value="<?php echo esc_attr( $options['save_preferences_text'] ); ?>" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th><label for="privacy_policy_url">Privacy Policy URL</label></th>
                        <td><input type="url" id="privacy_policy_url" name="cookiezu[privacy_policy_url]" value="<?php echo esc_url( $options['privacy_policy_url'] ); ?>" class="regular-text" placeholder="https://"></td>
                    </tr>
                    <tr>
                        <th><label for="privacy_policy_text">Privacy Policy Link Text</label></th>
                        <td><input type="text" id="privacy_policy_text" name="cookiezu[privacy_policy_text]" value="<?php echo esc_attr( $options['privacy_policy_text'] ); ?>" class="regular-text"></td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- ── DESIGN ── -->
        <div class="cookiezu-tab-content" id="tab-design">
            <div class="cookiezu-card">
                <h2>Appearance</h2>
                <table class="form-table">
                    <tr>
                        <th><label for="position">Position</label></th>
                        <td>
                            <select id="position" name="cookiezu[position]">
                                <option value="bottom" <?php selected( $options['position'], 'bottom' ); ?>>Bottom (full width)</option>
                                <option value="top" <?php selected( $options['position'], 'top' ); ?>>Top (full width)</option>
                                <option value="bottom-left" <?php selected( $options['position'], 'bottom-left' ); ?>>Bottom Left</option>
                                <option value="bottom-right" <?php selected( $options['position'], 'bottom-right' ); ?>>Bottom Right</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="layout">Layout</label></th>
                        <td>
                            <select id="layout" name="cookiezu[layout]">
                                <option value="bar" <?php selected( $options['layout'], 'bar' ); ?>>Bar</option>
                                <option value="box" <?php selected( $options['layout'], 'box' ); ?>>Box</option>
                                <option value="modal" <?php selected( $options['layout'], 'modal' ); ?>>Modal</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="theme">Theme</label></th>
                        <td>
                            <select id="theme" name="cookiezu[theme]">
                                <option value="light" <?php selected( $options['theme'], 'light' ); ?>>Light</option>
                                <option value="dark" <?php selected( $options['theme'], 'dark' ); ?>>Dark</option>
                                <option value="custom" <?php selected( $options['theme'], 'custom' ); ?>>Custom</option>
                            </select>
                        </td>
                    </tr>
                    <tr class="cookiezu-custom-colors" <?php echo $options['theme'] !== 'custom' ? 'style="display:none"' : ''; ?>>
                        <th>Primary Color</th>
                        <td><input type="text" name="cookiezu[primary_color]" value="<?php echo esc_attr( $options['primary_color'] ); ?>" class="cookiezu-color-picker"></td>
                    </tr>
                    <tr class="cookiezu-custom-colors" <?php echo $options['theme'] !== 'custom' ? 'style="display:none"' : ''; ?>>
                        <th>Text Color</th>
                        <td><input type="text" name="cookiezu[text_color]" value="<?php echo esc_attr( $options['text_color'] ); ?>" class="cookiezu-color-picker"></td>
                    </tr>
                    <tr class="cookiezu-custom-colors" <?php echo $options['theme'] !== 'custom' ? 'style="display:none"' : ''; ?>>
                        <th>Background Color</th>
                        <td><input type="text" name="cookiezu[bg_color]" value="<?php echo esc_attr( $options['bg_color'] ); ?>" class="cookiezu-color-picker"></td>
                    </tr>
                    <tr>
                        <th><label for="border_radius">Border Radius (px)</label></th>
                        <td>
                            <input type="number" id="border_radius" name="cookiezu[border_radius]" value="<?php echo esc_attr( $options['border_radius'] ); ?>" min="0" max="50" class="small-text">
                            <p class="description">Applies to buttons and the box/modal layout (0–50).</p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- ── CATEGORIES ── -->
        <div class="cookiezu-tab-content" id="tab-categories">
            <div class="cookiezu-card">
                <h2>Cookie Categories</h2>
                <table class="form-table">
                    <tr>
                        <th>Necessary</th>
                        <td><label><input type="checkbox" disabled checked> Always enabled — required for the site to function.</label></td>
                    </tr>
                    <tr>
                        <th>Analytics</th>
                        <td><label><input type="checkbox" name="cookiezu[analytics_cookies]" value="1" <?php checked( $options['analytics_cookies'] ); ?>> Show analytics category (e.g. Google Analytics)</label></td>
                    </tr>
                    <tr>
                        <th>Marketing</th>
                        <td><label><input type="checkbox" name="cookiezu[marketing_cookies]" value="1" <?php checked( $options['marketing_cookies'] ); ?>> Show marketing category (e.g. Facebook Pixel)</label></td>
                    </tr>
                    <tr>
                        <th>Functional</th>
                        <td><label><input type="checkbox" name="cookiezu[functional_cookies]" value="1" <?php checked( $options['functional_cookies'] ); ?>> Show functional category (e.g. live chat, maps)</label></td>
                    </tr>
                    <tr>
                        <th>Show Cookie Table</th>
                        <td><label><input type="checkbox" name="cookiezu[show_cookie_table]" value="1" <?php checked( $options['show_cookie_table'] ); ?>> Show a cookie details table in the preference panel</label></td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- ── INTEGRATIONS ── -->
        <div class="cookiezu-tab-content" id="tab-integrations">
            <div class="cookiezu-card">
                <h2>Google Analytics 4</h2>
                <table class="form-table">
                    <tr>
                        <th><label for="ga_id">Measurement ID</label></th>
                        <td>
                            <input type="text" id="ga_id" name="cookiezu[ga_id]" value="<?php echo esc_attr( $options['ga_id'] ); ?>" class="regular-text" placeholder="G-XXXXXXXXXX">
                            <p class="description">Automatically loads GA4 with Consent Mode v2. Tracking only starts after the visitor accepts Analytics cookies.</p>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="cookiezu-card">
                <h2>Google Tag Manager</h2>
                <table class="form-table">
                    <tr>
                        <th><label for="gtm_id">Container ID</label></th>
                        <td>
                            <input type="text" id="gtm_id" name="cookiezu[gtm_id]" value="<?php echo esc_attr( $options['gtm_id'] ); ?>" class="regular-text" placeholder="GTM-XXXXXXX">
                            <p class="description">CookiEzu pushes a <code>cookiezu_consent_updated</code> event to GTM's dataLayer on every consent save. Use this to trigger Facebook Pixel, Hotjar, LinkedIn Insight, etc.</p>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="cookiezu-card">
                <h2>JavaScript API</h2>
                <table class="form-table">
                    <tr>
                        <th>Custom Event</th>
                        <td>
<textarea class="large-text code" rows="9" readonly>document.addEventListener('cookiezuConsentUpdated', function(e) {
  var consent = e.detail;
  // consent.analytics  → true / false
  // consent.marketing  → true / false
  // consent.functional → true / false
  if (consent.analytics)  { /* load Hotjar, etc */ }
  if (consent.marketing)  { /* load Facebook Pixel */ }
  if (consent.functional) { /* load Intercom */ }
});</textarea>
                            <p class="description">Listen for this event anywhere in your theme to conditionally load any script. <a href="https://flyzal.github.io/CookiEzu/docs" target="_blank">Full API docs →</a></p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- ── ADVANCED ── -->
        <div class="cookiezu-tab-content" id="tab-advanced">
            <div class="cookiezu-card">
                <h2>Advanced Settings</h2>
                <table class="form-table">
                    <tr>
                        <th><label for="consent_expiry_days">Consent Expiry (days)</label></th>
                        <td>
                            <input type="number" id="consent_expiry_days" name="cookiezu[consent_expiry_days]" value="<?php echo esc_attr( $options['consent_expiry_days'] ); ?>" min="1" max="730" class="small-text">
                            <p class="description">How long to remember consent. GDPR recommends 365 days max.</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="auto_accept_days">Auto-Accept After (days)</label></th>
                        <td>
                            <input type="number" id="auto_accept_days" name="cookiezu[auto_accept_days]" value="<?php echo esc_attr( $options['auto_accept_days'] ); ?>" min="0" class="small-text">
                            <p class="description">Auto-accept all cookies after X days. Set 0 to disable. Check local regulations before enabling.</p>
                        </td>
                    </tr>
                    <tr>
                        <th>Record Consent</th>
                        <td><label><input type="checkbox" name="cookiezu[record_consent]" value="1" <?php checked( $options['record_consent'] ); ?>> Log consent records to the database (GDPR audit trail)</label></td>
                    </tr>
                    <tr>
                        <th><label for="reopen_position">Re-open Button Position</label></th>
                        <td>
                            <select id="reopen_position" name="cookiezu[reopen_position]">
                                <option value="bottom-left"  <?php selected( $options['reopen_position'] ?? 'bottom-left', 'bottom-left' );  ?>>Bottom Left</option>
                                <option value="bottom-right" <?php selected( $options['reopen_position'] ?? 'bottom-left', 'bottom-right' ); ?>>Bottom Right</option>
                            </select>
                            <p class="description">Position of the 🍪 floating re-open button after consent is given.</p>
                        </td>
                    </tr>
                    <tr>
                        <th>Keyboard Settings</th>
                        <td>
                            <label><input type="checkbox" name="cookiezu[escape_key_close]" value="1" <?php checked( $options['escape_key_close'] ?? true ); ?>> Allow <kbd>Esc</kbd> key to dismiss the banner (accepts Necessary Only)</label>
                            <p class="description">Recommended. Required for WCAG 2.1 §2.1 keyboard accessibility compliance.</p>
                        </td>
                    </tr>
                    <tr>
                        <th>Test Mode <span style="background:#FDF3E3;color:#C17B2F;font-size:10px;font-weight:700;padding:2px 6px;border-radius:4px;vertical-align:middle;">v1.2.0</span></th>
                        <td>
                            <label><input type="checkbox" name="cookiezu[test_mode]" value="1" <?php checked( ! empty( $options['test_mode'] ) ); ?>> Show banner to logged-in admins even if they already consented</label>
                            <p class="description">Useful for previewing design changes without clearing cookies. A <strong>🔧 TEST MODE</strong> badge appears on the banner. Consent is not recorded in test mode.</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="custom_css">Custom CSS</label></th>
                        <td>
                            <textarea id="custom_css" name="cookiezu[custom_css]" rows="8" class="large-text code"><?php echo esc_textarea( $options['custom_css'] ); ?></textarea>
                            <p class="description">Add custom CSS to override banner styles. <a href="https://flyzal.github.io/CookiEzu/docs#custom-css" target="_blank">CSS variable reference →</a></p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="cookiezu-save-bar">
            <?php submit_button( 'Save Settings', 'primary large', 'submit', false ); ?>
        </div>
    </form>
</div>
