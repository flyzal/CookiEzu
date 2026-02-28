<?php
/**
 * Consent log page view.
 *
 * @package CookiEzu
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'admin_head', function() { ?>
<style>
#wpcontent, #wpbody-content { background: #FBF7F0 !important; }
#wpcontent { padding-left: 20px !important; }
</style>
<?php } );

global $wpdb;
$table = $wpdb->prefix . 'cookiezu_consent_log';
$total = $wpdb->get_var( "SELECT COUNT(*) FROM $table" );
$rows  = $wpdb->get_results( "SELECT * FROM $table ORDER BY consent_date DESC LIMIT 200" );
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

    <?php if ( empty( $rows ) ) : ?>
        <div class="cookiezu-card"><p><?php esc_html_e( 'No consent records yet. Make sure "Record Consent" is enabled in Settings.', 'cookiezu' ); ?></p></div>
    <?php else : ?>
    <div class="cookiezu-card">
        <table class="widefat fixed striped cookiezu-log-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th><?php esc_html_e( 'Date', 'cookiezu' ); ?></th>
                    <th><?php esc_html_e( 'IP Address', 'cookiezu' ); ?></th>
                    <th><?php esc_html_e( 'Necessary', 'cookiezu' ); ?></th>
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
                    <td><?php echo esc_html( substr( $row->ip_address, 0, -4 ) . '****' ); // Partial anonymisation ?></td>
                    <td><?php echo $row->necessary ? '✅' : '❌'; ?></td>
                    <td><?php echo $row->analytics ? '✅' : '❌'; ?></td>
                    <td><?php echo $row->marketing ? '✅' : '❌'; ?></td>
                    <td><?php echo $row->functional ? '✅' : '❌'; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
