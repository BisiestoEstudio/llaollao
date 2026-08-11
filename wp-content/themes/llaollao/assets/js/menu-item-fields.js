/**
 * Selector de imagen/galería para los campos custom de los items de menú
 * (Apariencia → Menús). Vanilla JS + wp.media (el tema no usa node_modules).
 * Los items añadidos por AJAX (al marcar checkboxes y pulsar "Añadir al menú")
 * disparan el evento jQuery "menu-item-added", así que reenganchamos ahí.
 */
( function () {
	function renderPreview( field, attachments ) {
		var preview = field.querySelector( '.llao-media-preview' );
		preview.innerHTML = '';
		attachments.forEach( function ( attachment ) {
			var img = document.createElement( 'img' );
			var sizes = attachment.sizes || {};
			img.src = ( sizes.thumbnail && sizes.thumbnail.url ) || attachment.url;
			preview.appendChild( img );
		} );
	}

	function setup( field ) {
		if ( field._llaoReady ) {
			return;
		}
		field._llaoReady = true;

		var multiple   = 'gallery' === field.dataset.kind;
		var input      = field.querySelector( '.llao-media-value' );
		var selectBtn  = field.querySelector( '.llao-media-select' );
		var removeBtn  = field.querySelector( '.llao-media-remove' );

		selectBtn.addEventListener( 'click', function ( e ) {
			e.preventDefault();

			var frame = wp.media( {
				title: multiple ? bisiestoMenuItemFields.selectGalleryTitle : bisiestoMenuItemFields.selectImageTitle,
				multiple: multiple ? 'add' : false,
				library: { type: 'image' },
			} );

			frame.on( 'open', function () {
				var ids = input.value ? input.value.split( ',' ).filter( Boolean ) : [];
				if ( ! ids.length ) {
					return;
				}
				var selection = frame.state().get( 'selection' );
				ids.forEach( function ( id ) {
					var attachment = wp.media.attachment( id );
					attachment.fetch();
					selection.add( attachment );
				} );
			} );

			frame.on( 'select', function () {
				var selection = frame.state().get( 'selection' ).toJSON();
				var ids = selection.map( function ( attachment ) {
					return attachment.id;
				} );
				input.value = ids.join( ',' );
				renderPreview( field, selection );
				removeBtn.style.display = ids.length ? '' : 'none';
			} );

			frame.open();
		} );

		removeBtn.addEventListener( 'click', function ( e ) {
			e.preventDefault();
			input.value = '';
			field.querySelector( '.llao-media-preview' ).innerHTML = '';
			removeBtn.style.display = 'none';
		} );
	}

	function init() {
		document.querySelectorAll( '.llao-media-field' ).forEach( setup );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}

	if ( window.jQuery ) {
		window.jQuery( document ).on( 'menu-item-added', init );
	}
} )();
