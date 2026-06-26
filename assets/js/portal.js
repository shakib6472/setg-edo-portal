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

	if ( document.readyState !== 'loading' ) {
		initDocFilters();
	} else {
		document.addEventListener( 'DOMContentLoaded', initDocFilters );
	}
} )();
