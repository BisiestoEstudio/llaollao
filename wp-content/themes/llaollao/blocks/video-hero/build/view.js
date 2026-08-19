/**
 * Frontend del bloque "Video hero".
 * Escritorio (hover): reproduce al entrar el ratón, pausa y reinicia al salir.
 * Móvil/táctil (sin hover): arranca solo en cuanto entra en el viewport
 * (IntersectionObserver), sin esperar a un toque; tocar sigue pudiendo
 * pausar/reanudar. La clase .is-playing desvanece la portada (ver style.css).
 *
 * Con vídeo de móvil propio hay dos <video> en el DOM (--desktop/--mobile,
 * ver render.php); aquí se elige el que toque según el ancho (mismo criterio
 * que la media query de style.css, 768px) y solo a ese se le engancha todo:
 * el otro se queda sin `src`, así que tampoco se descarga.
 */
function initVideoHero( hero ) {
	const videos = hero.querySelectorAll( '.video-hero__video' );
	if ( ! videos.length ) {
		return;
	}

	let video = videos[ 0 ];
	if ( videos.length > 1 ) {
		const isMobile = window.matchMedia( '(max-width: 768px)' ).matches;
		const preferred = hero.querySelector(
			isMobile ? '.video-hero__video--mobile' : '.video-hero__video--desktop'
		);
		if ( preferred ) {
			video = preferred;
		}
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

		// Arranca en cuanto sea visible, sin esperar al primer toque.
		if ( 'IntersectionObserver' in window ) {
			const observer = new IntersectionObserver( ( entries ) => {
				entries.forEach( ( entry ) => {
					if ( entry.isIntersecting ) {
						play();
						observer.unobserve( hero );
					}
				} );
			} );
			observer.observe( hero );
		} else {
			play();
		}
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
