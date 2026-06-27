/**
 * SetG — EDO Community Portal
 * Light front-end behaviour. No framework; progressive enhancement only.
 */
( function () {
	'use strict';

	/**
	 * Documents page: filter the list by category chips.
	 */
	function initDocFilters() {
		var root = document.querySelector( '[data-edo-docs]' );
		if ( ! root ) {
			return;
		}

		var chips = root.querySelectorAll( '[data-filter]' );
		var rows  = root.querySelectorAll( '[data-cat]' );

		chips.forEach( function ( chip ) {
			chip.addEventListener( 'click', function () {
				var filter = chip.getAttribute( 'data-filter' );

				chips.forEach( function ( c ) {
					c.classList.toggle( 'is-active', c === chip );
				} );

				rows.forEach( function ( row ) {
					var show = 'all' === filter || row.getAttribute( 'data-cat' ) === filter;
					row.style.display = show ? '' : 'none';
				} );
			} );
		} );
	}

	/**
	 * Generic accessible modal: opens from elements matching openerSel, fills
	 * itself via fill(modal, opener), closes on overlay / close button / Esc.
	 *
	 * @param {HTMLElement} modal       The modal root.
	 * @param {string}      openerSel   Selector for trigger elements.
	 * @param {Function}    fill        Populates the modal from the opener.
	 */
	function setupModal( modal, openerSel, fill ) {
		if ( ! modal ) {
			return;
		}
		var lastFocus = null;

		function open( opener ) {
			lastFocus = document.activeElement;
			fill( modal, opener );
			modal.hidden = false;
			document.body.style.overflow = 'hidden';
			var closeBtn = modal.querySelector( '.edo-modal__close' );
			if ( closeBtn ) {
				closeBtn.focus();
			}
		}

		function close() {
			modal.hidden = true;
			modal.querySelectorAll( '[data-edo-modal-body], [data-edo-anc-meta]' ).forEach( function ( el ) {
				el.innerHTML = ''; // also stops any playing video / iframe.
			} );
			document.body.style.overflow = '';
			if ( lastFocus && lastFocus.focus ) {
				lastFocus.focus();
			}
		}

		document.addEventListener( 'click', function ( e ) {
			var opener = e.target.closest( openerSel );
			if ( opener ) {
				open( opener );
				return;
			}
			if ( modal.contains( e.target ) && e.target.closest( '[data-edo-close]' ) ) {
				close();
			}
		} );

		document.addEventListener( 'keydown', function ( e ) {
			if ( 'Escape' === e.key && ! modal.hidden ) {
				close();
				return;
			}
			var opener = e.target.closest && e.target.closest( openerSel );
			if ( opener && ( 'Enter' === e.key || ' ' === e.key ) ) {
				e.preventDefault();
				open( opener );
			}
		} );
	}

	/* ---- Document preview ---- */

	function toEmbed( url ) {
		var yt = url.match( /(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([\w-]+)/ );
		if ( yt ) {
			return 'https://www.youtube.com/embed/' + yt[ 1 ];
		}
		var vm = url.match( /vimeo\.com\/(?:video\/)?(\d+)/ );
		if ( vm ) {
			return 'https://player.vimeo.com/video/' + vm[ 1 ];
		}
		return url;
	}

	function buildPreview( url, ptype ) {
		var node;
		if ( 'pdf' === ptype ) {
			node = document.createElement( 'iframe' );
			node.className = 'edo-preview-frame';
			node.src = url;
		} else if ( 'image' === ptype ) {
			node = document.createElement( 'img' );
			node.className = 'edo-preview-img';
			node.alt = '';
			node.src = url;
		} else if ( 'video' === ptype ) {
			node = document.createElement( 'video' );
			node.className = 'edo-preview-video';
			node.controls = true;
			node.src = url;
		} else if ( 'embed' === ptype ) {
			node = document.createElement( 'iframe' );
			node.className = 'edo-preview-frame';
			node.allow = 'fullscreen; picture-in-picture';
			node.src = toEmbed( url );
		} else {
			node = document.createElement( 'div' );
			node.className = 'edo-preview-none';
			node.textContent = 'Dit bestand kan niet worden voorvertoond. Gebruik “Openen” om het te bekijken of te downloaden.';
		}
		return node;
	}

	function fillDoc( modal, opener ) {
		modal.querySelector( '.edo-modal__title' ).textContent = opener.getAttribute( 'data-title' ) || '';
		modal.querySelector( '.edo-modal__open' ).setAttribute( 'href', opener.getAttribute( 'data-url' ) );
		var body = modal.querySelector( '[data-edo-modal-body]' );
		body.innerHTML = '';
		body.appendChild( buildPreview( opener.getAttribute( 'data-url' ), opener.getAttribute( 'data-ptype' ) ) );
	}

	/* ---- Announcement detail ---- */

	function fillAnnouncement( modal, opener ) {
		modal.querySelector( '.edo-modal__title' ).textContent = opener.getAttribute( 'data-title' ) || '';

		var meta = modal.querySelector( '[data-edo-anc-meta]' );
		meta.innerHTML = '';
		if ( '1' === opener.getAttribute( 'data-important' ) ) {
			var badge = document.createElement( 'span' );
			badge.className = 'edo-badge-important';
			badge.textContent = 'Belangrijk';
			meta.appendChild( badge );
		}
		var date = document.createElement( 'span' );
		date.className = 'edo-anc__date';
		date.textContent = opener.getAttribute( 'data-date' ) || '';
		meta.appendChild( date );

		var full = opener.querySelector( '.edo-anc__full' );
		modal.querySelector( '[data-edo-modal-body]' ).innerHTML = full ? full.innerHTML : '';
	}

	/* ---- Assignment interest ---- */

	function initInterest() {
		if ( typeof edoPortal === 'undefined' ) {
			return;
		}

		document.addEventListener( 'click', function ( e ) {
			var btn = e.target.closest( '[data-edo-interest]' );
			if ( ! btn || btn.disabled ) {
				return;
			}

			btn.disabled = true;

			var data = new FormData();
			data.append( 'action', 'edo_toggle_interest' );
			data.append( 'assignment', btn.getAttribute( 'data-edo-interest' ) );
			data.append( 'nonce', edoPortal.nonce );

			fetch( edoPortal.ajaxUrl, { method: 'POST', credentials: 'same-origin', body: data } )
				.then( function ( r ) {
					return r.json();
				} )
				.then( function ( res ) {
					if ( res && res.success ) {
						var on = !! res.data.interested;
						btn.classList.toggle( 'is-on', on );
						btn.setAttribute( 'aria-pressed', on ? 'true' : 'false' );
					}
				} )
				.catch( function () {} )
				.finally( function () {
					btn.disabled = false;
				} );
		} );
	}

	function init() {
		initDocFilters();
		initInterest();
		setupModal( document.getElementById( 'edo-doc-modal' ), '[data-edo-doc-open]', fillDoc );
		setupModal( document.getElementById( 'edo-anc-modal' ), '[data-edo-anc-open]', fillAnnouncement );
	}

	if ( document.readyState !== 'loading' ) {
		init();
	} else {
		document.addEventListener( 'DOMContentLoaded', init );
	}
} )();
