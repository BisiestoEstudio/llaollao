/**
 * Front del bloque "Locales": buscador + botones de país filtran el listado
 * (comportamiento de radio en los países, como en el bloque Carta), y el mapa
 * de Google se sincroniza mostrando solo los markers de lo que quede visible.
 *
 * Los países viven en un panel desplegable (botón "Filtros") superpuesto al
 * listado (mismo hueco, position:absolute sobre .llao-locales__body): abrir y
 * cerrar solo alterna la clase is-open, que anima opacidad + transform en CSS;
 * no hace falta ocultar el <ul> de debajo porque el panel lo tapa. Al elegir
 * un país se cierra solo y actualiza el nombre junto al buscador
 * (.llao-locales__pais-actual).
 *
 * El mapa es opcional: si no hay clave (data-maps-key vacío, porque no se ha
 * rellenado "Clave de Maps" en Ajustes → Llaollao → Integraciones) el listado
 * sigue funcionando igual, solo que sin columna de mapa activa.
 *
 * Clic (o Enter/Espacio) en un local del listado: centra el mapa en su marker
 * y hace zoom de calle (ver zoomLocal()).
 *
 * Sin dependencias (build/view.asset.php no declara ninguna); el script de
 * Google Maps se inyecta a mano una sola vez aunque haya varios bloques en la
 * página.
 */
( function () {
	'use strict';

	var mapsPromise = null;

	function loadGoogleMaps( key ) {
		if ( window.google && window.google.maps ) {
			return Promise.resolve();
		}

		if ( ! mapsPromise ) {
			mapsPromise = new Promise( function ( resolve ) {
				window.__llaoLocalesMapsReady = resolve;

				console.log( 'Llamada a la GMaps' );

				var script = document.createElement( 'script' );
				script.src = 'https://maps.googleapis.com/maps/api/js?key='
					+ encodeURIComponent( key )
					+ '&callback=__llaoLocalesMapsReady&loading=async';
				script.async = true;
				document.head.appendChild( script );
			} );
		}

		return mapsPromise;
	}

	function initLocales( root ) {
		var search      = root.querySelector( '.llao-locales__search' );
		var buttons     = root.querySelectorAll( '.llao-locales__pais' );
		var items       = root.querySelectorAll( '.llao-locales__item' );
		var empty       = root.querySelector( '.llao-locales__empty' );
		var mapEl       = root.querySelector( '.llao-locales__map' );
		var key         = root.getAttribute( 'data-maps-key' ) || '';
		var paisActual  = root.querySelector( '.llao-locales__pais-actual' );
		var toggle      = root.querySelector( '.llao-locales__filtros-toggle' );
		var panel       = root.querySelector( '.llao-locales__filtros-panel' );
		var cerrar      = root.querySelector( '.llao-locales__filtros-cerrar' );

		if ( ! items.length ) {
			return;
		}

		var map        = null;
		var markers    = [];
		var activoBtn  = root.querySelector( '.llao-locales__pais.is-active' );
		var activoPais = activoBtn ? ( activoBtn.getAttribute( 'data-pais' ) || '' ) : '';

		function actualizarMapa( visibles ) {
			if ( ! map ) {
				return;
			}

			markers.forEach( function ( marker ) { marker.setMap( null ); } );
			markers = [];

			if ( ! visibles.length ) {
				return;
			}

			var bounds = new google.maps.LatLngBounds();

			visibles.forEach( function ( item ) {
				var pos = {
					lat: parseFloat( item.getAttribute( 'data-lat' ) ),
					lng: parseFloat( item.getAttribute( 'data-lng' ) ),
				};
				var tituloEl = item.querySelector( '.llao-locales__title' );

				markers.push( new google.maps.Marker( {
					position: pos,
					map:      map,
					title:    tituloEl ? tituloEl.textContent : '',
				} ) );
				bounds.extend( pos );
			} );

			map.fitBounds( bounds );

			// Un único local: fitBounds acerca demasiado el zoom (o lo deja al
			// máximo), así que se recorta a un valor razonable de calle.
			if ( 1 === visibles.length ) {
				google.maps.event.addListenerOnce( map, 'bounds_changed', function () {
					map.setZoom( Math.min( map.getZoom(), 16 ) );
				} );
			}
		}

		function apply() {
			var term     = search ? search.value.trim().toLowerCase() : '';
			var visibles = [];

			items.forEach( function ( item ) {
				var mismoPais = item.getAttribute( 'data-pais' ) === activoPais;
				var coincide  = ! term || ( item.getAttribute( 'data-search' ) || '' ).indexOf( term ) !== -1;
				var mostrar   = mismoPais && coincide;

				item.classList.toggle( 'is-hidden', ! mostrar );
				if ( mostrar ) {
					visibles.push( item );
				}
			} );

			if ( empty ) {
				empty.hidden = visibles.length > 0;
			}

			actualizarMapa( visibles );
		}

		function zoomLocal( item ) {
			if ( ! map ) {
				return;
			}

			map.panTo( {
				lat: parseFloat( item.getAttribute( 'data-lat' ) ),
				lng: parseFloat( item.getAttribute( 'data-lng' ) ),
			} );
			map.setZoom( 16 );
		}

		function cerrarPanel() {
			if ( ! panel ) {
				return;
			}
			panel.classList.remove( 'is-open' );
			if ( toggle ) {
				toggle.setAttribute( 'aria-expanded', 'false' );
			}
		}

		items.forEach( function ( item ) {
			item.addEventListener( 'click', function () {
				zoomLocal( item );
			} );

			item.addEventListener( 'keydown', function ( e ) {
				if ( 'Enter' === e.key || ' ' === e.key ) {
					e.preventDefault();
					zoomLocal( item );
				}
			} );
		} );

		buttons.forEach( function ( button ) {
			button.addEventListener( 'click', function () {
				activoPais = button.getAttribute( 'data-pais' ) || '';

				buttons.forEach( function ( b ) {
					var activo = b === button;
					b.classList.toggle( 'is-active', activo );
					b.setAttribute( 'aria-pressed', activo ? 'true' : 'false' );
				} );

				if ( paisActual ) {
					paisActual.textContent = button.textContent;
				}

				cerrarPanel();
				apply();
			} );
		} );

		if ( toggle && panel ) {
			toggle.addEventListener( 'click', function () {
				var abrir = ! panel.classList.contains( 'is-open' );

				panel.classList.toggle( 'is-open', abrir );
				toggle.setAttribute( 'aria-expanded', abrir ? 'true' : 'false' );
			} );
		}

		if ( cerrar ) {
			cerrar.addEventListener( 'click', cerrarPanel );
		}

		if ( search ) {
			search.addEventListener( 'input', apply );
		}

		if ( key && mapEl ) {
			loadGoogleMaps( key ).then( function () {
				map = new google.maps.Map( mapEl, {
					center: { lat: 0, lng: 0 },
					zoom:   2,
				} );
				apply();
			} );
		} else {
			apply();
		}
	}

	function initAll() {
		document.querySelectorAll( '.wp-block-llaollao-locales' ).forEach( initLocales );
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', initAll );
	} else {
		initAll();
	}
} )();
