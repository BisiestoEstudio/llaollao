/**
 * Editor del bloque "Side image · columna" (compilado a mano; equivale a
 * src/index.js). Fondo de color (soporte nativo) o imagen con focal point, e
 * inner blocks centrados. Escrito con wp.element.createElement porque el tema
 * no tiene node_modules.
 */
( function ( wp ) {
	var registerBlockType = wp.blocks.registerBlockType;
	var be = wp.blockEditor;
	var useBlockProps = be.useBlockProps;
	var InnerBlocks = be.InnerBlocks;
	var InspectorControls = be.InspectorControls;
	var MediaUpload = be.MediaUpload;
	var MediaUploadCheck = be.MediaUploadCheck;
	var cmp = wp.components;
	var PanelBody = cmp.PanelBody;
	var Button = cmp.Button;
	var FocalPointPicker = cmp.FocalPointPicker;
	var __ = wp.i18n.__;
	var el = wp.element.createElement;
	var Fragment = wp.element.Fragment;

	registerBlockType( 'bisiesto/side-image-column', {
		edit: function ( props ) {
			var attributes    = props.attributes;
			var setAttributes = props.setAttributes;
			var imageUrl   = attributes.imageUrl;
			var imageId    = attributes.imageId;
			var imageAlt   = attributes.imageAlt;
			var focalPoint = attributes.focalPoint || { x: 0.5, y: 0.5 };

			var bgStyle = imageUrl
				? {
					backgroundImage: 'url(' + imageUrl + ')',
					backgroundPosition:
						Math.round( focalPoint.x * 100 ) + '% ' +
						Math.round( focalPoint.y * 100 ) + '%',
				}
				: {};

			var blockProps = useBlockProps( {
				className: 'side-image-column',
				style: bgStyle,
			} );

			var media = el( MediaUploadCheck, {},
				el( MediaUpload, {
					onSelect: function ( m ) {
						setAttributes( {
							imageUrl: m.url,
							imageId:  m.id,
							imageAlt: m.alt || '',
						} );
					},
					allowedTypes: [ 'image' ],
					value: imageId,
					render: function ( o ) {
						return el( 'div',
							{ style: { display: 'flex', flexDirection: 'column', gap: 8 } },
							imageUrl
								? el( 'img', { src: imageUrl, alt: imageAlt, style: { width: '100%', borderRadius: 4 } } )
								: null,
							el( Button,
								{ onClick: o.open, variant: imageUrl ? 'secondary' : 'primary' },
								imageUrl
									? __( 'Cambiar imagen', 'bisiesto' )
									: __( 'Seleccionar imagen', 'bisiesto' )
							),
							imageUrl
								? el( Button, {
									onClick: function () {
										setAttributes( { imageUrl: '', imageId: undefined, imageAlt: '' } );
									},
									variant: 'link',
									isDestructive: true,
								}, __( 'Eliminar imagen', 'bisiesto' ) )
								: null
						);
					},
				} )
			);

			var focal = imageUrl
				? el( FocalPointPicker, {
					url: imageUrl,
					value: focalPoint,
					onChange: function ( v ) { setAttributes( { focalPoint: v } ); },
					style: { marginTop: 16 },
				} )
				: null;

			var inspector = el( InspectorControls, {},
				el( PanelBody, { title: __( 'Imagen de fondo', 'bisiesto' ) }, media, focal )
			);

			// templateLock false: rompe la herencia del "all" del padre para poder
			// editar los inner blocks dentro de la columna.
			var column = el( 'div', blockProps,
				el( 'div', { className: 'side-image-column__inner' },
					el( InnerBlocks, { templateLock: false } )
				)
			);

			return el( Fragment, {}, inspector, column );
		},

		save: function () {
			return el( InnerBlocks.Content );
		},
	} );
} )( window.wp );
