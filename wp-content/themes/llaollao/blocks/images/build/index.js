/**
 * Editor del bloque "Images" (compilado a mano; equivale a src/index.js).
 * Bloque estático de maquetación: dos filas de core/columns con huecos para
 * Video Llaollao y un centro libre. Escrito con wp.element.createElement.
 */
( function ( wp ) {
	var registerBlockType = wp.blocks.registerBlockType;
	var be = wp.blockEditor;
	var useBlockProps = be.useBlockProps;
	var InnerBlocks = be.InnerBlocks;
	var el = wp.element.createElement;

	var TEMPLATE = [
		[ 'core/columns', { className: 'images__row images__row--top' }, [
			[ 'core/column', { width: '24%' }, [ [ 'bisiesto/video-llaollao' ] ] ],
			[ 'core/column', { width: '52%', templateLock: false }, [] ],
			[ 'core/column', { width: '24%' }, [ [ 'bisiesto/video-llaollao' ] ] ],
		] ],
		[ 'core/columns', { className: 'images__row images__row--bottom' }, [
			[ 'core/column', {}, [ [ 'bisiesto/video-llaollao' ] ] ],
			[ 'core/column', {}, [ [ 'bisiesto/video-llaollao' ] ] ],
		] ],
	];

	registerBlockType( 'bisiesto/images', {
		edit: function () {
			var blockProps = useBlockProps( { className: 'images' } );
			return el(
				'div',
				blockProps,
				el( InnerBlocks, { template: TEMPLATE, templateLock: 'all' } )
			);
		},

		save: function () {
			var blockProps = useBlockProps.save( { className: 'images' } );
			return el( 'div', blockProps, el( InnerBlocks.Content ) );
		},
	} );
} )( window.wp );
