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

						var card = btn.closest( '.edo-acard' );
						var likeN = card && card.querySelector( '.edo-likecount__n' );
						if ( likeN && typeof res.data.count !== 'undefined' ) {
							likeN.textContent = res.data.count;
						}
					}
				} )
				.catch( function () {} )
				.finally( function () {
					btn.disabled = false;
				} );
		} );
	}

	/* ---- Comments / questions ---- */

	function initComments() {
		var modal = document.getElementById( 'edo-cmt-modal' );
		if ( ! modal || typeof edoPortal === 'undefined' ) {
			return;
		}

		var titleEl   = modal.querySelector( '#edo-cmt-title' );
		var listEl    = modal.querySelector( '[data-edo-cmt-list]' );
		var form      = modal.querySelector( '[data-edo-cmt-form]' );
		var input     = modal.querySelector( '[data-edo-cmt-input]' );
		var replyBar  = modal.querySelector( '[data-edo-cmt-replyto]' );
		var replyName = modal.querySelector( '[data-edo-cmt-replyname]' );
		var current   = null;
		var replyTo   = 0;
		var lastFocus = null;

		function setEmpty() {
			listEl.innerHTML = '<p class="edo-cmt-empty">' + 'Nog geen reacties. Stel de eerste vraag.' + '</p>';
		}

		function clearReply() {
			replyTo = 0;
			if ( replyBar ) {
				replyBar.hidden = true;
			}
			if ( replyName ) {
				replyName.textContent = '';
			}
		}

		function load() {
			listEl.innerHTML = '<p class="edo-cmt-empty">…</p>';
			var data = new FormData();
			data.append( 'action', 'edo_get_comments' );
			data.append( 'assignment', current );
			data.append( 'nonce', edoPortal.nonce );
			fetch( edoPortal.ajaxUrl, { method: 'POST', credentials: 'same-origin', body: data } )
				.then( function ( r ) { return r.json(); } )
				.then( function ( res ) {
					if ( res && res.success && res.data.count ) {
						listEl.innerHTML = res.data.html;
						listEl.scrollTop = listEl.scrollHeight;
					} else {
						setEmpty();
					}
				} )
				.catch( setEmpty );
		}

		function open( id, title ) {
			current = id;
			lastFocus = document.activeElement;
			titleEl.textContent = title || '';
			input.value = '';
			clearReply();
			modal.hidden = false;
			document.body.style.overflow = 'hidden';
			load();
		}

		function close() {
			modal.hidden = true;
			listEl.innerHTML = '';
			document.body.style.overflow = '';
			clearReply();
			if ( lastFocus && lastFocus.focus ) {
				lastFocus.focus();
			}
		}

		function updateCount( id, count ) {
			var badge = document.querySelector( '[data-edo-cmt-open="' + id + '"] .edo-cmtbtn__count' );
			if ( badge ) {
				badge.textContent = '(' + count + ')';
			}
		}

		document.addEventListener( 'click', function ( e ) {
			var opener = e.target.closest( '[data-edo-cmt-open]' );
			if ( opener ) {
				open( opener.getAttribute( 'data-edo-cmt-open' ), opener.getAttribute( 'data-title' ) );
				return;
			}
			if ( ! modal.contains( e.target ) ) {
				return;
			}
			if ( e.target.closest( '[data-edo-close]' ) ) {
				close();
				return;
			}
			var replyBtn = e.target.closest( '.edo-cmt-reply' );
			if ( replyBtn ) {
				replyTo = replyBtn.getAttribute( 'data-reply' );
				replyName.textContent = replyBtn.getAttribute( 'data-name' );
				replyBar.hidden = false;
				input.focus();
				return;
			}
			if ( e.target.closest( '[data-edo-cmt-replycancel]' ) ) {
				clearReply();
			}
		} );

		document.addEventListener( 'keydown', function ( e ) {
			if ( 'Escape' === e.key && ! modal.hidden ) {
				close();
			}
		} );

		form.addEventListener( 'submit', function ( e ) {
			e.preventDefault();
			var content = input.value.trim();
			if ( ! content || ! current ) {
				return;
			}
			var btn = form.querySelector( 'button[type="submit"]' );
			btn.disabled = true;

			var data = new FormData();
			data.append( 'action', 'edo_post_comment' );
			data.append( 'assignment', current );
			data.append( 'content', content );
			data.append( 'parent', replyTo || 0 );
			data.append( 'nonce', edoPortal.nonce );

			fetch( edoPortal.ajaxUrl, { method: 'POST', credentials: 'same-origin', body: data } )
				.then( function ( r ) { return r.json(); } )
				.then( function ( res ) {
					if ( res && res.success ) {
						var empty = listEl.querySelector( '.edo-cmt-empty' );
						if ( empty ) {
							empty.remove();
						}
						var target = listEl;
						if ( res.data.parent ) {
							var parentEl = listEl.querySelector( '[data-id="' + res.data.parent + '"] .edo-cmt__children' );
							if ( parentEl ) {
								target = parentEl;
							}
						}
						target.insertAdjacentHTML( 'beforeend', res.data.html );
						input.value = '';
						clearReply();
						listEl.scrollTop = listEl.scrollHeight;
						updateCount( current, res.data.count );
					}
				} )
				.catch( function () {} )
				.finally( function () { btn.disabled = false; } );
		} );
	}

	function init() {
		initDocFilters();
		initInterest();
		initComments();
		setupModal( document.getElementById( 'edo-doc-modal' ), '[data-edo-doc-open]', fillDoc );
		setupModal( document.getElementById( 'edo-anc-modal' ), '[data-edo-anc-open]', fillAnnouncement );
	}

	if ( document.readyState !== 'loading' ) {
		init();
	} else {
		document.addEventListener( 'DOMContentLoaded', init );
	}
} )();
