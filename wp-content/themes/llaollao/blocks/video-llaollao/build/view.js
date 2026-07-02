/**
 * Frontend del bloque "Video Llaollao" (compilado a mano; equivale a src/view.js).
 * Escritorio (hover): al primer hover carga y reproduce en bucle; no se pausa al salir.
 * Móvil/táctil: muestra imagen de inicio; al tocar arranca/pausa. .is-playing la desvanece.
 */
( function () {
	function initVideoLlaollao( item ) {
		var video = item.querySelector( '.video-llaollao__video' );
		if ( ! video ) {
			return;
		}

		function play() {
			// Carga diferida: asigna el src desde data-src la primera vez.
			if ( ! video.getAttribute( 'src' ) && video.dataset.src ) {
				video.src = video.dataset.src;
			}
			item.classList.add( 'is-playing' );
			var p = video.play();
			if ( p && p.catch ) {
				p.catch( function () {} );
			}
		}

		var canHover = window.matchMedia( '(hover: hover)' ).matches;

		if ( canHover ) {
			// Reproduce mientras haya hover (en bucle si sigue el hover); pausa al salir.
			item.addEventListener( 'mouseenter', play );
			item.addEventListener( 'mouseleave', function () {
				video.pause();
				try {
					video.currentTime = 0;
				} catch ( e ) {}
				item.classList.remove( 'is-playing' );
			} );
		} else {
			item.style.cursor = 'pointer';
			item.addEventListener( 'click', function () {
				if ( video.paused ) {
					play();
				} else {
					video.pause();
					item.classList.remove( 'is-playing' );
				}
			} );
		}
	}

	function ready() {
		document.querySelectorAll( '.video-llaollao' ).forEach( initVideoLlaollao );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', ready );
	} else {
		ready();
	}
} )();
