/**
 * Front del bloque "Carta": filtrado de las cards por la taxonomía "tipo".
 *
 * Una etiqueta cada vez (comportamiento de radio): al pulsar una se desactiva
 * la anterior, y "Todos" (data-filter vacío) devuelve el mosaico completo. No
 * hay peticiones: las cards ya están en el HTML y solo se ocultan con una
 * clase, así que el multi-columna recoloca lo que queda a la vista.
 *
 * Sin dependencias (build/view.asset.php no declara ninguna).
 */
( function () {
	'use strict';

	function initCarta( root ) {
		var filters = root.querySelectorAll( '.llao-carta__filter' );
		var cards   = root.querySelectorAll( '.llao-carta__card' );
		var empty   = root.querySelector( '.llao-carta__empty' );

		if ( ! filters.length || ! cards.length ) {
			return;
		}

		function apply( slug ) {
			var visible = 0;

			cards.forEach( function ( card ) {
				// data-tipos viene como lista separada por espacios; se compara
				// por término entero para que "mango" no case con "mangos".
				var tipos = ( card.getAttribute( 'data-tipos' ) || '' ).split( /\s+/ );
				var show  = ! slug || tipos.indexOf( slug ) !== -1;

				card.classList.toggle( 'is-hidden', ! show );
				if ( show ) {
					visible++;
				}
			} );

			// La card de apertura (.is-wide, a 3x2) pasa a ser la primera que
			// quede a la vista: si no, al filtrar y caer la que la llevaba, la
			// rejilla abriría sin card grande. Para que sea siempre el mismo
			// producto pase lo que pase, basta con quitar este bloque.
			var primera = null;
			cards.forEach( function ( card ) {
				card.classList.remove( 'is-wide' );
				if ( ! primera && ! card.classList.contains( 'is-hidden' ) ) {
					primera = card;
				}
			} );
			if ( primera ) {
				primera.classList.add( 'is-wide' );
			}

			filters.forEach( function ( button ) {
				var active = ( button.getAttribute( 'data-filter' ) || '' ) === slug;
				button.classList.toggle( 'is-active', active );
				button.setAttribute( 'aria-pressed', active ? 'true' : 'false' );
			} );

			if ( empty ) {
				empty.hidden = visible > 0;
			}
		}

		filters.forEach( function ( button ) {
			button.addEventListener( 'click', function () {
				apply( button.getAttribute( 'data-filter' ) || '' );
			} );
		} );

		// Filtro inicial por URL (?tipo=slug): así las migas de pan del
		// producto pueden enlazar directo a la Carta ya filtrada por su tipo.
		// Sin parámetro, se respeta el que haya marcado el propio PHP como
		// activo por defecto ("Todos", o el primer tipo si no hay "Todos").
		var initial = new URLSearchParams( window.location.search ).get( 'tipo' );
		if ( ! initial ) {
			var activo = root.querySelector( '.llao-carta__filter.is-active' );
			initial = activo ? ( activo.getAttribute( 'data-filter' ) || '' ) : '';
		}
		apply( initial );
	}

	function initAll() {
		document.querySelectorAll( '.wp-block-llaollao-carta' ).forEach( initCarta );
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', initAll );
	} else {
		initAll();
	}
} )();
