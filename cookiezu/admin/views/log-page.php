<?php
/**
 * Consent log page — v1.2.1
 * Adds visual metrics dashboard above the raw log table.
 * Country stored as 2-letter code only (GDPR minimal data principle).
 *
 * @package CookiEzu
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

add_action( 'admin_head', function() { ?>
<style>
#wpcontent, #wpbody-content { background: #F7F2EA !important; }
#wpcontent { padding-left: 0 !important; padding-right: 0 !important; }
.cookiezu_page_cookiezu-log .cookiezu-wrap { padding: 0 24px 60px !important; }
</style>
<?php } );

global $wpdb;
$table = $wpdb->prefix . 'cookiezu_consent_log';

/* ── Aggregate metrics ── */
$total      = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $table" );
$accept_all = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $table WHERE analytics=1 AND marketing=1 AND functional=1" );
$nec_only   = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $table WHERE analytics=0 AND marketing=0 AND functional=0" );
$custom_c   = $total - $accept_all - $nec_only;
$analytics  = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $table WHERE analytics=1" );
$marketing  = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $table WHERE marketing=1" );
$functional = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $table WHERE functional=1" );

/* ── 30-day trend (daily counts) ── */
$trend = $wpdb->get_results(
    "SELECT DATE(consent_date) as day, COUNT(*) as cnt
     FROM $table
     WHERE consent_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
     GROUP BY DATE(consent_date)
     ORDER BY day ASC"
);

/* ── Country breakdown (top 10) ── */
$countries = $wpdb->get_results(
    "SELECT country_code, COUNT(*) as cnt
     FROM $table
     WHERE country_code IS NOT NULL AND country_code != ''
     GROUP BY country_code
     ORDER BY cnt DESC
     LIMIT 10"
);

/* ── Recent rows ── */
$rows = $wpdb->get_results( "SELECT * FROM $table ORDER BY consent_date DESC LIMIT 200" );

/* ── Helper ── */
function cz_pct( $part, $total ) {
    return $total > 0 ? round( ($part / $total) * 100 ) : 0;
}

/* ── Flag emoji from country code ── */
function cz_flag( $code ) {
    if ( strlen($code) !== 2 ) return '🌍';
    $code = strtoupper( $code );
    // Regional indicator symbols: A=🇦 (U+1F1E6), offset from ASCII A (65)
    $offset = 0x1F1E6 - ord('A');
    $flag   = mb_chr( ord($code[0]) + $offset, 'UTF-8' )
            . mb_chr( ord($code[1]) + $offset, 'UTF-8' );
    return $flag;
}
?>
<div class="wrap cookiezu-wrap">

    <div class="cookiezu-header">
        <div class="cookiezu-header-brand">
            <div class="cookiezu-logo-icon">🍪</div>
            <h1>Cooki<span>Ezu</span> — <?php esc_html_e( 'Consent Log', 'cookiezu' ); ?></h1>
        </div>
        <p class="cookiezu-tagline"><?php printf( esc_html__( 'Total records: %s (showing latest 200)', 'cookiezu' ), number_format_i18n( $total ) ); ?></p>
        <div class="cookiezu-header-badges">
            <span class="cookiezu-badge cookiezu-badge-gpl">✓ GDPR Audit Trail</span>
        </div>
    </div>

    <?php if ( $total === 0 ) : ?>
        <div class="cookiezu-card"><p><?php esc_html_e( 'No consent records yet. Make sure "Record Consent" is enabled in Settings.', 'cookiezu' ); ?></p></div>
    <?php else : ?>

    <!-- ── METRICS GRID ── -->
    <div class="czlog-metrics-grid">

        <!-- Consent rate card -->
        <div class="czlog-card czlog-card-rate">
            <div class="czlog-card-label">Total Consents</div>
            <div class="czlog-card-value"><?php echo number_format_i18n( $total ); ?></div>
            <div class="czlog-card-sub">All time</div>
        </div>

        <!-- Accept all -->
        <div class="czlog-card">
            <div class="czlog-card-label">Accept All</div>
            <div class="czlog-card-value czlog-val-green"><?php echo cz_pct( $accept_all, $total ); ?>%</div>
            <div class="czlog-donut-wrap">
                <svg viewBox="0 0 36 36" class="czlog-donut">
                    <circle cx="18" cy="18" r="15.9" fill="none" stroke="#e8f5ee" stroke-width="3"/>
                    <circle cx="18" cy="18" r="15.9" fill="none" stroke="#2D7A4F" stroke-width="3"
                        stroke-dasharray="<?php echo cz_pct($accept_all,$total); ?> 100"
                        stroke-dashoffset="25" stroke-linecap="round" transform="rotate(-90 18 18)"/>
                </svg>
            </div>
            <div class="czlog-card-sub"><?php echo number_format_i18n( $accept_all ); ?> visitors</div>
        </div>

        <!-- Necessary only -->
        <div class="czlog-card">
            <div class="czlog-card-label">Necessary Only</div>
            <div class="czlog-card-value czlog-val-amber"><?php echo cz_pct( $nec_only, $total ); ?>%</div>
            <div class="czlog-donut-wrap">
                <svg viewBox="0 0 36 36" class="czlog-donut">
                    <circle cx="18" cy="18" r="15.9" fill="none" stroke="#FDF3E3" stroke-width="3"/>
                    <circle cx="18" cy="18" r="15.9" fill="none" stroke="#C17B2F" stroke-width="3"
                        stroke-dasharray="<?php echo cz_pct($nec_only,$total); ?> 100"
                        stroke-dashoffset="25" stroke-linecap="round" transform="rotate(-90 18 18)"/>
                </svg>
            </div>
            <div class="czlog-card-sub"><?php echo number_format_i18n( $nec_only ); ?> visitors</div>
        </div>

        <!-- Custom -->
        <div class="czlog-card">
            <div class="czlog-card-label">Custom Choices</div>
            <div class="czlog-card-value czlog-val-blue"><?php echo cz_pct( $custom_c, $total ); ?>%</div>
            <div class="czlog-donut-wrap">
                <svg viewBox="0 0 36 36" class="czlog-donut">
                    <circle cx="18" cy="18" r="15.9" fill="none" stroke="#EFF6FF" stroke-width="3"/>
                    <circle cx="18" cy="18" r="15.9" fill="none" stroke="#2563EB" stroke-width="3"
                        stroke-dasharray="<?php echo cz_pct($custom_c,$total); ?> 100"
                        stroke-dashoffset="25" stroke-linecap="round" transform="rotate(-90 18 18)"/>
                </svg>
            </div>
            <div class="czlog-card-sub"><?php echo number_format_i18n( $custom_c ); ?> visitors</div>
        </div>
    </div>

    <!-- ── CATEGORY ACCEPTANCE BARS ── -->
    <div class="czlog-row">
        <div class="cookiezu-card czlog-categories-card">
            <h3 class="czlog-section-title">Category Acceptance Rate</h3>
            <?php
            $cats = [
                'Analytics'  => [ $analytics,  '#2D7A4F', '#EDF7F2' ],
                'Marketing'  => [ $marketing,  '#2563EB', '#EFF6FF' ],
                'Functional' => [ $functional, '#C17B2F', '#FDF3E3' ],
                'Necessary'  => [ $total,      '#6B7280', '#F3F4F6' ],
            ];
            foreach ( $cats as $name => [ $cnt, $color, $bg ] ) :
                $pct = cz_pct( $cnt, $total );
            ?>
            <div class="czlog-cat-row">
                <div class="czlog-cat-name"><?php echo esc_html($name); ?></div>
                <div class="czlog-cat-bar-wrap">
                    <div class="czlog-cat-bar" style="width:<?php echo $pct; ?>%;background:<?php echo esc_attr($color); ?>"></div>
                </div>
                <div class="czlog-cat-pct" style="color:<?php echo esc_attr($color); ?>"><?php echo $pct; ?>%</div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- ── 30-DAY TREND CHART ── -->
        <div class="cookiezu-card czlog-trend-card">
            <h3 class="czlog-section-title">30-Day Consent Trend</h3>
            <div class="czlog-trend-chart" id="czTrendChart">
                <?php
                if ( ! empty( $trend ) ) {
                    $max = max( array_column( (array) $trend, 'cnt' ) );
                    $max = max( $max, 1 );
                    echo '<div class="czlog-bars">';
                    foreach ( $trend as $row ) {
                        $h = round( ( $row->cnt / $max ) * 100 );
                        $label = date_i18n( 'M j', strtotime( $row->day ) );
                        echo '<div class="czlog-bar-col" title="' . esc_attr( $label . ': ' . $row->cnt . ' records' ) . '">';
                        echo '<div class="czlog-bar-inner" style="height:' . $h . '%"></div>';
                        echo '</div>';
                    }
                    echo '</div>';
                    echo '<div class="czlog-trend-foot">Last 30 days — ' . number_format_i18n( array_sum( array_column( (array)$trend, 'cnt' ) ) ) . ' consents</div>';
                } else {
                    echo '<div class="czlog-no-data">No data in the last 30 days</div>';
                }
                ?>
            </div>
        </div>
    </div>

    <?php if ( ! empty( $countries ) ) : ?>
    <!-- ── COUNTRY BREAKDOWN ── -->
    <div class="cookiezu-card czlog-countries-card">
        <h3 class="czlog-section-title">
            Top Countries
            <span class="czlog-gdpr-note" title="Only the 2-letter country code is stored. No city or precise location data is retained.">ⓘ Country code only — GDPR minimal data</span>
        </h3>
        <div class="czlog-countries-grid">
            <?php foreach ( $countries as $c ) :
                $pct = cz_pct( $c->cnt, $total );
            ?>
            <div class="czlog-country-row">
                <span class="czlog-country-flag"><?php echo cz_flag( $c->country_code ); ?></span>
                <span class="czlog-country-code"><?php echo esc_html( strtoupper( $c->country_code ) ); ?></span>
                <?php
                $code = strtoupper( $c->country_code );
                $tier = CookiEzu::$compliance_tiers[ $code ] ?? 'none';
                $meta = CookiEzu::$tier_meta[ $tier ];
                if ( $tier !== 'none' ) :
                ?>
                <span class="czlog-tier-badge" style="background:<?php echo esc_attr( $meta['color'] ); ?>22;color:<?php echo esc_attr( $meta['color'] ); ?>;border:1px solid <?php echo esc_attr( $meta['color'] ); ?>44;font-size:9px;font-weight:700;padding:1px 5px;border-radius:4px;white-space:nowrap;"><?php echo esc_html( $meta['label'] ); ?></span>
                <?php endif; ?>
                <div class="czlog-country-bar-wrap">
                    <div class="czlog-country-bar" style="width:<?php echo $pct; ?>%"></div>
                </div>
                <span class="czlog-country-cnt"><?php echo number_format_i18n( $c->cnt ); ?></span>
                <span class="czlog-country-pct"><?php echo $pct; ?>%</span>
            </div>
            <?php endforeach; ?>
        </div>
        <p class="czlog-country-note">Country detection requires the <code>country_code</code> column (added in v1.2.1). <a href="https://flyzal.github.io/CookiEzu/docs#consent-log" target="_blank">See docs →</a></p>
    </div>
    <?php endif; ?>

    <!-- ── RAW LOG TABLE ── -->
    <div class="cookiezu-card czlog-raw-card">
        <h3 class="czlog-section-title">Raw Consent Records <span style="font-size:12px;font-weight:400;color:var(--cza-ink-45)">(latest 200)</span></h3>
        <table class="widefat fixed striped cookiezu-log-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th><?php esc_html_e( 'Date', 'cookiezu' ); ?></th>
                    <th><?php esc_html_e( 'IP (masked)', 'cookiezu' ); ?></th>
                    <th><?php esc_html_e( 'Country', 'cookiezu' ); ?></th>
                    <th><?php esc_html_e( 'Analytics', 'cookiezu' ); ?></th>
                    <th><?php esc_html_e( 'Marketing', 'cookiezu' ); ?></th>
                    <th><?php esc_html_e( 'Functional', 'cookiezu' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $rows as $row ) : ?>
                <tr>
                    <td><?php echo esc_html( $row->id ); ?></td>
                    <td><?php echo esc_html( $row->consent_date ); ?></td>
                    <td style="font-family:monospace"><?php echo esc_html( substr( $row->ip_address, 0, -4 ) . '****' ); ?></td>
                    <td>
                        <?php if ( ! empty( $row->country_code ) ) :
                            echo cz_flag( $row->country_code ) . ' ' . esc_html( strtoupper($row->country_code) );
                        else : ?>
                            <span style="color:var(--cza-ink-45);font-size:11px">—</span>
                        <?php endif; ?>
                    </td>
                    <td style="font-size:11px;opacity:0.7;"><?php echo esc_html( $row->policy_version ?? '1' ); ?></td>
                    <td><?php echo $row->analytics  ? '<span class="czlog-yes">✓</span>' : '<span class="czlog-no">✗</span>'; ?></td>
                    <td><?php echo $row->marketing   ? '<span class="czlog-yes">✓</span>' : '<span class="czlog-no">✗</span>'; ?></td>
                    <td><?php echo $row->functional  ? '<span class="czlog-yes">✓</span>' : '<span class="czlog-no">✗</span>'; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php endif; ?>

    <div class="cookiezu-admin-footer">
        <span>CookiEzu v<?php echo esc_html( COOKIEZU_VERSION ); ?> — Free &amp; Open Source under GPL v2</span>
        <span class="cookiezu-admin-footer-sep">·</span>
        <a href="https://github.com/flyzal/CookiEzu" target="_blank" rel="noopener">⭐ Star on GitHub</a>
        <span class="cookiezu-admin-footer-sep">·</span>
        <a href="https://buymeacoffee.com/flyzal" target="_blank" rel="noopener">☕ Buy me a coffee</a>
        <span class="cookiezu-admin-footer-sep">·</span>
        <span>Made with ❤️ by <a href="https://github.com/flyzal" target="_blank" rel="noopener">flyzal</a></span>
    </div>
</div>
