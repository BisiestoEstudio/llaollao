/**
 * Frontend del bloque "Video hero".
 * Escritorio (hover): reproduce al entrar el ratón, pausa y reinicia al salir.
 * Móvil/táctil (sin hover): muestra la portada; al tocar arranca/pausa el vídeo.
 * La clase .is-playing desvanece la portada (ver style.css).
 */
function initVideoHero( hero ) {
	const video = hero.querySelector( '.video-hero__video' );
	if ( ! video ) {
		return;
	}

	const play = () => {
		// Carga diferida: asigna el src desde data-src la primera vez.
		if ( ! video.getAttribute( 'src' ) && video.dataset.src ) {
			video.src = video.dataset.src;
		}
		hero.classList.add( 'is-playing' );
		const p = video.play();
		if ( p && p.catch ) {
			p.catch( () => {} );
		}
	};

	const canHover = window.matchMedia( '(hover: hover)' ).matches;

	if ( canHover ) {
		// Al primer hover arranca y se queda en bucle; no se pausa al salir.
		hero.addEventListener( 'mouseenter', play );
	} else {
		hero.style.cursor = 'pointer';
		hero.addEventListener( 'click', () => {
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
