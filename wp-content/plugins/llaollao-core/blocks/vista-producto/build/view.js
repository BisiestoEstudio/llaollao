/**
 * Front del bloque "Vista producto con slide vertical".
 *
 * Dos modos, según el ancho:
 *
 * - Escritorio: la pista enseña solo la pieza activa (las demás con display:none
 *   desde el CSS) y la tira lateral la cambia moviendo la clase .is-active. No
 *   se recarga ninguna imagen porque todas están ya en el DOM.
 *
 * - Móvil: las piezas conviven en fila dentro de la pista, que se arrastra con
 *   el dedo. El arrastre es scroll nativo con scroll-snap, así que el gesto y
 *   la inercia los pone el navegador; aquí solo se lee dónde ha quedado la
 *   pista para encender el punto correspondiente, y al pulsar un punto se
 *   desplaza la pista hasta esa pieza.
 *
 * Si la pieza que se abandona es un vídeo en marcha, se pausa: seguiría sonando
 * fuera de la vista.
 *
 * Sin dependencias (build/view.asset.php no declara ninguna).
 */
( function () {
	'use strict';

	// Mismo punto de ruptura que el @media de style.css.
	var MOVIL = '(max-width: 781px)';

	/**
	 * Arrastrar con el ratón para desplazar una fila horizontal. Mismo
	 * planteamiento que el bloque "Slider de imágenes": en táctil y trackpad el
	 * desplazamiento lateral ya es nativo, así que solo se atiende al ratón.
	 *
	 * No se usa setPointerCapture: redirige el click al contenedor y rompería
	 * el de los enlaces de dentro. Se escucha move/up en window.
	 */
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

		// Que soltar tras arrastrar no acabe navegando al enlace de debajo.
		scroller.addEventListener( 'click', function ( e ) {
			if ( movido ) {
				e.preventDefault();
				e.stopPropagation();
				movido = false;
			}
		}, true );

		// Sin esto el navegador arrastraría el propio enlace en vez de la fila.
		scroller.addEventListener( 'dragstart', function ( e ) {
			e.preventDefault();
		} );
	}

	function initVista( root ) {
		var thumbs = root.querySelectorAll( '.llao-vista__thumb' );
		var medias = root.querySelectorAll( '.llao-vista__media' );
		var stage  = root.querySelector( '.llao-vista__stage' );
		var pista  = root.querySelector( '.llao-vista__siblings-track' );
		var tipos  = root.querySelector( '.llao-vista__tipos-track' );
		var volverTipos = root.querySelector( '.llao-vista__tipos-icon' );

		// Las filas de tipos y de hermanos son independientes de la galería: se
		// preparan antes de la salida por falta de recursos.
		if ( pista ) {
			hacerArrastrable( pista );
		}
		if ( tipos ) {
			hacerArrastrable( tipos );
		}
		// El icono de "hermanos" ya es un enlace normal (al padre o a la Carta,
		// ver render.php); solo el de "tipos" sigue volviendo con JS.
		if ( volverTipos ) {
			volverTipos.addEventListener( 'click', function () {
				history.back();
			} );
		}

		if ( ! medias.length ) {
			return;
		}

		var mql = window.matchMedia( MOVIL );

		/**
		 * Deja marcada la pieza que está a la vista. En escritorio la clase es
		 * lo que decide cuál se enseña; en móvil todas se ven y la clase solo
		 * sirve para encender su punto.
		 */
		function marcar( index ) {
			index = String( index );

			medias.forEach( function ( media ) {
				var activo = media.getAttribute( 'data-index' ) === index;

				if ( ! activo ) {
					var video = media.querySelector( 'video' );
					if ( video && ! video.paused ) {
						video.pause();
					}
				}

				media.classList.toggle( 'is-active', activo );
			} );

			thumbs.forEach( function ( thumb ) {
				var activo = thumb.getAttribute( 'data-index' ) === index;
				thumb.classList.toggle( 'is-active', activo );
				thumb.setAttribute( 'aria-pressed', activo ? 'true' : 'false' );
			} );

			ajustarAlto();
		}

		/**
		 * Da a la pista el alto de la pieza activa. Sin esto la pista mide lo
		 * que la pieza más alta —es un flex y su alto es el del mayor—, y las
		 * imágenes bajas dejan un hueco entre la foto y los puntos.
		 *
		 * En escritorio se limpia el alto en línea: allí solo se enseña una
		 * pieza y la pista ya se ajusta sola.
		 */
		function ajustarAlto() {
			if ( ! stage ) {
				return;
			}

			if ( ! mql.matches ) {
				stage.style.height = '';
				return;
			}

			var activa = root.querySelector( '.llao-vista__media.is-active' );
			if ( activa && activa.offsetHeight ) {
				stage.style.height = activa.offsetHeight + 'px';
			}
		}

		/**
		 * Va a una pieza. En móvil no se toca la clase a mano: se desplaza la
		 * pista y de marcarla ya se encarga el listener de scroll, de modo que
		 * el punto encendido siempre concuerde con lo que se ve, venga el
		 * cambio de un toque o de un arrastre.
		 */
		function ir( index, suave ) {
			if ( mql.matches && stage ) {
				stage.scrollTo( {
					left: stage.clientWidth * parseInt( index, 10 ),
					behavior: false === suave ? 'auto' : 'smooth',
				} );
			} else {
				marcar( index );
			}
		}

		thumbs.forEach( function ( thumb ) {
			thumb.addEventListener( 'click', function () {
				ir( thumb.getAttribute( 'data-index' ) );
			} );
		} );

		if ( stage ) {
			// El scroll dispara muy seguido: se agrupa en un frame para no
			// recalcular la pieza a la vista en cada píxel del arrastre.
			var pendiente = null;

			stage.addEventListener( 'scroll', function () {
				if ( ! mql.matches || ! stage.clientWidth ) {
					return;
				}

				if ( pendiente ) {
					cancelAnimationFrame( pendiente );
				}

				pendiente = requestAnimationFrame( function () {
					marcar( Math.round( stage.scrollLeft / stage.clientWidth ) );
				} );
			}, { passive: true } );
		}

		/**
		 * Al cruzar el punto de ruptura, coloca la pista en la pieza que estaba
		 * activa. Sin esto se pasa de escritorio a móvil con el scroll en la
		 * primera imagen y el punto encendido en otra.
		 */
		function sincronizar() {
			var activa = root.querySelector( '.llao-vista__media.is-active' );
			ir( activa ? activa.getAttribute( 'data-index' ) : 0, false );
			ajustarAlto();
		}

		if ( mql.addEventListener ) {
			mql.addEventListener( 'change', sincronizar );
		}

		// El alto natural de una pieza cambia con el ancho de la ventana, y no
		// se sabe del todo hasta que la imagen (o los metadatos del vídeo) han
		// cargado. Los eventos load y loadedmetadata no burbujean, de ahí la
		// fase de captura.
		window.addEventListener( 'resize', ajustarAlto );

		if ( stage ) {
			stage.addEventListener( 'load', ajustarAlto, true );
			stage.addEventListener( 'loadedmetadata', ajustarAlto, true );
		}

		ajustarAlto();
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
