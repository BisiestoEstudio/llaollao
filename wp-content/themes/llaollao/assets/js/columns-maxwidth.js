/**
 * Añade un campo "Ancho máximo" al bloque padre core/columns.
 * - Registra el atributo maxWidth.
 * - Muestra un control en el inspector.
 * - Aplica el max-width (centrado) en el guardado (front) y en la vista del editor.
 * Escrito a mano con wp.element/hooks (el tema no usa node_modules).
 */
( function ( wp ) {
	var el = wp.element.createElement;
	var Fragment = wp.element.Fragment;
	var addFilter = wp.hooks.addFilter;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var cmp = wp.components;
	var PanelBody = cmp.PanelBody;
	var UnitControl = cmp.__experimentalUnitControl || cmp.UnitControl;
	var TextControl = cmp.TextControl;
	var createHigherOrderComponent = wp.compose.createHigherOrderComponent;
	var __ = wp.i18n.__;

	var TARGET = 'core/columns';

	function maxWidthStyle( value ) {
		return {
			maxWidth: value,
			marginLeft: 'auto',
			marginRight: 'auto',
		};
	}

	// 1) Atributo maxWidth en core/columns.
	addFilter(
		'blocks.registerBlockType',
		'bisiesto/columns-max-width/attribute',
		function ( settings, name ) {
			if ( name !== TARGET ) {
				return settings;
			}
			settings.attributes = Object.assign( {}, settings.attributes, {
				maxWidth: { type: 'string', default: '' },
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
			var maxWidth = props.attributes.maxWidth || '';
			var onChange = function ( v ) {
				props.setAttributes( { maxWidth: v || '' } );
			};
			var control = UnitControl
				? el( UnitControl, {
					label: __( 'Ancho máximo', 'bisiesto' ),
					value: maxWidth,
					onChange: onChange,
					units: [
						{ value: 'px', label: 'px' },
						{ value: 'rem', label: 'rem' },
						{ value: '%', label: '%' },
					],
					__next40pxDefaultSize: true,
				} )
				: el( TextControl, {
					label: __( 'Ancho máximo (ej. 800px)', 'bisiesto' ),
					value: maxWidth,
					onChange: onChange,
				} );

			return el( Fragment, {},
				el( BlockEdit, props ),
				el( InspectorControls, {},
					el( PanelBody, { title: __( 'Ancho máximo', 'bisiesto' ), initialOpen: false },
						control
					)
				)
			);
		};
	}, 'withColumnsMaxWidthControl' );
	addFilter( 'editor.BlockEdit', 'bisiesto/columns-max-width/control', withControl );

	// 3) Estilo en el guardado (front).
	addFilter(
		'blocks.getSaveContent.extraProps',
		'bisiesto/columns-max-width/save',
		function ( extraProps, blockType, attributes ) {
			if ( blockType.name !== TARGET || ! attributes.maxWidth ) {
				return extraProps;
			}
			extraProps.style = Object.assign( {}, extraProps.style, maxWidthStyle( attributes.maxWidth ) );
			return extraProps;
		}
	);

	// 4) Vista previa en el editor.
	var withEditorStyle = createHigherOrderComponent( function ( BlockListBlock ) {
		return function ( props ) {
			if ( props.name !== TARGET || ! props.attributes.maxWidth ) {
				return el( BlockListBlock, props );
			}
			var wrapperProps = Object.assign( {}, props.wrapperProps );
			wrapperProps.style = Object.assign( {}, wrapperProps.style, maxWidthStyle( props.attributes.maxWidth ) );
			return el( BlockListBlock, Object.assign( {}, props, { wrapperProps: wrapperProps } ) );
		};
	}, 'withColumnsMaxWidthEditorStyle' );
	addFilter( 'editor.BlockListBlock', 'bisiesto/columns-max-width/editor', withEditorStyle );
} )( window.wp );
