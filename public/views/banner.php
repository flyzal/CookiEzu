<?php
/**
 * Cookie banner template.
 *
 * @package CookiEzu
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div id="cookiezu-banner"
     class="cookiezu-banner cookiezu-layout-<?php echo esc_attr( $options['layout'] ); ?> cookiezu-pos-<?php echo esc_attr( $options['position'] ); ?> cookiezu-theme-<?php echo esc_attr( $options['theme'] ); ?>"
     role="dialog"
     aria-modal="true"
     aria-labelledby="cookiezu-banner-title"
     aria-describedby="cookiezu-banner-desc"
>
    <?php if ( $options['layout'] === 'modal' ) : ?>
    <div class="cookiezu-overlay"></div>
    <?php endif; ?>

    <div class="cookiezu-inner">

        <!-- Main banner view -->
        <div class="cookiezu-main" id="cookiezu-main">
            <div class="cookiezu-text">
                <strong class="cookiezu-title" id="cookiezu-banner-title"><?php echo esc_html( $options['banner_title'] ); ?></strong>
                <p class="cookiezu-message" id="cookiezu-banner-desc"><?php echo wp_kses_post( $options['banner_message'] ); ?>
                    <?php if ( ! empty( $options['privacy_policy_url'] ) ) : ?>
                        <a href="<?php echo esc_url( $options['privacy_policy_url'] ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $options['privacy_policy_text'] ); ?></a>
                    <?php endif; ?>
                </p>
            </div>
            <div class="cookiezu-actions">
                <button id="cookiezu-accept-all" class="cookiezu-btn cookiezu-btn-primary"><?php echo esc_html( $options['accept_all_text'] ); ?></button>
                <button id="cookiezu-accept-necessary" class="cookiezu-btn cookiezu-btn-secondary"><?php echo esc_html( $options['accept_necessary_text'] ); ?></button>
                <button id="cookiezu-customize" class="cookiezu-btn cookiezu-btn-link"><?php echo esc_html( $options['customize_text'] ); ?></button>
            </div>
        </div>

        <!-- Preference panel -->
        <div class="cookiezu-preferences" id="cookiezu-preferences">
            <h3><?php esc_html_e( 'Cookie Preferences', 'cookiezu' ); ?></h3>

            <div class="cookiezu-category">
                <div class="cookiezu-category-header">
                    <div>
                        <strong><?php esc_html_e( 'Necessary Cookies', 'cookiezu' ); ?></strong>
                        <p><?php esc_html_e( 'These cookies are essential for the website to function properly. They cannot be disabled.', 'cookiezu' ); ?></p>
                    </div>
                    <label class="cookiezu-toggle cookiezu-toggle-disabled">
                        <input type="checkbox" id="cookiezu-cat-necessary" checked disabled>
                        <span class="cookiezu-slider"></span>
                    </label>
                </div>
            </div>

            <?php if ( ! empty( $options['analytics_cookies'] ) ) : ?>
            <div class="cookiezu-category">
                <div class="cookiezu-category-header">
                    <div>
                        <strong><?php esc_html_e( 'Analytics Cookies', 'cookiezu' ); ?></strong>
                        <p><?php esc_html_e( 'Help us understand how visitors interact with our website by collecting and reporting data anonymously.', 'cookiezu' ); ?></p>
                    </div>
                    <label class="cookiezu-toggle">
                        <input type="checkbox" id="cookiezu-cat-analytics" checked>
                        <span class="cookiezu-slider"></span>
                    </label>
                </div>
                <?php if ( ! empty( $options['show_cookie_table'] ) ) : ?>
                <div class="cookiezu-cookie-table-wrap">
                    <table class="cookiezu-cookie-table">
                        <thead><tr><th><?php esc_html_e( 'Cookie', 'cookiezu' ); ?></th><th><?php esc_html_e( 'Provider', 'cookiezu' ); ?></th><th><?php esc_html_e( 'Expiry', 'cookiezu' ); ?></th></tr></thead>
                        <tbody>
                            <tr><td>_ga</td><td>Google Analytics</td><td>2 years</td></tr>
                            <tr><td>_ga_*</td><td>Google Analytics</td><td>2 years</td></tr>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php if ( ! empty( $options['marketing_cookies'] ) ) : ?>
            <div class="cookiezu-category">
                <div class="cookiezu-category-header">
                    <div>
                        <strong><?php esc_html_e( 'Marketing Cookies', 'cookiezu' ); ?></strong>
                        <p><?php esc_html_e( 'Used to track visitors across websites to display relevant advertisements.', 'cookiezu' ); ?></p>
                    </div>
                    <label class="cookiezu-toggle">
                        <input type="checkbox" id="cookiezu-cat-marketing">
                        <span class="cookiezu-slider"></span>
                    </label>
                </div>
                <?php if ( ! empty( $options['show_cookie_table'] ) ) : ?>
                <div class="cookiezu-cookie-table-wrap">
                    <table class="cookiezu-cookie-table">
                        <thead><tr><th><?php esc_html_e( 'Cookie', 'cookiezu' ); ?></th><th><?php esc_html_e( 'Provider', 'cookiezu' ); ?></th><th><?php esc_html_e( 'Expiry', 'cookiezu' ); ?></th></tr></thead>
                        <tbody>
                            <tr><td>_fbp</td><td>Facebook</td><td>3 months</td></tr>
                            <tr><td>ads/ga-audiences</td><td>Google Ads</td><td>Session</td></tr>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php if ( ! empty( $options['functional_cookies'] ) ) : ?>
            <div class="cookiezu-category">
                <div class="cookiezu-category-header">
                    <div>
                        <strong><?php esc_html_e( 'Functional Cookies', 'cookiezu' ); ?></strong>
                        <p><?php esc_html_e( 'Enable enhanced functionality and personalisation such as live chat and embedded maps.', 'cookiezu' ); ?></p>
                    </div>
                    <label class="cookiezu-toggle">
                        <input type="checkbox" id="cookiezu-cat-functional" checked>
                        <span class="cookiezu-slider"></span>
                    </label>
                </div>
            </div>
            <?php endif; ?>

            <div class="cookiezu-pref-actions">
                <button id="cookiezu-save-prefs" class="cookiezu-btn cookiezu-btn-primary"><?php echo esc_html( $options['save_preferences_text'] ); ?></button>
                <button id="cookiezu-back" class="cookiezu-btn cookiezu-btn-link">← <?php esc_html_e( 'Back', 'cookiezu' ); ?></button>
            </div>
        </div>

    </div><!-- .cookiezu-inner -->
</div><!-- #cookiezu-banner -->

<!-- Re-open button (shown after consent) -->
<button id="cookiezu-reopen" class="cookiezu-reopen" aria-label="<?php esc_attr_e( 'Cookie settings', 'cookiezu' ); ?>">🍪</button>

<?php if ( ! empty( $options['custom_css'] ) ) : ?>
<style id="cookiezu-custom-css"><?php echo wp_strip_all_tags( $options['custom_css'] ); ?></style>
<?php endif; ?>
