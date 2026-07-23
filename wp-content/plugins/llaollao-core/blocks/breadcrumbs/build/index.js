/**
 * Representación del bloque "Migas de pan" en el editor.
 *
 * Es un bloque dinámico (render.php). Aquí solo pintamos una muestra estática
 * para el editor. Sin build: JS a mano con wp.element.createElement.
 */
( function ( wp ) {
	var el                = wp.element.createElement;
	var __                = wp.i18n.__;
	var registerBlockType = wp.blocks.registerBlockType;
	var useBlockProps     = wp.blockEditor.useBlockProps;

	registerBlockType( 'llaollao/breadcrumbs', {
		edit: function () {
			var props  = useBlockProps( { className: 'llao-breadcrumbs' } );
			var sample = __( 'Carta', 'llaollao-core' ) + ' / ' +
				__( 'Productos', 'llaollao-core' ) + ' / ' +
				__( 'Tipo', 'llaollao-core' ) + ' / ' +
				__( 'Producto', 'llaollao-core' );
			return el( 'nav', props, el( 'span', { style: { opacity: 0.7 } }, sample ) );
		},
		save: function () {
			return null;
		}
	} );
} )( window.wp );
