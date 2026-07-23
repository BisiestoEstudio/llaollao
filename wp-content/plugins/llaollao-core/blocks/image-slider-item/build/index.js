/**
 * Editor del bloque "Slider · imagen" (compilado a mano; equivale a
 * src/index.js). Selector de imagen, proporción por imagen y punto focal. El
 * front lo arma render.php. Escrito con wp.element.createElement (sin
 * node_modules).
 */
( function ( wp ) {
	var registerBlockType  = wp.blocks.registerBlockType;
	var be                 = wp.blockEditor;
	var useBlockProps      = be.useBlockProps;
	var InspectorControls  = be.InspectorControls;
	var BlockControls      = be.BlockControls;
	var MediaPlaceholder   = be.MediaPlaceholder;
	var MediaReplaceFlow   = be.MediaReplaceFlow;
	var PanelBody          = wp.components.PanelBody;
	var SelectControl      = wp.components.SelectControl;
	var TextControl        = wp.components.TextControl;
	var FocalPointPicker   = wp.components.FocalPointPicker;
	var Button             = wp.components.Button;
	var el                 = wp.element.createElement;
	var Fragment           = wp.element.Fragment;
	var __                 = wp.i18n.__;

	var RATIOS = [
		{ label: __( 'Original del archivo', 'llaollao-core' ), value: '' },
		{ label: __( 'Cuadrada · 1:1', 'llaollao-core' ),       value: '1/1' },
		{ label: __( 'Vertical · 3:4', 'llaollao-core' ),       value: '3/4' },
		{ label: __( 'Vertical · 2:3', 'llaollao-core' ),       value: '2/3' },
		{ label: __( 'Vertical · 9:16', 'llaollao-core' ),      value: '9/16' },
		{ label: __( 'Apaisada · 4:3', 'llaollao-core' ),       value: '4/3' },
		{ label: __( 'Apaisada · 3:2', 'llaollao-core' ),       value: '3/2' },
		{ label: __( 'Apaisada · 16:9', 'llaollao-core' ),      value: '16/9' },
	];

	// Atributos a partir de un adjunto del selector de medios.
	function mediaToAttrs( media ) {
		var large = media.sizes && media.sizes.large ? media.sizes.large : null;
		return {
			id:     media.id,
			url:    ( large && large.url ) || media.url || '',
			alt:    media.alt || '',
			width:  media.width || ( large && large.width ) || 0,
			height: media.height || ( large && large.height ) || 0,
		};
	}

	registerBlockType( 'llaollao/image-slider-item', {
		edit: function ( props ) {
			var a = props.attributes;
			var setAttributes = props.setAttributes;
			var focal = a.focalPoint || { x: 0.5, y: 0.5 };

			// La proporción elegida en el panel o, si es "Original", la del archivo.
			var shown = a.ratio || ( a.width && a.height ? a.width + '/' + a.height : '' );

			var classes = [ 'llao-slider__item' ];
			if ( ! a.url ) {
				classes.push( 'llao-slider__item--empty' );
			}
			if ( ! shown ) {
				classes.push( 'llao-slider__item--auto' );
			}

			var blockProps = useBlockProps( {
				className: classes.join( ' ' ),
				style: shown ? { aspectRatio: shown } : undefined,
			} );

			function onSelect( media ) {
				if ( ! media || ! media.id ) {
					return;
				}
				setAttributes( mediaToAttrs( media ) );
			}

			if ( ! a.url ) {
				return el( 'figure', blockProps,
					el( MediaPlaceholder, {
						icon: 'format-image',
						labels: { title: __( 'Imagen del slider', 'llaollao-core' ) },
						onSelect: onSelect,
						accept: 'image/*',
						allowedTypes: [ 'image' ],
					} )
				);
			}

			var toolbar = el( BlockControls, null,
				el( MediaReplaceFlow, {
					mediaId: a.id,
					mediaURL: a.url,
					accept: 'image/*',
					allowedTypes: [ 'image' ],
					onSelect: onSelect,
				} )
			);

			var inspector = el( InspectorControls, null,
				el( PanelBody, { title: __( 'Imagen', 'llaollao-core' ), initialOpen: true },
					el( SelectControl, {
						label: __( 'Proporción', 'llaollao-core' ),
						value: a.ratio,
						options: RATIOS,
						onChange: function ( v ) { setAttributes( { ratio: v } ); },
						help: __( 'El alto lo marca el slider; la proporción decide el ancho de esta imagen.', 'llaollao-core' ),
						__nextHasNoMarginBottom: true,
					} ),
					el( TextControl, {
						label: __( 'Texto alternativo', 'llaollao-core' ),
						value: a.alt,
						onChange: function ( v ) { setAttributes( { alt: v } ); },
						__nextHasNoMarginBottom: true,
					} ),
					el( FocalPointPicker, {
						label: __( 'Punto focal', 'llaollao-core' ),
						url: a.url,
						value: focal,
						onChange: function ( v ) { setAttributes( { focalPoint: v } ); },
						__nextHasNoMarginBottom: true,
					} ),
					el( Button, {
						onClick: function () {
							setAttributes( { id: undefined, url: '', alt: '', width: 0, height: 0 } );
						},
						variant: 'link',
						isDestructive: true,
					}, __( 'Quitar imagen', 'llaollao-core' ) )
				)
			);

			return el( Fragment, null,
				toolbar,
				inspector,
				el( 'figure', blockProps,
					el( 'img', {
						className: 'llao-slider__img',
						src: a.url,
						alt: a.alt,
						style: {
							objectPosition: Math.round( ( focal.x !== undefined ? focal.x : 0.5 ) * 100 ) + '% ' +
								Math.round( ( focal.y !== undefined ? focal.y : 0.5 ) * 100 ) + '%',
						},
					} )
				)
			);
		},

		// Dinámico: render.php pinta la imagen (srcset/lazy) desde el ID.
		save: function () {
			return null;
		},
	} );
} )( window.wp );
