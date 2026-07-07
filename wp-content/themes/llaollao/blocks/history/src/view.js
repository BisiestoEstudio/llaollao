/**
 * Front del bloque "History".
 * - Arrastrar-para-desplazar con ratón en ambas filas (nav e ítems); en
 *   táctil/trackpad el scroll es nativo.
 * - Click en un enlace de la nav → desplaza los ítems hasta ese año.
 * - El enlace activo se sincroniza con el scroll de los ítems.
 */
( function () {
	// Arrastre con ratón sobre un contenedor con overflow-x. No usamos
	// setPointerCapture porque redirige el evento click al contenedor y rompe el
	// click de los enlaces; en su lugar escuchamos move/up en window.
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

		// Evita que un arrastre dispare clicks dentro (enlaces/botones).
		scroller.addEventListener( 'click', function ( e ) {
			if ( moved ) {
				e.preventDefault();
				e.stopPropagation();
				moved = false;
			}
		}, true );
	}

	function wireNav( root ) {
		var nav = root.querySelector( '.history__nav' );
		var items = root.querySelector( '.history__items' );
		if ( ! nav || ! items ) {
			return;
		}
		var links = Array.prototype.slice.call( nav.querySelectorAll( '.history__nav-link' ) );
		var cells = Array.prototype.slice.call( items.querySelectorAll( '.history-item' ) );

		function setActive( i ) {
			links.forEach( function ( l, j ) {
				l.classList.toggle( 'is-active', j === i );
			} );
		}

		links.forEach( function ( link, i ) {
			link.addEventListener( 'click', function ( e ) {
				e.preventDefault();
				var target = cells[ i ];
				if ( ! target ) {
					return;
				}
				// Alinea el ítem con el borde izquierdo del scroller.
				var delta = target.getBoundingClientRect().left - items.getBoundingClientRect().left;
				items.scrollBy( { left: delta, behavior: 'smooth' } );
				setActive( i );
				// Deja el enlace pulsado a la vista dentro de la nav.
				link.scrollIntoView( { behavior: 'smooth', inline: 'center', block: 'nearest' } );
			} );
		} );

		// Sincroniza el enlace activo con el scroll de los ítems: marca el ítem
		// cuyo borde izquierdo esté más cerca del borde izquierdo del scroller.
		var raf = null;
		items.addEventListener( 'scroll', function () {
			if ( raf ) {
				return;
			}
			raf = window.requestAnimationFrame( function () {
				raf = null;
				var contLeft = items.getBoundingClientRect().left;
				var best = 0;
				var bestDist = Infinity;
				cells.forEach( function ( cell, i ) {
					var dist = Math.abs( cell.getBoundingClientRect().left - contLeft );
					if ( dist < bestDist ) {
						bestDist = dist;
						best = i;
					}
				} );
				setActive( best );
			} );
		} );

		setActive( 0 );
	}

	function boot() {
		document.querySelectorAll( '.wp-block-bisiesto-history' ).forEach( function ( root ) {
			var nav = root.querySelector( '.history__nav' );
			var items = root.querySelector( '.history__items' );
			if ( items ) {
				makeDraggable( items );
			}
			if ( nav ) {
				makeDraggable( nav );
			}
			wireNav( root );
		} );
	}

	if ( document.readyState !== 'loading' ) {
		boot();
	} else {
		document.addEventListener( 'DOMContentLoaded', boot );
	}
} )();
