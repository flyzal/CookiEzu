/**
 * CookiEzu – Admin JavaScript v1.3.0
 *
 * New in v1.2.1:
 *  - Live banner preview (layout, position, theme, custom colors, border-radius)
 *
 * New in v1.3.0:
 *  - Live preview reflects RTL direction when Arabic language is selected
 *  - Language dropdown change triggers preview refresh
 */
jQuery( function ( $ ) {

  /* ══════════════════
     TABS
  ══════════════════ */
  $( '.cookiezu-tab' ).on( 'click', function () {
    var tab = $( this ).data( 'tab' );
    $( '.cookiezu-tab' ).removeClass( 'active' );
    $( this ).addClass( 'active' );
    $( '.cookiezu-tab-content' ).removeClass( 'active' );
    $( '#tab-' + tab ).addClass( 'active' );
    if ( tab === 'design' ) renderPreview();
  });

  /* ══════════════════
     COLOR PICKERS
  ══════════════════ */
  $( '.cookiezu-color-picker' ).wpColorPicker({
    change: function() { setTimeout( renderPreview, 50 ); },
    clear:  function() { setTimeout( renderPreview, 50 ); },
  });

  $( '#theme' ).on( 'change', function () {
    if ( $( this ).val() === 'custom' ) {
      $( '.cookiezu-custom-colors' ).show();
    } else {
      $( '.cookiezu-custom-colors' ).hide();
    }
    renderPreview();
  });

  /* ══════════════════
     LIVE PREVIEW ENGINE
  ══════════════════ */
  var previewContainer = $( '#czPreviewBanner' );

  function getPreviewState() {
    return {
      language:      $( '#banner_language' ).val() || 'en',
      layout:        $( '#layout' ).val()        || 'bar',
      position:      $( '#position' ).val()      || 'bottom',
      theme:         $( '#theme' ).val()         || 'light',
      radius:        parseInt( $( '#border_radius' ).val() ) || 10,
      primaryColor:  $( 'input[name="cookiezu[primary_color]"]' ).val() || '#C17B2F',
      textColor:     $( 'input[name="cookiezu[text_color]"]' ).val()    || '#1A1208',
      bgColor:       $( 'input[name="cookiezu[bg_color]"]' ).val()      || '#FEFCF8',
      title:         $( '#banner_title' ).val()  || 'We value your privacy 🍪',
      message:       $( '#banner_message' ).val()|| 'We use cookies to enhance your experience.',
      acceptText:    $( '#accept_all_text' ).val()       || 'Accept All',
      necessaryText: $( '#accept_necessary_text' ).val() || 'Necessary Only',
      customizeText: $( '#customize_text' ).val()        || 'Customize',
    };
  }

  /* Theme colour palettes */
  var THEMES = {
    light:  { bg: '#FEFCF8', surface: '#FBF7F0', text: '#1A1208', muted: 'rgba(26,18,8,0.55)', border: 'rgba(26,18,8,0.10)', primary: '#C17B2F' },
    dark:   { bg: '#1A1208', surface: '#231A0D', text: '#FEFCF8', muted: 'rgba(254,252,248,0.60)', border: 'rgba(254,252,248,0.10)', primary: '#C17B2F' },
    custom: null,
  };

  function renderPreview() {
    var s = getPreviewState();
    var t = ( s.theme === 'custom' )
      ? { bg: s.bgColor, surface: s.bgColor, text: s.textColor, muted: s.textColor, border: 'rgba(128,128,128,0.2)', primary: s.primaryColor }
      : THEMES[ s.theme ] || THEMES.light;

    var r = s.radius + 'px';

    /* Shared button styles */
    var btnBase = [
      'display:inline-flex', 'align-items:center', 'justify-content:center',
      'padding:5px 10px', 'border-radius:' + r,
      'font-size:9.5px', 'font-weight:700', 'cursor:default',
      'border:1.5px solid transparent', 'line-height:1', 'white-space:nowrap',
      'font-family:inherit',
    ].join(';');
    var btnPrimary    = btnBase + ';background:' + t.primary + ';color:#fff;border-color:' + t.primary + ';box-shadow:0 1px 6px rgba(193,123,47,.3)';
    var btnSecondary  = btnBase + ';background:transparent;color:' + t.text + ';border-color:' + t.border;
    var btnLink       = 'display:inline-flex;align-items:center;font-size:8.5px;background:none;border:none;color:' + t.muted + ';text-decoration:underline;padding:3px 2px;cursor:default;font-family:inherit';

    /* Accent bar on top */
    var accentBar = '<div style="position:absolute;top:0;left:0;right:0;height:2px;background:linear-gradient(90deg,' + t.primary + ',#E8A84A)"></div>';

    var html = '';

    /* ── BAR ── */
    if ( s.layout === 'bar' ) {
      var posStyle = ( s.position === 'top' )
        ? 'position:absolute;top:0;left:0;right:0;'
        : 'position:absolute;bottom:0;left:0;right:0;';
      html = '<div style="' + posStyle + 'background:' + t.bg + ';border-top:1.5px solid ' + t.border + ';padding:10px 12px;display:flex;align-items:center;gap:10px;flex-wrap:wrap;box-shadow:0 -3px 16px rgba(0,0,0,.08);position:absolute">' +
        accentBar +
        '<div style="flex:1;min-width:80px;">' +
          '<div style="font-weight:700;font-size:10px;color:' + t.text + ';margin-bottom:2px;">' + escHtml( s.title ) + '</div>' +
          '<div style="font-size:9px;color:' + t.muted + ';">' + escHtml( s.message.substring(0,60) ) + '…</div>' +
        '</div>' +
        '<div style="display:flex;gap:5px;flex-wrap:wrap;">' +
          '<button style="' + btnPrimary   + '">' + escHtml( s.acceptText )    + '</button>' +
          '<button style="' + btnSecondary + '">' + escHtml( s.necessaryText ) + '</button>' +
          '<button style="' + btnLink      + '">' + escHtml( s.customizeText ) + '</button>' +
        '</div>' +
      '</div>';
    }

    /* ── BOX ── */
    else if ( s.layout === 'box' ) {
      var boxPos = ( s.position === 'bottom-right' || s.position === 'top' )
        ? 'right:10px;bottom:10px;'
        : 'left:10px;bottom:10px;';
      html = '<div style="position:absolute;' + boxPos + 'width:140px;background:' + t.bg + ';border-radius:' + Math.max(parseInt(r),4) + 'px;padding:14px;border:1px solid ' + t.border + ';box-shadow:0 10px 32px rgba(0,0,0,.18);overflow:hidden">' +
        accentBar +
        '<div style="font-size:16px;margin-bottom:6px;">🍪</div>' +
        '<div style="font-weight:700;font-size:10px;color:' + t.text + ';margin-bottom:3px;">' + escHtml( s.title ) + '</div>' +
        '<div style="font-size:8.5px;color:' + t.muted + ';line-height:1.4;margin-bottom:10px;">' + escHtml( s.message.substring(0,55) ) + '…</div>' +
        '<div style="display:flex;flex-direction:column;gap:4px;">' +
          '<button style="' + btnPrimary + ';width:100%;justify-content:center">'   + escHtml( s.acceptText )    + '</button>' +
          '<button style="' + btnSecondary + ';width:100%;justify-content:center">' + escHtml( s.necessaryText ) + '</button>' +
          '<button style="' + btnLink + ';text-align:center;width:100%;justify-content:center">' + escHtml( s.customizeText ) + '</button>' +
        '</div>' +
      '</div>';
    }

    /* ── MODAL ── */
    else if ( s.layout === 'modal' ) {
      html = '<div style="position:absolute;inset:0;background:rgba(26,18,8,0.55);display:flex;align-items:center;justify-content:center;backdrop-filter:blur(2px);">' +
        '<div style="background:' + t.bg + ';border-radius:' + Math.max(parseInt(r)+4,8) + 'px;padding:18px;width:190px;border:1px solid ' + t.border + ';box-shadow:0 20px 50px rgba(0,0,0,.35);position:relative;overflow:hidden">' +
          accentBar +
          '<div style="font-size:18px;margin-bottom:8px;">🍪</div>' +
          '<div style="font-weight:700;font-size:10.5px;color:' + t.text + ';margin-bottom:4px;">' + escHtml( s.title ) + '</div>' +
          '<div style="font-size:8.5px;color:' + t.muted + ';line-height:1.45;margin-bottom:12px;">' + escHtml( s.message.substring(0,70) ) + '…</div>' +
          '<div style="display:flex;flex-direction:column;gap:4px;">' +
            '<button style="' + btnPrimary   + ';width:100%;justify-content:center">' + escHtml( s.acceptText )    + '</button>' +
            '<button style="' + btnSecondary + ';width:100%;justify-content:center">' + escHtml( s.necessaryText ) + '</button>' +
            '<button style="' + btnLink      + ';text-align:center;width:100%;justify-content:center">' + escHtml( s.customizeText ) + '</button>' +
          '</div>' +
        '</div>' +
      '</div>';
    }

    // Apply RTL direction on preview wrapper
    if ( s.language === 'ar' ) {
      previewContainer.attr( 'dir', 'rtl' );
    } else {
      previewContainer.removeAttr( 'dir' );
    }
    previewContainer.html( html );
  }

  function escHtml( str ) {
    return String(str)
      .replace(/&/g,'&amp;')
      .replace(/</g,'&lt;')
      .replace(/>/g,'&gt;')
      .replace(/"/g,'&quot;');
  }

  /* Trigger preview on any design control change */
  $( '#banner_language' ).on( 'change', function() { renderPreview(); });

  $( document ).on( 'change input', '.cz-preview-trigger', function() {
    renderPreview();
  });

  /* Also re-render when content tab text changes */
  $( document ).on( 'input', '#banner_title, #banner_message, #accept_all_text, #accept_necessary_text, #customize_text', function() {
    renderPreview();
  });

  /* Initial render */
  renderPreview();

});
