/**
 * SetG — admin: media picker for the document "Bestand" field.
 */
( function ( $ ) {
	'use strict';

	$( function () {
		$( '.edo-media-field' ).each( function () {
			var $field = $( this );
			var frame;

			$field.on( 'click', '.edo-media-pick', function ( e ) {
				e.preventDefault();

				if ( frame ) {
					frame.open();
					return;
				}

				frame = wp.media( {
					title: 'Selecteer bestand',
					button: { text: 'Gebruiken' },
					multiple: false
				} );

				frame.on( 'select', function () {
					var att = frame.state().get( 'selection' ).first().toJSON();
					$field.find( '.edo-media-id' ).val( att.id );
					$field.find( '.edo-media-preview' ).text( att.filename || att.title );
				} );

				frame.open();
			} );

			$field.on( 'click', '.edo-media-remove', function ( e ) {
				e.preventDefault();
				$field.find( '.edo-media-id' ).val( '' );
				$field.find( '.edo-media-preview' ).text( 'Geen bestand gekozen' );
			} );
		} );
	} );
} )( jQuery );
