/**
 * Front del bloque Tabla de contenidos:
 * - Plegar/desplegar la tabla con el botón de la cabecera.
 * - Scroll suave al hacer clic en un enlace.
 */
( function () {
	function initToc( toc ) {
		// Plegar / desplegar.
		var toggle = toc.querySelector( '.tcb-toc__toggle' );
		if ( toggle ) {
			toggle.addEventListener( 'click', function () {
				var collapsed = toc.classList.toggle( 'is-collapsed' );
				toggle.setAttribute( 'aria-expanded', collapsed ? 'false' : 'true' );
			} );
		}

		var links = Array.prototype.slice.call( toc.querySelectorAll( 'a[href^="#"]' ) );
		if ( ! links.length ) {
			return;
		}

		// Scroll suave + actualización del hash.
		links.forEach( function ( link ) {
			link.addEventListener( 'click', function ( e ) {
				var id     = decodeURIComponent( link.getAttribute( 'href' ).slice( 1 ) );
				var target = document.getElementById( id );
				if ( ! target ) {
					return;
				}
				e.preventDefault();
				target.scrollIntoView( { behavior: 'smooth', block: 'start' } );
				if ( window.history && window.history.pushState ) {
					window.history.pushState( null, '', '#' + id );
				}
			} );
		} );

	}

	function init() {
		document.querySelectorAll( '.tcb-toc' ).forEach( initToc );
	}

	if ( 'loading' !== document.readyState ) {
		init();
	} else {
		document.addEventListener( 'DOMContentLoaded', init );
	}
} )();
