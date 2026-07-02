/**
 * Registro del bloque en el editor, con panel de ajustes y vista previa real
 * mediante ServerSideRender. Sin proceso de build (JS plano).
 */
( function ( wp ) {
	var el                = wp.element.createElement;
	var Fragment          = wp.element.Fragment;
	var registerBlockType = wp.blocks.registerBlockType;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var useBlockProps     = wp.blockEditor.useBlockProps;
	var PanelBody         = wp.components.PanelBody;
	var ToggleControl     = wp.components.ToggleControl;
	var TextControl       = wp.components.TextControl;
	var ServerSideRender  = wp.serverSideRender;
	var __                = wp.i18n.__;

	registerBlockType( 'bisiesto/table-of-contents', {
		apiVersion: 3,
		title: __( 'Tabla de contenidos', 'tabla-contenidos-bisiesto' ),
		category: 'widgets',
		icon: 'list-view',

		edit: function ( props ) {
			var attributes    = props.attributes;
			var setAttributes = props.setAttributes;
			var blockProps    = useBlockProps();

			return el(
				Fragment,
				{},
				el(
					InspectorControls,
					{},
					el(
						PanelBody,
						{ title: __( 'Ajustes', 'tabla-contenidos-bisiesto' ), initialOpen: true },
						el( TextControl, {
							label: __( 'Título', 'tabla-contenidos-bisiesto' ),
							value: attributes.title,
							placeholder: __( 'In this post', 'tabla-contenidos-bisiesto' ),
							onChange: function ( value ) { setAttributes( { title: value } ); }
						} ),
						el( ToggleControl, {
							label: __( 'Fijar al hacer scroll (sticky)', 'tabla-contenidos-bisiesto' ),
							checked: !! attributes.sticky,
							onChange: function ( value ) { setAttributes( { sticky: value } ); }
						} )
					)
				),
				el(
					'div',
					blockProps,
					el( ServerSideRender, {
						block: 'bisiesto/table-of-contents',
						attributes: attributes
					} )
				)
			);
		},

		// Bloque dinámico: el HTML lo genera render.php.
		save: function () { return null; }
	} );
} )( window.wp );
