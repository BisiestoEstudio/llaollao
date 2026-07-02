/**
 * Frontend del bloque "Video hero" (compilado a mano; equivale a src/view.js).
 * Escritorio (hover): reproduce al entrar el ratón, pausa/reinicia al salir.
 * Móvil/táctil: muestra portada; al tocar arranca/pausa. .is-playing desvanece la portada.
 */
( function () {
	function initVideoHero( hero ) {
		var video = hero.querySelector( '.video-hero__video' );
		if ( ! video ) {
			return;
		}

		function play() {
			// Carga diferida: asigna el src desde data-src la primera vez.
			if ( ! video.getAttribute( 'src' ) && video.dataset.src ) {
				video.src = video.dataset.src;
			}
			hero.classList.add( 'is-playing' );
			var p = video.play();
			if ( p && p.catch ) {
				p.catch( function () {} );
			}
		}

		var canHover = window.matchMedia( '(hover: hover)' ).matches;

		if ( canHover ) {
			// Al primer hover arranca y se queda en bucle; no se pausa al salir.
			hero.addEventListener( 'mouseenter', play );
		} else {
			hero.style.cursor = 'pointer';
			hero.addEventListener( 'click', function () {
				if ( video.paused ) {
					play();
				} else {
					video.pause();
					hero.classList.remove( 'is-playing' );
				}
			} );
		}
	}

	function ready() {
		document.querySelectorAll( '.video-hero' ).forEach( initVideoHero );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', ready );
	} else {
		ready();
	}
} )();
