/**
 * Front del bloque "Vista producto": arrastrar con el ratón la nube de
 * etiquetas de Tipo.
 *
 * Mismo planteamiento que el bloque "Slider de imágenes" y que la fila de
 * hermanos de "Vista producto con slide vertical": en táctil y trackpad el
 * desplazamiento lateral ya es nativo, así que solo se atiende al ratón.
 *
 * No se usa setPointerCapture: redirige el click al contenedor y rompería el de
 * lo que haya dentro. Se escucha move/up en window.
 *
 * Sin dependencias (build/view.asset.php no declara ninguna).
 */
( function () {
	'use strict';

	function hacerArrastrable( scroller ) {
		var pulsado = false;
		var movido  = false;
		var inicioX = 0;
		var inicioScroll = 0;

		scroller.addEventListener( 'pointerdown', function ( e ) {
			if ( e.pointerType && 'mouse' !== e.pointerType ) {
				return; // táctil/lápiz: desplazamiento nativo
			}
			pulsado = true;
			movido  = false;
			inicioX = e.clientX;
			inicioScroll = scroller.scrollLeft;
			scroller.classList.add( 'is-dragging' );
		} );

		window.addEventListener( 'pointermove', function ( e ) {
			if ( ! pulsado ) {
				return;
			}
			var recorrido = e.clientX - inicioX;
			if ( Math.abs( recorrido ) > 3 ) {
				movido = true;
			}
			scroller.scrollLeft = inicioScroll - recorrido;
		} );

		window.addEventListener( 'pointerup', function () {
			if ( ! pulsado ) {
				return;
			}
			pulsado = false;
			scroller.classList.remove( 'is-dragging' );
		} );

		// Aquí las etiquetas no son enlaces, pero se mantiene por si algún día
		// lo son: que soltar tras arrastrar no dispare un click.
		scroller.addEventListener( 'click', function ( e ) {
			if ( movido ) {
				e.preventDefault();
				e.stopPropagation();
				movido = false;
			}
		}, true );

		// Sin esto el navegador arrastraría el contenido en vez de la fila.
		scroller.addEventListener( 'dragstart', function ( e ) {
			e.preventDefault();
		} );
	}

	function initAll() {
		document
			.querySelectorAll( '.wp-block-llaollao-vista-producto-simple .llao-vp__tipos-track' )
			.forEach( hacerArrastrable );
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', initAll );
	} else {
		initAll();
	}
} )();
