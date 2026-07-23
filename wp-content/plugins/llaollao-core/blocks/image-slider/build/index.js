/**
 * Editor del bloque "Slider de imágenes" (compilado a mano; equivale a
 * src/index.js). Controles de alto y separación + alta de imágenes en lote, y
 * los inner blocks dentro de .llao-slider__track. El front lo arma render.php.
 * Escrito con wp.element.createElement (sin node_modules).
 */
( function ( wp ) {
	var registerBlockType   = wp.blocks.registerBlockType;
	var createBlock         = wp.blocks.createBlock;
	var be                  = wp.blockEditor;
	var useBlockProps       = be.useBlockProps;
	var useInnerBlocksProps = be.useInnerBlocksProps;
	var InnerBlocks         = be.InnerBlocks;
	var InspectorControls   = be.InspectorControls;
	var MediaUpload         = be.MediaUpload;
	var MediaUploadCheck    = be.MediaUploadCheck;
	var PanelBody           = wp.components.PanelBody;
	var RangeControl        = wp.components.RangeControl;
	var Button              = wp.components.Button;
	var useDispatch         = wp.data.useDispatch;
	var el                  = wp.element.createElement;
	var Fragment            = wp.element.Fragment;
	var __                  = wp.i18n.__;

	var ITEM = 'llaollao/image-slider-item';

	// Atributos del hijo a partir de un adjunto del selector de medios.
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

	registerBlockType( 'llaollao/image-slider', {
		edit: function ( props ) {
			var attributes   = props.attributes;
			var setAttributes = props.setAttributes;
			var clientId     = props.clientId;
			var insertBlocks = useDispatch( 'core/block-editor' ).insertBlocks;

			var style = {
				'--llao-slider-h':        attributes.height + 'px',
				'--llao-slider-h-mobile': attributes.heightMobile + 'px',
				'--llao-slider-gap':      attributes.gap + 'px',
			};

			var blockProps = useBlockProps( { className: 'llao-slider', style: style } );

			// useInnerBlocksProps funde la pista y los inner blocks en un mismo
			// elemento: cada imagen queda como hija directa del flex, sin el
			// wrapper intermedio que Gutenberg añadiría.
			var innerProps = useInnerBlocksProps(
				{ className: 'llao-slider__track' },
				{
					allowedBlocks:  [ ITEM ],
					template:       [ [ ITEM ] ],
					templateLock:   false,
					orientation:    'horizontal',
					renderAppender: InnerBlocks.ButtonBlockAppender,
				}
			);

			// Alta en lote: una imagen del selector = un bloque hijo, al final.
			function addImages( media ) {
				var list = Array.isArray( media ) ? media : [ media ];
				var blocks = list
					.filter( function ( m ) { return m && m.id; } )
					.map( function ( m ) { return createBlock( ITEM, mediaToAttrs( m ) ); } );
				if ( blocks.length ) {
					insertBlocks( blocks, undefined, clientId );
				}
			}

			var inspector = el( InspectorControls, null,
				el( PanelBody, { title: __( 'Slider', 'llaollao-core' ), initialOpen: true },
					el( RangeControl, {
						label: __( 'Alto de la fila (px)', 'llaollao-core' ),
						value: attributes.height,
						onChange: function ( v ) { setAttributes( { height: v || 480 } ); },
						min: 160,
						max: 900,
						step: 10,
						__nextHasNoMarginBottom: true,
					} ),
					el( RangeControl, {
						label: __( 'Alto en móvil (px)', 'llaollao-core' ),
						value: attributes.heightMobile,
						onChange: function ( v ) { setAttributes( { heightMobile: v || 260 } ); },
						min: 120,
						max: 600,
						step: 10,
						__nextHasNoMarginBottom: true,
					} ),
					el( RangeControl, {
						label: __( 'Separación entre imágenes (px)', 'llaollao-core' ),
						value: attributes.gap,
						onChange: function ( v ) { setAttributes( { gap: v === undefined ? 20 : v } ); },
						min: 0,
						max: 80,
						step: 2,
						__nextHasNoMarginBottom: true,
					} )
				),
				el( PanelBody, { title: __( 'Añadir imágenes', 'llaollao-core' ), initialOpen: true },
					el( MediaUploadCheck, null,
						el( MediaUpload, {
							multiple: true,
							allowedTypes: [ 'image' ],
							onSelect: addImages,
							render: function ( o ) {
								return el( Button, { onClick: o.open, variant: 'primary' },
									__( 'Seleccionar varias imágenes', 'llaollao-core' )
								);
							},
						} )
					)
				)
			);

			return el( Fragment, null,
				inspector,
				el( 'div', blockProps, el( 'div', innerProps ) )
			);
		},

		// Dinámico: render.php arma el wrapper y la pista.
		save: function () {
			return el( InnerBlocks.Content );
		},
	} );
} )( window.wp );
