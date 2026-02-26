/**
 * CookiEzu – Public JavaScript
 * License: GPL v2 or later
 */
(function () {
  'use strict';

  var settings = window.cookiezuSettings || {};
  var options  = settings.options || {};
  var COOKIE_NAME = 'cookiezu_consent';
  var expiry   = parseInt( settings.expiryDays, 10 ) || 365;

  /* -----------------------------------------------
     Cookie helpers
  ----------------------------------------------- */
  function setCookie( name, value, days ) {
    var d = new Date();
    d.setTime( d.getTime() + days * 864e5 );
    document.cookie = name + '=' + encodeURIComponent( value ) +
      ';expires=' + d.toUTCString() + ';path=/;SameSite=Lax';
  }

  function getCookie( name ) {
    var match = document.cookie.match( new RegExp( '(?:^|;\\s*)' + name + '=([^;]*)' ) );
    return match ? decodeURIComponent( match[1] ) : null;
  }

  function getConsent() {
    var raw = getCookie( COOKIE_NAME );
    if ( ! raw ) return null;
    try { return JSON.parse( raw ); } catch (e) { return null; }
  }

  /* -----------------------------------------------
     DOM helpers
  ----------------------------------------------- */
  function $( id ) { return document.getElementById( id ); }

  function show( el ) { if ( el ) el.style.display = ''; }
  function hide( el ) { if ( el ) el.style.display = 'none'; }

  /* -----------------------------------------------
     Banner elements
  ----------------------------------------------- */
  var banner      = $( 'cookiezu-banner' );
  var mainView    = $( 'cookiezu-main' );
  var prefView    = $( 'cookiezu-preferences' );
  var reopenBtn   = $( 'cookiezu-reopen' );

  if ( ! banner ) return;

  /* -----------------------------------------------
     Apply custom colours (theme = custom)
  ----------------------------------------------- */
  if ( options.theme === 'custom' ) {
    banner.style.setProperty( '--cz-primary',  options.primary_color || '#3b82f6' );
    banner.style.setProperty( '--cz-text',     options.text_color    || '#1f2937' );
    banner.style.setProperty( '--cz-bg',       options.bg_color      || '#ffffff' );
  }

  /* Apply border-radius */
  if ( options.border_radius ) {
    banner.style.setProperty( '--cz-radius', options.border_radius + 'px' );
    if ( reopenBtn ) reopenBtn.style.setProperty( '--cz-radius', options.border_radius + 'px' );
  }

  /* -----------------------------------------------
     Accept consent
  ----------------------------------------------- */
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
    hide( banner );
    show( reopenBtn );

    // Fire event
    dispatchConsentEvent( consent );

    // Record to DB
    if ( settings.ajaxUrl ) {
      var fd = new FormData();
      fd.append( 'action',   'cookiezu_save_consent' );
      fd.append( 'nonce',    settings.nonce );
      fd.append( 'necessary',  1 );
      if ( consent.analytics )  fd.append( 'analytics',  1 );
      if ( consent.marketing )  fd.append( 'marketing',  1 );
      if ( consent.functional ) fd.append( 'functional', 1 );
      fetch( settings.ajaxUrl, { method: 'POST', body: fd } );
    }

    // GTM dataLayer
    if ( window.dataLayer && options.gtm_id ) {
      window.dataLayer.push({
        event: 'cookiezu_consent_updated',
        cookiezu: consent,
      });
    }

    // GA4 consent mode v2
    if ( window.gtag ) {
      gtag( 'consent', 'update', {
        analytics_storage:  consent.analytics  ? 'granted' : 'denied',
        ad_storage:         consent.marketing  ? 'granted' : 'denied',
        functionality_storage: consent.functional ? 'granted' : 'denied',
      });
    }
  }

  /* -----------------------------------------------
     Custom event
  ----------------------------------------------- */
  function dispatchConsentEvent( consent ) {
    var evt = new CustomEvent( 'cookiezuConsentUpdated', { detail: consent, bubbles: true } );
    document.dispatchEvent( evt );
  }

  /* -----------------------------------------------
     Init – show or restore
  ----------------------------------------------- */
  function init() {
    var saved = getConsent();

    if ( saved ) {
      // Consent already given – restore & fire events silently
      dispatchConsentEvent( saved );
      show( reopenBtn );
      return;
    }

    show( banner );

    // Auto-accept
    var autodays = parseInt( options.auto_accept_days, 10 );
    if ( autodays > 0 ) {
      var firstSeen = getCookie( 'cookiezu_first_seen' );
      if ( ! firstSeen ) {
        setCookie( 'cookiezu_first_seen', new Date().toISOString(), autodays );
      } else {
        var diff = ( Date.now() - new Date( firstSeen ).getTime() ) / 864e5;
        if ( diff >= autodays ) {
          accept({ analytics: true, marketing: true, functional: true });
          return;
        }
      }
    }
  }

  /* -----------------------------------------------
     Event listeners
  ----------------------------------------------- */
  function on( id, evt, fn ) {
    var el = $( id );
    if ( el ) el.addEventListener( evt, fn );
  }

  on( 'cookiezu-accept-all', 'click', function () {
    accept({ analytics: true, marketing: true, functional: true });
  });

  on( 'cookiezu-accept-necessary', 'click', function () {
    accept({ analytics: false, marketing: false, functional: false });
  });

  on( 'cookiezu-customize', 'click', function () {
    hide( mainView );
    show( prefView );
  });

  on( 'cookiezu-back', 'click', function () {
    hide( prefView );
    show( mainView );
  });

  on( 'cookiezu-save-prefs', 'click', function () {
    accept({
      analytics:  $( 'cookiezu-cat-analytics' )  ? $( 'cookiezu-cat-analytics' ).checked  : false,
      marketing:  $( 'cookiezu-cat-marketing' )   ? $( 'cookiezu-cat-marketing' ).checked  : false,
      functional: $( 'cookiezu-cat-functional' )  ? $( 'cookiezu-cat-functional' ).checked : false,
    });
  });

  if ( reopenBtn ) {
    reopenBtn.addEventListener( 'click', function () {
      hide( reopenBtn );
      show( banner );
      show( mainView );
      hide( prefView );
    });
  }

  /* -----------------------------------------------
     Load GA4 if configured
  ----------------------------------------------- */
  function loadGA4( id ) {
    if ( ! id || document.querySelector( 'script[src*="googletagmanager.com/gtag"]' ) ) return;

    // Default deny until consent
    window.dataLayer = window.dataLayer || [];
    function gtag() { window.dataLayer.push( arguments ); }
    window.gtag = gtag;
    gtag( 'js', new Date() );
    gtag( 'consent', 'default', {
      analytics_storage:    'denied',
      ad_storage:           'denied',
      functionality_storage:'denied',
      wait_for_update:      500,
    });
    gtag( 'config', id );

    var s = document.createElement( 'script' );
    s.async = true;
    s.src = 'https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent( id );
    document.head.appendChild( s );
  }

  if ( options.ga_id ) loadGA4( options.ga_id );

  /* Run */
  if ( document.readyState === 'loading' ) {
    document.addEventListener( 'DOMContentLoaded', init );
  } else {
    init();
  }

})();
