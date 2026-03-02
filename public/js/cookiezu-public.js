/**
 * CookiEzu – Public JavaScript v1.3.0
 * License: GPL v2 or later
 *
 * v1.1.0: Rewrote show/hide to use element.style.display.
 * v1.2.0: No-flicker init, focus trap, Escape key, WCAG 2.4.3, test mode.
 * v1.3.0: Policy version re-consent trigger — if policy_version in cookie
 *         doesn't match the current setting, treat as new visitor and show
 *         banner again. Also sends policy_version to the AJAX record.
 *         RTL class applied via PHP; JS handles reopen button position for RTL.
 */
(function () {
  'use strict';

  var settings      = window.cookiezuSettings || {};
  var options       = settings.options || {};
  var COOKIE_NAME   = 'cookiezu_consent';
  var expiry        = parseInt( settings.expiryDays, 10 ) || 365;
  var testMode      = !! settings.testMode;
  var escapeClose   = settings.escapeKeyClose !== false;
  var policyVersion = settings.policyVersion || '1';
  var isRtl         = !! settings.isRtl;

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

  /* ── DOM helpers ── */
  function byId( id ) { return document.getElementById( id ); }

  function showEl( el, displayValue ) {
    if ( ! el ) return;
    el.style.display = displayValue || 'block';
  }

  function hideEl( el ) {
    if ( ! el ) return;
    el.style.display = 'none';
  }

  var FOCUSABLE = 'a[href],button:not([disabled]),input:not([disabled]),select:not([disabled]),textarea:not([disabled]),[tabindex]:not([tabindex="-1"])';

  /* ── Elements ── */
  var banner    = byId( 'cookiezu-banner' );
  var mainView  = byId( 'cookiezu-main' );
  var prefView  = byId( 'cookiezu-preferences' );
  var reopenBtn = byId( 'cookiezu-reopen' );

  if ( ! banner ) return;

  /* ── No-flicker ── */
  banner.style.visibility = 'visible';

  /* ── Determine correct display value per layout ── */
  function bannerDisplayValue() {
    return banner.classList.contains( 'cookiezu-layout-modal' ) ? 'flex' : 'block';
  }

  /* ── Apply custom theme colours ── */
  if ( options.theme === 'custom' ) {
    banner.style.setProperty( '--cz-primary', options.primary_color || '#C17B2F' );
    banner.style.setProperty( '--cz-text',    options.text_color    || '#1A1208' );
    banner.style.setProperty( '--cz-bg',      options.bg_color      || '#FEFCF8' );
  }

  if ( options.border_radius ) {
    banner.style.setProperty( '--cz-radius', options.border_radius + 'px' );
  }

  /* ── Re-open button position ── */
  if ( reopenBtn ) {
    if ( isRtl ) {
      // RTL sites: reopen on bottom-right by default
      reopenBtn.style.left  = 'auto';
      reopenBtn.style.right = '20px';
    } else if ( options.reopen_position === 'bottom-right' ) {
      reopenBtn.style.left  = 'auto';
      reopenBtn.style.right = '20px';
    }
  }

  /* ── Accept & dismiss ── */
  function accept( prefs ) {
    var consent = {
      necessary:     true,
      analytics:     !! prefs.analytics,
      marketing:     !! prefs.marketing,
      functional:    !! prefs.functional,
      policyVersion: policyVersion,
      version:       settings.version,
      date:          new Date().toISOString(),
    };

    if ( ! testMode ) {
      setCookie( COOKIE_NAME, JSON.stringify( consent ), expiry );
    }

    hideBanner();
    showEl( reopenBtn, 'flex' );

    if ( reopenBtn ) {
      requestAnimationFrame( function () { reopenBtn.focus(); } );
    }

    dispatchConsentEvent( consent );
    recordConsent( consent );
    fireGTM( consent );
    updateGA4( consent );
  }

  /* ── Show banner ── */
  function showBanner() {
    hideEl( prefView );
    showEl( mainView, 'block' );
    showEl( banner, bannerDisplayValue() );

    requestAnimationFrame( function () {
      var first = banner.querySelector( FOCUSABLE );
      if ( first ) first.focus();
    });

    if ( banner.classList.contains( 'cookiezu-layout-modal' ) ) {
      document.addEventListener( 'keydown', trapFocus );
    }
  }

  function hideBanner() {
    hideEl( banner );
    document.removeEventListener( 'keydown', trapFocus );
    document.removeEventListener( 'keydown', onEscape );
  }

  /* ── Focus trap ── */
  function trapFocus( e ) {
    if ( e.key !== 'Tab' ) return;
    var focusable = Array.from( banner.querySelectorAll( FOCUSABLE ) ).filter( function( el ) {
      return el.offsetParent !== null;
    });
    if ( ! focusable.length ) return;
    var first = focusable[0];
    var last  = focusable[ focusable.length - 1 ];
    if ( e.shiftKey ) {
      if ( document.activeElement === first ) { e.preventDefault(); last.focus(); }
    } else {
      if ( document.activeElement === last )  { e.preventDefault(); first.focus(); }
    }
  }

  /* ── Escape key ── */
  function onEscape( e ) {
    if ( e.key === 'Escape' && escapeClose ) {
      accept({ analytics: false, marketing: false, functional: false });
    }
  }

  /* ── Events ── */
  function dispatchConsentEvent( consent ) {
    try {
      document.dispatchEvent(
        new CustomEvent( 'cookiezuConsentUpdated', { detail: consent, bubbles: true } )
      );
    } catch(e) {}
  }

  /* ── AJAX record ── */
  function recordConsent( consent ) {
    if ( ! settings.ajaxUrl || testMode ) return;
    var fd = new FormData();
    fd.append( 'action',         'cookiezu_save_consent' );
    fd.append( 'nonce',          settings.nonce );
    fd.append( 'policy_version', policyVersion );
    fd.append( 'necessary', 1 );
    if ( consent.analytics )  fd.append( 'analytics',  1 );
    if ( consent.marketing )  fd.append( 'marketing',  1 );
    if ( consent.functional ) fd.append( 'functional', 1 );
    fetch( settings.ajaxUrl, { method: 'POST', body: fd } ).catch( function(){} );
  }

  /* ── GTM ── */
  function fireGTM( consent ) {
    if ( ! window.dataLayer ) return;
    window.dataLayer.push({
      event: 'cookiezu_consent_updated',
      cookiezu: consent,
    });
  }

  /* ── GA4 Consent Mode v2 ── */
  function updateGA4( consent ) {
    if ( ! window.gtag ) return;
    window.gtag( 'consent', 'update', {
      analytics_storage:     consent.analytics  ? 'granted' : 'denied',
      ad_storage:            consent.marketing  ? 'granted' : 'denied',
      functionality_storage: consent.functional ? 'granted' : 'denied',
    });
  }

  /* ── Test mode badge ── */
  function addTestModeBadge() {
    var badge = document.createElement('div');
    badge.style.cssText = [
      'position:fixed','bottom:80px','left:20px','z-index:9999999',
      'background:#C17B2F','color:#fff','font-size:11px','font-weight:700',
      'padding:4px 10px','border-radius:6px','letter-spacing:0.3px',
      'pointer-events:none','font-family:sans-serif','box-shadow:0 2px 8px rgba(0,0,0,0.2)',
    ].join(';');
    badge.textContent = '🔧 TEST MODE — Admins only';
    document.body.appendChild( badge );
  }

  /* ── Init ── */
  function init() {
    hideEl( banner );
    hideEl( reopenBtn );
    hideEl( prefView );
    showEl( mainView, 'block' );

    if ( testMode ) {
      showBanner();
      addTestModeBadge();
      if ( escapeClose ) document.addEventListener( 'keydown', onEscape );
      return;
    }

    var saved = getConsent();

    if ( saved ) {
      /*
       * v1.3.0 — Policy version check.
       * If the admin changed policy_version since the visitor last consented,
       * we treat them as a first-time visitor and show the banner again.
       * This satisfies GDPR / PDPL / PDPO re-consent requirements when
       * data processing purpose or policy changes.
       */
      var savedPolicyVersion = saved.policyVersion || saved.version || '';
      if ( savedPolicyVersion !== policyVersion ) {
        // Policy changed — clear old cookie, show banner
        setCookie( COOKIE_NAME, '', -1 );
        showBanner();
        if ( escapeClose ) document.addEventListener( 'keydown', onEscape );
        return;
      }

      dispatchConsentEvent( saved );
      showEl( reopenBtn, 'flex' );
      return;
    }

    showBanner();

    if ( escapeClose ) {
      document.addEventListener( 'keydown', onEscape );
    }

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

  /* ── Button bindings ── */
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
    prefView.setAttribute( 'aria-live', 'polite' );
    requestAnimationFrame( function () {
      var h3 = prefView.querySelector( 'h3' );
      if ( h3 ) { h3.setAttribute( 'tabindex', '-1' ); h3.focus(); }
    });
  });

  on( 'cookiezu-back', function () {
    hideEl( prefView );
    showEl( mainView, 'block' );
    requestAnimationFrame( function () {
      var btn = byId( 'cookiezu-customize' );
      if ( btn ) btn.focus();
    });
  });

  on( 'cookiezu-save-prefs', function () {
    var a = byId('cookiezu-cat-analytics');
    var m = byId('cookiezu-cat-marketing');
    var f = byId('cookiezu-cat-functional');
    accept({
      analytics:  a ? a.checked : false,
      marketing:  m ? m.checked : false,
      functional: f ? f.checked : false,
    });
  });

  if ( reopenBtn ) {
    reopenBtn.addEventListener( 'click', function () {
      hideEl( reopenBtn );
      showBanner();
      if ( escapeClose ) document.addEventListener( 'keydown', onEscape );
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
