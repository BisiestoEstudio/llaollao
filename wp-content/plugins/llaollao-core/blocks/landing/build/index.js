/**
 * Editor del bloque "Landing" (compilado a mano; equivale a src/index.js).
 * Zona libre a la izquierda con InnerBlocks y, a la derecha, las tres columnas
 * de imágenes: aquí se ven quietas y sin duplicar (eso lo hace render.php para
 * el bucle), solo para poder añadirlas y quitarlas. Escrito con
 * wp.element.createElement (sin node_modules).
 */
( function ( wp ) {
	var registerBlockType = wp.blocks.registerBlockType;
	var be                = wp.blockEditor;
	var useBlockProps     = be.useBlockProps;
	var InnerBlocks       = be.InnerBlocks;
	var InspectorControls = be.InspectorControls;
	var MediaUpload       = be.MediaUpload;
	var MediaUploadCheck  = be.MediaUploadCheck;
	var PanelBody         = wp.components.PanelBody;
	var RangeControl      = wp.components.RangeControl;
	var Button            = wp.components.Button;
	var el                = wp.element.createElement;
	var Fragment          = wp.element.Fragment;
	var __                = wp.i18n.__;

	registerBlockType( 'llaollao/landing', {
		edit: function ( props ) {
			var attributes    = props.attributes;
			var setAttributes = props.setAttributes;
			var images        = attributes.images || [];
			var blockProps    = useBlockProps( { className: 'llao-landing' } );

			// Mismo reparto por turnos que render.php, para que lo que se ve en
			// el editor case con el front.
			var columns = [ [], [], [] ];
			images.forEach( function ( image, i ) {
				columns[ i % 3 ].push( { image: image, index: i } );
			} );

			function addImages( media ) {
				var list = Array.isArray( media ) ? media : [ media ];
				var next = list
					.filter( function ( m ) { return m && m.id; } )
					.map( function ( m ) {
						return { id: m.id, url: m.url, alt: m.alt || '' };
					} );
				setAttributes( { images: images.concat( next ) } );
			}

			function removeImage( index ) {
				setAttributes( {
					images: images.filter( function ( _image, i ) {
						return i !== index;
					} ),
				} );
			}

			var media = images.length
				? el( 'div', { className: 'llao-landing__media llao-landing__media--editing' },
					columns.map( function ( column, colIndex ) {
						return el( 'div', {
							key:       colIndex,
							className: 'llao-landing__col llao-landing__col--' +
								( 1 === colIndex ? 'up' : 'down' ),
						},
							el( 'div', { className: 'llao-landing__track' },
								column.map( function ( entry ) {
									return el( 'div', {
										key:       entry.index,
										className: 'llao-landing__thumb',
									},
										el( 'img', {
											className: 'llao-landing__img',
											src:       entry.image.url,
											alt:       entry.image.alt || '',
										} ),
										el( 'button', {
											type:        'button',
											className:   'llao-landing__remove',
											onClick:     function () {
												removeImage( entry.index );
											},
											'aria-label': __( 'Quitar imagen', 'llaollao-core' ),
										}, '×' )
									);
								} )
							)
						);
					} )
				)
				: el( 'div', { className: 'llao-landing__media llao-landing__media--empty' },
					__( 'Todavía no hay imágenes.', 'llaollao-core' )
				);

			var picker = el( 'div', { className: 'llao-landing__picker' },
				el( MediaUploadCheck, null,
					el( MediaUpload, {
						multiple:     true,
						gallery:      true,
						allowedTypes: [ 'image' ],
						value:        images.map( function ( image ) { return image.id; } ),
						onSelect:     addImages,
						render:       function ( o ) {
							return el( Button, { onClick: o.open, variant: 'primary' },
								__( 'Añadir imágenes', 'llaollao-core' )
							);
						},
					} )
				)
			);

			var inspector = el( InspectorControls, null,
				el( PanelBody, { title: __( 'Landing', 'llaollao-core' ), initialOpen: true },
					el( RangeControl, {
						label:    __( 'Duración del recorrido (segundos)', 'llaollao-core' ),
						help:     __( 'Cuanto más alto, más despacio se mueven las imágenes.', 'llaollao-core' ),
						value:    attributes.speed,
						onChange: function ( v ) {
							setAttributes( { speed: v === undefined ? 30 : v } );
						},
						min:  5,
						max:  120,
						step: 1,
						__nextHasNoMarginBottom: true,
					} )
				)
			);

			return el( Fragment, null,
				inspector,
				el( 'div', blockProps,
					el( 'div', { className: 'llao-landing__content is-layout-flow' }, el( InnerBlocks ) ),
					el( 'div', null, media, picker )
				)
			);
		},

		// Dinámico: render.php arma las columnas y duplica las pistas. Solo
		// persistimos los InnerBlocks de la zona libre.
		save: function () {
			return el( InnerBlocks.Content );
		},
	} );
} )( window.wp );
