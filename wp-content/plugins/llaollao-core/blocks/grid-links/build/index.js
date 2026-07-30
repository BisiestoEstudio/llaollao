/**
 * Editor del bloque "Grid links" (compilado a mano; equivale a src/index.js).
 * Zona libre con InnerBlocks arriba y, debajo, la rejilla: cada casilla con su
 * imagen, su título y su enlace. El front lo arma render.php. Escrito con
 * wp.element.createElement (sin node_modules).
 */
( function ( wp ) {
	var registerBlockType = wp.blocks.registerBlockType;
	var be                = wp.blockEditor;
	var useBlockProps     = be.useBlockProps;
	var InnerBlocks       = be.InnerBlocks;
	var RichText          = be.RichText;
	var InspectorControls = be.InspectorControls;
	var MediaUpload       = be.MediaUpload;
	var MediaUploadCheck  = be.MediaUploadCheck;
	var PanelBody         = wp.components.PanelBody;
	var RangeControl      = wp.components.RangeControl;
	var TextControl       = wp.components.TextControl;
	var Button            = wp.components.Button;
	var el                = wp.element.createElement;
	var Fragment          = wp.element.Fragment;
	var __                = wp.i18n.__;

	var EMPTY_ITEM = { imageId: 0, imageUrl: '', imageAlt: '', text: '', url: '' };

	registerBlockType( 'llaollao/grid-links', {
		edit: function ( props ) {
			var attributes    = props.attributes;
			var setAttributes = props.setAttributes;
			var items         = attributes.items || [];
			var blockProps    = useBlockProps( { className: 'llao-grid-links' } );

			function updateItem( index, patch ) {
				setAttributes( {
					items: items.map( function ( item, i ) {
						return i === index ? Object.assign( {}, item, patch ) : item;
					} ),
				} );
			}

			function addItem() {
				setAttributes( { items: items.concat( [ Object.assign( {}, EMPTY_ITEM ) ] ) } );
			}

			function removeItem( index ) {
				setAttributes( {
					items: items.filter( function ( _item, i ) {
						return i !== index;
					} ),
				} );
			}

			// Una casilla: la muestra (imagen + título) y sus mandos debajo.
			var cells = items.map( function ( item, index ) {
				var classes = 'llao-grid-links__item';
				if ( ! item.imageUrl ) {
					classes += ' llao-grid-links__item--empty';
				}

				var tile = el( 'div', { className: classes },
					item.imageUrl
						? el( 'img', {
							className: 'llao-grid-links__img',
							src:       item.imageUrl,
							alt:       item.imageAlt || '',
						} )
						: null,
					el( RichText, {
						tagName:        'span',
						className:      'llao-grid-links__text',
						value:          item.text,
						onChange:       function ( text ) {
							updateItem( index, { text: text } );
						},
						placeholder:    __( 'Título…', 'llaollao-core' ),
						allowedFormats: [ 'core/bold', 'core/italic' ],
					} )
				);

				var picker = el( MediaUploadCheck, null,
					el( MediaUpload, {
						allowedTypes: [ 'image' ],
						value:        item.imageId,
						onSelect:     function ( media ) {
							updateItem( index, {
								imageId:  media.id,
								imageUrl: media.url,
								imageAlt: media.alt || '',
							} );
						},
						render: function ( o ) {
							return el( Button, { onClick: o.open, variant: 'secondary', size: 'small' },
								item.imageUrl
									? __( 'Cambiar imagen', 'llaollao-core' )
									: __( 'Seleccionar imagen', 'llaollao-core' )
							);
						},
					} )
				);

				var controls = el( 'div', { className: 'llao-grid-links__edit' },
					el( TextControl, {
						label:    __( 'Enlace (URL)', 'llaollao-core' ),
						type:     'url',
						value:    item.url || '',
						onChange: function ( url ) {
							updateItem( index, { url: url } );
						},
						placeholder: 'https://',
						__nextHasNoMarginBottom: true,
					} ),
					el( 'div', { className: 'llao-grid-links__edit-row' },
						picker,
						el( Button, {
							onClick:       function () {
								removeItem( index );
							},
							variant:       'link',
							isDestructive: true,
							style:         { fontSize: 11 },
						}, __( 'Eliminar', 'llaollao-core' ) )
					)
				);

				return el( 'div', { key: index, className: 'llao-grid-links__cell' }, tile, controls );
			} );

			var inspector = el( InspectorControls, null,
				el( PanelBody, { title: __( 'Grid links', 'llaollao-core' ), initialOpen: true },
					el( RangeControl, {
						label:    __( 'Separación entre elementos (px)', 'llaollao-core' ),
						value:    attributes.gap,
						onChange: function ( v ) {
							setAttributes( { gap: v === undefined ? 20 : v } );
						},
						min:  0,
						max:  80,
						step: 2,
						__nextHasNoMarginBottom: true,
					} )
				)
			);

			return el( Fragment, null,
				inspector,
				el( 'div', blockProps,
					el( 'div', { className: 'llao-grid-links__header' }, el( InnerBlocks ) ),
					el( 'div', { className: 'llao-grid-links__grid' }, cells ),
					el( Button, {
						onClick: addItem,
						variant: 'primary',
						style:   { marginTop: '1rem' },
					}, __( '+ Añadir enlace', 'llaollao-core' ) )
				)
			);
		},

		// Dinámico: render.php arma la rejilla a partir de los atributos. Solo
		// persistimos los InnerBlocks de la zona libre.
		save: function () {
			return el( InnerBlocks.Content );
		},
	} );
} )( window.wp );
