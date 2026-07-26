/**
 * Front del bloque "Vista producto con slide vertical": la tira de miniaturas
 * cambia la pieza grande.
 *
 * Todos los recursos están ya en el DOM; cambiar de uno a otro es mover la
 * clase .is-active, así que no se recarga ninguna imagen. Si la pieza que se
 * abandona es un vídeo en marcha, se pausa: seguiría sonando oculta.
 *
 * Sin dependencias (build/view.asset.php no declara ninguna).
 */
( function () {
	'use strict';

	function initVista( root ) {
		var thumbs = root.querySelectorAll( '.llao-vista__thumb' );
		var medias = root.querySelectorAll( '.llao-vista__media' );

		if ( ! thumbs.length || ! medias.length ) {
			return;
		}

		function mostrar( index ) {
			medias.forEach( function ( media ) {
				var activo = media.getAttribute( 'data-index' ) === String( index );

				if ( ! activo ) {
					var video = media.querySelector( 'video' );
					if ( video && ! video.paused ) {
						video.pause();
					}
				}

				media.classList.toggle( 'is-active', activo );
			} );

			thumbs.forEach( function ( thumb ) {
				var activo = thumb.getAttribute( 'data-index' ) === String( index );
				thumb.classList.toggle( 'is-active', activo );
				thumb.setAttribute( 'aria-pressed', activo ? 'true' : 'false' );
			} );
		}

		thumbs.forEach( function ( thumb ) {
			thumb.addEventListener( 'click', function () {
				mostrar( thumb.getAttribute( 'data-index' ) );
			} );
		} );
	}

	function initAll() {
		document.querySelectorAll( '.wp-block-llaollao-vista-producto' ).forEach( initVista );
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', initAll );
	} else {
		initAll();
	}
} )();
