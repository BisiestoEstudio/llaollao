var GLOBE_SVG = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 2.4375C10.1087 2.4375 8.25991 2.99833 6.68736 4.04907C5.11482 5.09981 3.88917 6.59327 3.16541 8.34059C2.44164 10.0879 2.25227 12.0106 2.62125 13.8656C2.99022 15.7205 3.90096 17.4244 5.2383 18.7617C6.57564 20.099 8.27951 21.0098 10.1345 21.3788C11.9894 21.7477 13.9121 21.5584 15.6594 20.8346C17.4067 20.1108 18.9002 18.8852 19.9509 17.3126C21.0017 15.7401 21.5625 13.8913 21.5625 12C21.5595 9.46478 20.5511 7.03425 18.7584 5.24158C16.9658 3.44891 14.5352 2.44048 12 2.4375ZM19.6472 8.4375H15.8616C15.4102 6.69835 14.5873 5.07769 13.4494 3.68719C14.7879 3.92321 16.05 4.47811 17.1288 5.30495C18.2076 6.13179 19.0714 7.20626 19.6472 8.4375ZM20.4375 12C20.4381 12.8257 20.3172 13.647 20.0784 14.4375H16.1072C16.381 12.824 16.381 11.176 16.1072 9.5625H20.0784C20.3172 10.353 20.4381 11.1743 20.4375 12ZM12 20.2341C11.4584 19.6816 10.9807 19.0699 10.5759 18.4106C10.0296 17.5221 9.60522 16.5641 9.31407 15.5625H14.6859C14.3948 16.5641 13.9704 17.5221 13.4241 18.4106C13.0193 19.0699 12.5416 19.6816 12 20.2341ZM9.04219 14.4375C8.73598 12.8269 8.73598 11.1731 9.04219 9.5625H14.9578C15.264 11.1731 15.264 12.8269 14.9578 14.4375H9.04219ZM3.5625 12C3.56187 11.1743 3.68286 10.353 3.92157 9.5625H7.89282C7.61901 11.176 7.61901 12.824 7.89282 14.4375H3.92157C3.68286 13.647 3.56187 12.8257 3.5625 12ZM12 3.76594C12.5416 4.31841 13.0193 4.93007 13.4241 5.58938C13.9704 6.47794 14.3948 7.43589 14.6859 8.4375H9.31407C9.60522 7.43589 10.0296 6.47794 10.5759 5.58938C10.9807 4.93007 11.4584 4.31841 12 3.76594ZM10.5506 3.68719C9.41272 5.07769 8.58976 6.69835 8.13844 8.4375H4.35282C4.9286 7.20626 5.79242 6.13179 6.87122 5.30495C7.95001 4.47811 9.21206 3.92321 10.5506 3.68719ZM4.35282 15.5625H8.13844C8.58976 17.3017 9.41272 18.9223 10.5506 20.3128C9.21206 20.0768 7.95001 19.5219 6.87122 18.6951C5.79242 17.8682 4.9286 16.7937 4.35282 15.5625ZM13.4466 20.3128C14.5855 18.9226 15.4094 17.3019 15.8616 15.5625H19.6472C19.0714 16.7937 18.2076 17.8682 17.1288 18.6951C16.05 19.5219 14.7879 20.0768 13.4494 20.3128H13.4466Z" fill="currentColor"/></svg>';
var CHEVRON_SVG = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M6 9L12 15L18 9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';

function initLangSwitcher() {
	document.querySelectorAll( '.site-header__lang' ).forEach( function ( container ) {
		const ul = container.querySelector( '.wp-block-polylang-language-switcher' );
		if ( ! ul ) return;

		const currentItem = ul.querySelector( '.current-lang' );
		if ( ! currentItem ) return;

		const langName = currentItem.querySelector( 'a' ).textContent.trim();

		const trigger = document.createElement( 'button' );
		trigger.className = 'site-header__lang-trigger';
		trigger.setAttribute( 'aria-expanded', 'false' );
		trigger.setAttribute( 'aria-haspopup', 'true' );
		trigger.innerHTML = GLOBE_SVG + '<span>' + langName + '</span>' + CHEVRON_SVG;

		const nav = ul.closest( 'nav' ) || ul;
		container.insertBefore( trigger, nav );

		var closeTimer;

		container.addEventListener( 'mouseenter', function () {
			clearTimeout( closeTimer );
			ul.classList.add( 'is-open' );
			trigger.setAttribute( 'aria-expanded', 'true' );
		} );

		container.addEventListener( 'mouseleave', function () {
			closeTimer = setTimeout( function () {
				ul.classList.remove( 'is-open' );
				trigger.setAttribute( 'aria-expanded', 'false' );
			}, 150 );
		} );
	} );
}

function initHeader() {
	const headers = document.querySelectorAll( '.site-header' );

	headers.forEach( function ( header ) {
		const hamburger = header.querySelector( '.site-header__hamburger' );
		if ( ! hamburger ) return;

		hamburger.addEventListener( 'click', function () {
			const isOpen = header.classList.toggle( 'is-open' );
			hamburger.setAttribute( 'aria-expanded', isOpen ? 'true' : 'false' );
			hamburger.setAttribute( 'aria-label', isOpen ? 'Cerrar menú' : 'Abrir menú' );
		} );

		document.addEventListener( 'keydown', function ( e ) {
			if ( e.key === 'Escape' && header.classList.contains( 'is-open' ) ) {
				header.classList.remove( 'is-open' );
				hamburger.setAttribute( 'aria-expanded', 'false' );
				hamburger.setAttribute( 'aria-label', 'Abrir menú' );
				hamburger.focus();
			}
		} );
	} );
}

if ( ! ( window.wp && window.wp.blocks ) ) {
	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', function () {
			initLangSwitcher();
			initHeader();
		} );
	} else {
		initLangSwitcher();
		initHeader();
	}
}
