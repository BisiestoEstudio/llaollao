/**
 * Front del bloque "Slider de imágenes": arrastrar-para-desplazar con ratón
 * sobre la fila. En táctil/trackpad el scroll horizontal ya es nativo, así que
 * ahí no hacemos nada.
 *
 * No usamos setPointerCapture: redirige el click al contenedor y rompería los
 * clicks de lo que haya dentro; escuchamos move/up en window.
 */
( function () {
	function makeDraggable( scroller ) {
		var isDown = false;
		var moved = false;
		var startX = 0;
		var scrollLeft = 0;

		scroller.addEventListener( 'pointerdown', function ( e ) {
			if ( e.pointerType && e.pointerType !== 'mouse' ) {
				return; // táctil/lápiz: scroll nativo
			}
			isDown = true;
			moved = false;
			startX = e.clientX;
			scrollLeft = scroller.scrollLeft;
			scroller.classList.add( 'is-dragging' );
		} );

		window.addEventListener( 'pointermove', function ( e ) {
			if ( ! isDown ) {
				return;
			}
			var walk = e.clientX - startX;
			if ( Math.abs( walk ) > 3 ) {
				moved = true;
			}
			scroller.scrollLeft = scrollLeft - walk;
		} );

		window.addEventListener( 'pointerup', function () {
			if ( ! isDown ) {
				return;
			}
			isDown = false;
			scroller.classList.remove( 'is-dragging' );
		} );

		// Que un arrastre no dispare clicks de enlaces/botones de dentro.
		scroller.addEventListener( 'click', function ( e ) {
			if ( moved ) {
				e.preventDefault();
				e.stopPropagation();
				moved = false;
			}
		}, true );

		// Sin esto el navegador arrastraría la imagen (fantasma) en vez de la fila.
		scroller.addEventListener( 'dragstart', function ( e ) {
			e.preventDefault();
		} );
	}

	function boot() {
		document.querySelectorAll( '.wp-block-llaollao-image-slider .llao-slider__track' ).forEach( makeDraggable );
	}

	if ( document.readyState !== 'loading' ) {
		boot();
	} else {
		document.addEventListener( 'DOMContentLoaded', boot );
	}
} )();
