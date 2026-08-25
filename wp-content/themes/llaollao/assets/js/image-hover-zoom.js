/**
 * Añade al bloque core/image la opción "Zoom con hover" en el panel lateral
 * del editor (junto a Estilos, Filtros, Dimensiones, Borde y sombra...). El
 * zoom real lo hace el CSS (.has-hover-zoom en blocks.css); aquí solo se
 * gestiona el atributo, el control y la clase, tanto en el guardado como en
 * el lienzo del editor. Mismo patrón que assets/js/spacer-hide-mobile.js.
 * Escrito a mano con wp.element/hooks (el tema no usa node_modules).
 */
( function ( wp ) {
	var el = wp.element.createElement;
	var Fragment = wp.element.Fragment;
	var addFilter = wp.hooks.addFilter;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var cmp = wp.components;
	var PanelBody = cmp.PanelBody;
	var ToggleControl = cmp.ToggleControl;
	var createHigherOrderComponent = wp.compose.createHigherOrderComponent;
	var __ = wp.i18n.__;

	var TARGET = 'core/image';
	var CLASS_NAME = 'has-hover-zoom';

	// 1) Atributo hoverZoom en core/image.
	addFilter(
		'blocks.registerBlockType',
		'bisiesto/image-hover-zoom/attribute',
		function ( settings, name ) {
			if ( name !== TARGET ) {
				return settings;
			}
			settings.attributes = Object.assign( {}, settings.attributes, {
				hoverZoom: { type: 'boolean', default: false },
			} );
			return settings;
		}
	);

	// 2) Control en el panel lateral (Configuración del bloque).
	var withControl = createHigherOrderComponent( function ( BlockEdit ) {
		return function ( props ) {
			if ( props.name !== TARGET ) {
				return el( BlockEdit, props );
			}

			return el( Fragment, {},
				el( BlockEdit, props ),
				el( InspectorControls, {},
					el( PanelBody, { title: __( 'Zoom con hover', 'bisiesto' ), initialOpen: false },
						el( ToggleControl, {
							label: __( 'Zoom con hover', 'bisiesto' ),
							checked: !! props.attributes.hoverZoom,
							onChange: function ( v ) {
								props.setAttributes( { hoverZoom: !! v } );
							},
							__nextHasNoMarginBottom: true,
						} )
					)
				)
			);
		};
	}, 'withImageHoverZoomControl' );
	addFilter( 'editor.BlockEdit', 'bisiesto/image-hover-zoom/control', withControl );

	// 3) Clase en el guardado (front).
	addFilter(
		'blocks.getSaveContent.extraProps',
		'bisiesto/image-hover-zoom/save',
		function ( extraProps, blockType, attributes ) {
			if ( blockType.name !== TARGET || ! attributes.hoverZoom ) {
				return extraProps;
			}
			extraProps.className = ( extraProps.className ? extraProps.className + ' ' : '' ) + CLASS_NAME;
			return extraProps;
		}
	);

	// 4) Misma clase en el lienzo del editor: blocks.css también se carga ahí
	// (add_editor_style), así que el zoom se puede previsualizar igual.
	var withEditorClass = createHigherOrderComponent( function ( BlockListBlock ) {
		return function ( props ) {
			if ( props.name !== TARGET || ! props.attributes.hoverZoom ) {
				return el( BlockListBlock, props );
			}
			var wrapperProps = Object.assign( {}, props.wrapperProps );
			wrapperProps.className = ( wrapperProps.className ? wrapperProps.className + ' ' : '' ) + CLASS_NAME;
			return el( BlockListBlock, Object.assign( {}, props, { wrapperProps: wrapperProps } ) );
		};
	}, 'withImageHoverZoomEditorClass' );
	addFilter( 'editor.BlockListBlock', 'bisiesto/image-hover-zoom/editor', withEditorClass );
} )( window.wp );
