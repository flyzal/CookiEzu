/**
 * CookiEzu – Admin JavaScript
 */
jQuery( function ( $ ) {

  // Tabs
  $( '.cookiezu-tab' ).on( 'click', function () {
    var tab = $( this ).data( 'tab' );
    $( '.cookiezu-tab' ).removeClass( 'active' );
    $( this ).addClass( 'active' );
    $( '.cookiezu-tab-content' ).removeClass( 'active' );
    $( '#tab-' + tab ).addClass( 'active' );
  });

  // Color pickers
  $( '.cookiezu-color-picker' ).wpColorPicker();

  // Show/hide custom colour rows based on theme selection
  $( '#theme' ).on( 'change', function () {
    if ( $( this ).val() === 'custom' ) {
      $( '.cookiezu-custom-colors' ).show();
    } else {
      $( '.cookiezu-custom-colors' ).hide();
    }
  });

});
