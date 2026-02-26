/**
 * CookiEzu – Public JavaScript
 * License: GPL v2 or later
 *
 * Uses element.style.display directly so inline styles always
 * override any CSS rule regardless of specificity or !important.
 */
(function () {
  'use strict';

  var settings    = window.cookiezuSettings || {};
  var options     = settings.options || {};
  var COOKIE_NAME = 'cookiezu_consent';
  var expiry      = parseInt( settings.expiryDays, 10 ) || 365;

  /* ── Cookie helpers ── */
  function setCookie( name, value, days ) {
    var d = new Date();
    d.setTime( d.getTime() + days * 864e5 );
    document.cookie = name + '=' + encodeURIComponent( value )
      + ';expires=' + d.toUTCString() + ';path=/;SameSite=Lax';
  }

  function getCookie( name ) {
    var m = document.cookie.match( new RegExp( '(?:^|;\\s*)' + name + '=([^;]*)' ) );
    return m ? decodeURIComponent( m[1] ) : null;
  }

  function getConsent() {
    var raw = getCookie( COOKIE_NAME );
    if ( ! raw ) return null;
    try { return JSON.parse( raw ); } catch (e) { return null; }
  }

  /* ── DOM helpers ──
   * Inline style.display ALWAYS wins over any CSS rule including !important.
   * This is the only reliable way to show/hide elements that have
   * display values forced via CSS (e.g. display:flex !important on modal).
   */
  function byId( id ) { return document.getElementById( id ); }

  function showEl( el, displayValue ) {
    if ( ! el ) return;
    el.style.display = displayValue || 'block';
  }

  function hideEl( el ) {
    if ( ! el ) return;
    el.style.display = 'none';
  }

  /* ── Elements ── */
  var banner    = byId( 'cookiezu-banner' );
  var mainView  = byId( 'cookiezu-main' );
  var prefView  = byId( 'cookiezu-preferences' );
  var reopenBtn = byId( 'cookiezu-reopen' );

  if ( ! banner ) return;

  /* ── Determine correct display value per layout ── */
  function bannerDisplayValue() {
    if ( banner.classList.contains( 'cookiezu-layout-modal' ) ) {
      return 'flex';
    }
    return 'block';
  }

  /* ── Apply custom theme colours ── */
  if ( options.theme === 'custom' ) {
    banner.style.setProperty( '--cz-primary',  options.primary_color || '#C17B2F' );
    banner.style.setProperty( '--cz-text',     options.text_color    || '#1A1208' );
    banner.style.setProperty( '--cz-bg',       options.bg_color      || '#FEFCF8' );
  }

  if ( options.border_radius ) {
    banner.style.setProperty( '--cz-radius', options.border_radius + 'px' );
  }

  /* ── Accept & dismiss ── */
  function accept( prefs ) {
    var consent = {
      necessary:  true,
      analytics:  !! prefs.analytics,
      marketing:  !! prefs.marketing,
      functional: !! prefs.functional,
      version:    settings.version,
      date:       new Date().toISOString(),
    };

    setCookie( COOKIE_NAME, JSON.stringify( consent ), expiry );

    hideEl( banner );
    showEl( reopenBtn, 'flex' );

    dispatchConsentEvent( consent );
    recordConsent( consent );
    fireGTM( consent );
    updateGA4( consent );
  }

  /* ── Fire DOM event ── */
  function dispatchConsentEvent( consent ) {
    try {
      document.dispatchEvent(
        new CustomEvent( 'cookiezuConsentUpdated', { detail: consent, bubbles: true } )
      );
    } catch(e) {}
  }

  /* ── AJAX record ── */
  function recordConsent( consent ) {
    if ( ! settings.ajaxUrl ) return;
    var fd = new FormData();
    fd.append( 'action',    'cookiezu_save_consent' );
    fd.append( 'nonce',     settings.nonce );
    fd.append( 'necessary', 1 );
    if ( consent.analytics )  fd.append( 'analytics',  1 );
    if ( consent.marketing )  fd.append( 'marketing',  1 );
    if ( consent.functional ) fd.append( 'functional', 1 );
    fetch( settings.ajaxUrl, { method: 'POST', body: fd } ).catch( function(){} );
  }

  /* ── GTM dataLayer ── */
  function fireGTM( consent ) {
    if ( ! window.dataLayer ) return;
    window.dataLayer.push({
      event: 'cookiezu_consent_updated',
      cookiezu: consent,
    });
  }

  /* ── GA4 Consent Mode v2 update ── */
  function updateGA4( consent ) {
    if ( ! window.gtag ) return;
    window.gtag( 'consent', 'update', {
      analytics_storage:     consent.analytics  ? 'granted' : 'denied',
      ad_storage:            consent.marketing  ? 'granted' : 'denied',
      functionality_storage: consent.functional ? 'granted' : 'denied',
    });
  }

  /* ── Init ── */
  function init() {
    /* Start everything hidden via inline style */
    hideEl( banner );
    hideEl( reopenBtn );
    hideEl( prefView );
    showEl( mainView, 'block' );

    var saved = getConsent();
    if ( saved ) {
      dispatchConsentEvent( saved );
      showEl( reopenBtn, 'flex' );
      return;
    }

    showEl( banner, bannerDisplayValue() );

    /* Auto-accept after N days */
    var autodays = parseInt( options.auto_accept_days, 10 ) || 0;
    if ( autodays > 0 ) {
      var firstSeen = getCookie( 'cookiezu_first_seen' );
      if ( ! firstSeen ) {
        setCookie( 'cookiezu_first_seen', new Date().toISOString(), autodays );
      } else {
        var diff = ( Date.now() - new Date( firstSeen ).getTime() ) / 864e5;
        if ( diff >= autodays ) {
          accept({ analytics: true, marketing: true, functional: true });
        }
      }
    }
  }

  /* ── Bind buttons ── */
  function on( id, fn ) {
    var el = byId( id );
    if ( el ) el.addEventListener( 'click', fn );
  }

  on( 'cookiezu-accept-all', function () {
    accept({ analytics: true, marketing: true, functional: true });
  });

  on( 'cookiezu-accept-necessary', function () {
    accept({ analytics: false, marketing: false, functional: false });
  });

  on( 'cookiezu-customize', function () {
    hideEl( mainView );
    showEl( prefView, 'block' );
  });

  on( 'cookiezu-back', function () {
    hideEl( prefView );
    showEl( mainView, 'block' );
  });

  on( 'cookiezu-save-prefs', function () {
    var analytics  = byId( 'cookiezu-cat-analytics' );
    var marketing  = byId( 'cookiezu-cat-marketing' );
    var functional = byId( 'cookiezu-cat-functional' );
    accept({
      analytics:  analytics  ? analytics.checked  : false,
      marketing:  marketing  ? marketing.checked  : false,
      functional: functional ? functional.checked : false,
    });
  });

  if ( reopenBtn ) {
    reopenBtn.addEventListener( 'click', function () {
      hideEl( reopenBtn );
      hideEl( prefView );
      showEl( mainView, 'block' );
      showEl( banner, bannerDisplayValue() );
    });
  }

  /* ── Load GA4 ── */
  function loadGA4( id ) {
    if ( ! id || document.querySelector( 'script[src*="googletagmanager.com/gtag"]' ) ) return;
    window.dataLayer = window.dataLayer || [];
    function gtag(){ window.dataLayer.push( arguments ); }
    window.gtag = gtag;
    gtag( 'js', new Date() );
    gtag( 'consent', 'default', {
      analytics_storage:     'denied',
      ad_storage:            'denied',
      functionality_storage: 'denied',
      wait_for_update:       500,
    });
    gtag( 'config', id );
    var s   = document.createElement( 'script' );
    s.async = true;
    s.src   = 'https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent( id );
    document.head.appendChild( s );
  }

  if ( options.ga_id ) loadGA4( options.ga_id );

  /* ── Boot ── */
  if ( document.readyState === 'loading' ) {
    document.addEventListener( 'DOMContentLoaded', init );
  } else {
    init();
  }

})();
