<?php
/**
 * Cookie banner template.
 *
 * @package CookiEzu
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$lang    = $options['banner_language'] ?? 'en';
$str     = CookiEzu::get_lang_strings( $lang );
$is_rtl  = ( $lang === 'ar' );
$dir_attr = $is_rtl ? ' dir="rtl"' : '';

// Resolve display strings — use saved admin fields as override, fall back to language strings
$title         = ! empty( $options['banner_title'] )          ? $options['banner_title']          : ( $str['banner_title']          ?? 'We value your privacy 🍪' );
$message       = ! empty( $options['banner_message'] )        ? $options['banner_message']        : ( $str['banner_message']        ?? '' );
$accept_all    = ! empty( $options['accept_all_text'] )       ? $options['accept_all_text']       : ( $str['accept_all']            ?? 'Accept All' );
$necessary     = ! empty( $options['accept_necessary_text'] ) ? $options['accept_necessary_text'] : ( $str['necessary_only']        ?? 'Necessary Only' );
$customize     = ! empty( $options['customize_text'] )        ? $options['customize_text']        : ( $str['customize']            ?? 'Customize' );
$save_prefs    = ! empty( $options['save_preferences_text'] ) ? $options['save_preferences_text'] : ( $str['save_preferences']      ?? 'Save Preferences' );
$pp_text       = ! empty( $options['privacy_policy_text'] )   ? $options['privacy_policy_text']   : ( $str['privacy_policy']       ?? 'Privacy Policy' );
$back_text     = $str['back'] ?? '← Back';
$pref_title    = $str['preferences_title'] ?? 'Cookie Preferences';
?>
<div id="cookiezu-banner"
     class="cookiezu-banner cookiezu-layout-<?php echo esc_attr( $options['layout'] ); ?> cookiezu-pos-<?php echo esc_attr( $options['position'] ); ?> cookiezu-theme-<?php echo esc_attr( $options['theme'] ); ?><?php echo $is_rtl ? ' cookiezu-rtl' : ''; ?>"
     role="dialog"
     aria-modal="true"
     aria-labelledby="cookiezu-banner-title"
     aria-describedby="cookiezu-banner-desc"
     <?php echo $dir_attr; ?>
>
    <?php if ( $options['layout'] === 'modal' ) : ?>
    <div class="cookiezu-overlay"></div>
    <?php endif; ?>

    <div class="cookiezu-inner">

        <!-- Main banner view -->
        <div class="cookiezu-main" id="cookiezu-main">
            <div class="cookiezu-text">
                <strong class="cookiezu-title" id="cookiezu-banner-title"><?php echo esc_html( $title ); ?></strong>
                <p class="cookiezu-message" id="cookiezu-banner-desc"><?php echo wp_kses_post( $message ); ?>
                    <?php if ( ! empty( $options['privacy_policy_url'] ) ) : ?>
                        <a href="<?php echo esc_url( $options['privacy_policy_url'] ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $pp_text ); ?></a>
                    <?php endif; ?>
                </p>

                <?php if ( ! empty( $options['extended_disclosure'] ) ) : ?>
                <p class="cookiezu-extended-disclosure"><?php echo wp_kses_post( $options['extended_disclosure'] ); ?></p>
                <?php endif; ?>

                <?php if ( ! empty( $options['data_processing_location'] ) ) : ?>
                <p class="cookiezu-data-location">
                    <?php
                    /* translators: %s: country or region name */
                    printf( esc_html__( 'Your data is processed and stored on servers located in %s.', 'cookiezu' ), '<strong>' . esc_html( $options['data_processing_location'] ) . '</strong>' );
                    ?>
                </p>
                <?php endif; ?>
            </div>
            <div class="cookiezu-actions">
                <button id="cookiezu-accept-all"       class="cookiezu-btn cookiezu-btn-primary"><?php echo esc_html( $accept_all ); ?></button>
                <button id="cookiezu-accept-necessary" class="cookiezu-btn cookiezu-btn-secondary"><?php echo esc_html( $necessary ); ?></button>
                <button id="cookiezu-customize"        class="cookiezu-btn cookiezu-btn-link"><?php echo esc_html( $customize ); ?></button>
            </div>
        </div>

        <!-- Preference panel -->
        <div class="cookiezu-preferences" id="cookiezu-preferences">
            <h3><?php echo esc_html( $pref_title ); ?></h3>

            <div class="cookiezu-category">
                <div class="cookiezu-category-header">
                    <div>
                        <strong><?php echo esc_html( $str['cat_necessary'] ?? __( 'Necessary Cookies', 'cookiezu' ) ); ?></strong>
                        <p><?php echo esc_html( $str['cat_necessary_desc'] ?? __( 'These cookies are essential for the website to function properly. They cannot be disabled.', 'cookiezu' ) ); ?></p>
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
                        <strong><?php echo esc_html( $str['cat_analytics'] ?? __( 'Analytics Cookies', 'cookiezu' ) ); ?></strong>
                        <p><?php echo esc_html( $str['cat_analytics_desc'] ?? __( 'Help us understand how visitors interact with our website by collecting and reporting data anonymously.', 'cookiezu' ) ); ?></p>
                    </div>
                    <label class="cookiezu-toggle">
                        <input type="checkbox" id="cookiezu-cat-analytics" checked>
                        <span class="cookiezu-slider"></span>
                    </label>
                </div>
                <?php if ( ! empty( $options['show_cookie_table'] ) ) : ?>
                <div class="cookiezu-cookie-table-wrap">
                    <table class="cookiezu-cookie-table">
                        <thead><tr>
                            <th><?php echo esc_html( $str['tbl_cookie'] ?? __( 'Cookie', 'cookiezu' ) ); ?></th>
                            <th><?php echo esc_html( $str['tbl_provider'] ?? __( 'Provider', 'cookiezu' ) ); ?></th>
                            <th><?php echo esc_html( $str['tbl_expiry'] ?? __( 'Expiry', 'cookiezu' ) ); ?></th>
                        </tr></thead>
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
                        <strong><?php echo esc_html( $str['cat_marketing'] ?? __( 'Marketing Cookies', 'cookiezu' ) ); ?></strong>
                        <p><?php echo esc_html( $str['cat_marketing_desc'] ?? __( 'Used to track visitors across websites to display relevant advertisements.', 'cookiezu' ) ); ?></p>
                    </div>
                    <label class="cookiezu-toggle">
                        <input type="checkbox" id="cookiezu-cat-marketing">
                        <span class="cookiezu-slider"></span>
                    </label>
                </div>
                <?php if ( ! empty( $options['show_cookie_table'] ) ) : ?>
                <div class="cookiezu-cookie-table-wrap">
                    <table class="cookiezu-cookie-table">
                        <thead><tr>
                            <th><?php echo esc_html( $str['tbl_cookie']    ?? __( 'Cookie',   'cookiezu' ) ); ?></th>
                            <th><?php echo esc_html( $str['tbl_provider']  ?? __( 'Provider', 'cookiezu' ) ); ?></th>
                            <th><?php echo esc_html( $str['tbl_expiry']    ?? __( 'Expiry',   'cookiezu' ) ); ?></th>
                        </tr></thead>
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
                        <strong><?php echo esc_html( $str['cat_functional'] ?? __( 'Functional Cookies', 'cookiezu' ) ); ?></strong>
                        <p><?php echo esc_html( $str['cat_functional_desc'] ?? __( 'Enable enhanced functionality and personalisation such as live chat and embedded maps.', 'cookiezu' ) ); ?></p>
                    </div>
                    <label class="cookiezu-toggle">
                        <input type="checkbox" id="cookiezu-cat-functional" checked>
                        <span class="cookiezu-slider"></span>
                    </label>
                </div>
            </div>
            <?php endif; ?>

            <div class="cookiezu-pref-actions">
                <button id="cookiezu-save-prefs" class="cookiezu-btn cookiezu-btn-primary"><?php echo esc_html( $save_prefs ); ?></button>
                <button id="cookiezu-back"       class="cookiezu-btn cookiezu-btn-link"><?php echo esc_html( $back_text ); ?></button>
            </div>
        </div>

    </div><!-- .cookiezu-inner -->
</div><!-- #cookiezu-banner -->

<!-- Re-open button -->
<button id="cookiezu-reopen" class="cookiezu-reopen" aria-label="<?php echo esc_attr( $str['reopen_aria'] ?? __( 'Cookie settings', 'cookiezu' ) ); ?>">🍪</button>

<?php if ( ! empty( $options['custom_css'] ) ) : ?>
<style id="cookiezu-custom-css"><?php echo wp_strip_all_tags( $options['custom_css'] ); ?></style>
<?php endif; ?>
