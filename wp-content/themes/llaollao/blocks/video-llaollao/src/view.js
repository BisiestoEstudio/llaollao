/**
 * Frontend del bloque "Video Llaollao".
 * Escritorio (hover): al primer hover carga y reproduce en bucle; no se pausa al salir.
 * Móvil/táctil (sin hover): muestra la imagen de inicio; al tocar arranca/pausa.
 * La clase .is-playing desvanece la imagen de inicio (ver style.css).
 */
function initVideoLlaollao( item ) {
	const video = item.querySelector( '.video-llaollao__video' );
	if ( ! video ) {
		return;
	}

	const play = () => {
		// Carga diferida: asigna el src desde data-src la primera vez.
		if ( ! video.getAttribute( 'src' ) && video.dataset.src ) {
			video.src = video.dataset.src;
		}
		item.classList.add( 'is-playing' );
		const p = video.play();
		if ( p && p.catch ) {
			p.catch( () => {} );
		}
	};

	const canHover = window.matchMedia( '(hover: hover)' ).matches;

	if ( canHover ) {
		// Reproduce mientras haya hover (en bucle si sigue el hover); pausa al salir.
		item.addEventListener( 'mouseenter', play );
		item.addEventListener( 'mouseleave', () => {
			video.pause();
			try {
				video.currentTime = 0;
			} catch ( e ) {}
			item.classList.remove( 'is-playing' );
		} );
	} else {
		item.style.cursor = 'pointer';
		item.addEventListener( 'click', () => {
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
