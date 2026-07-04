/**
 * Editor del bloque "Side images" (compilado a mano; equivale a src/index.js).
 * Contenedor de dos columnas bisiesto/side-image-column con plantilla bloqueada.
 * Escrito con wp.element.createElement porque el tema no tiene node_modules.
 */
( function ( wp ) {
	var registerBlockType = wp.blocks.registerBlockType;
	var be = wp.blockEditor;
	var useBlockProps = be.useBlockProps;
	var useInnerBlocksProps = be.useInnerBlocksProps;
	var InnerBlocks = be.InnerBlocks;
	var el = wp.element.createElement;

	var TEMPLATE = [
		[ 'bisiesto/side-image-column', {} ],
		[ 'bisiesto/side-image-column', {} ],
	];

	registerBlockType( 'bisiesto/side-images', {
		edit: function () {
			var blockProps = useBlockProps( { className: 'side-images' } );
			// useInnerBlocksProps funde wrapper e inner blocks en un mismo elemento:
			// así las columnas son hijas directas del flex (sin el wrapper
			// intermedio que Gutenberg añadiría) y se ven en dos columnas también
			// en el editor.
			var innerProps = useInnerBlocksProps( blockProps, {
				template: TEMPLATE,
				templateLock: 'all',
			} );
			return el( 'div', innerProps );
		},

		save: function () {
			var blockProps = useBlockProps.save( { className: 'side-images' } );
			return el( 'div', blockProps, el( InnerBlocks.Content ) );
		},
	} );
} )( window.wp );
