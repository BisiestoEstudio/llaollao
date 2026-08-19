/**
 * Añade al bloque core/spacer la opción de ocultarlo en móvil.
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

	var TARGET = 'core/spacer';
	var CLASS_NAME = 'is-hidden-mobile';

	// 1) Atributo hideOnMobile en core/spacer.
	addFilter(
		'blocks.registerBlockType',
		'bisiesto/spacer-hide-mobile/attribute',
		function ( settings, name ) {
			if ( name !== TARGET ) {
				return settings;
			}
			settings.attributes = Object.assign( {}, settings.attributes, {
				hideOnMobile: { type: 'boolean', default: false },
			} );
			return settings;
		}
	);

	// 2) Control en el inspector.
	var withControl = createHigherOrderComponent( function ( BlockEdit ) {
		return function ( props ) {
			if ( props.name !== TARGET ) {
				return el( BlockEdit, props );
			}

			return el( Fragment, {},
				el( BlockEdit, props ),
				el( InspectorControls, {},
					el( PanelBody, { title: __( 'Visibilidad', 'bisiesto' ), initialOpen: true },
						el( ToggleControl, {
							label: __( 'Ocultar en móvil', 'bisiesto' ),
							checked: !! props.attributes.hideOnMobile,
							onChange: function ( v ) {
								props.setAttributes( { hideOnMobile: !! v } );
							},
							__nextHasNoMarginBottom: true,
						} )
					)
				)
			);
		};
	}, 'withSpacerHideMobileControl' );
	addFilter( 'editor.BlockEdit', 'bisiesto/spacer-hide-mobile/control', withControl );

	// 3) Clase en el guardado (front): la ocultación real la hace el CSS
	// (.is-hidden-mobile en blocks.css, @media max-width:768px).
	addFilter(
		'blocks.getSaveContent.extraProps',
		'bisiesto/spacer-hide-mobile/save',
		function ( extraProps, blockType, attributes ) {
			if ( blockType.name !== TARGET || ! attributes.hideOnMobile ) {
				return extraProps;
			}
			extraProps.className = ( extraProps.className ? extraProps.className + ' ' : '' ) + CLASS_NAME;
			return extraProps;
		}
	);
} )( window.wp );
