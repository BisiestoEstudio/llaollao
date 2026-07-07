/**
 * Editor del bloque "History · año" (compilado a mano; equivale a src/index.js).
 * Año editable (RichText) + cuerpo con inner blocks. Escrito con
 * wp.element.createElement (sin node_modules).
 */
( function ( wp ) {
	var registerBlockType = wp.blocks.registerBlockType;
	var be = wp.blockEditor;
	var useBlockProps = be.useBlockProps;
	var InnerBlocks = be.InnerBlocks;
	var RichText = be.RichText;
	var __ = wp.i18n.__;
	var el = wp.element.createElement;

	registerBlockType( 'bisiesto/history-item', {
		edit: function ( props ) {
			var year = props.attributes.year || '';
			var setAttributes = props.setAttributes;
			var blockProps = useBlockProps( { className: 'history-item' } );

			return el( 'div', blockProps,
				el( RichText, {
					tagName: 'span',
					className: 'history-item__year',
					value: year,
					allowedFormats: [],
					onChange: function ( v ) { setAttributes( { year: v } ); },
					placeholder: __( 'Año', 'bisiesto' ),
				} ),
				el( 'div', { className: 'history-item__body' },
					el( InnerBlocks, { templateLock: false } )
				)
			);
		},

		save: function () {
			return el( InnerBlocks.Content );
		},
	} );
} )( window.wp );
