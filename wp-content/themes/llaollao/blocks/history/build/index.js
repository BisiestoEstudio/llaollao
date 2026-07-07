/**
 * Editor del bloque "History" (compilado a mano; equivale a src/index.js).
 * Nav de años en vivo (useSelect) + inner blocks en .history__items. El front lo
 * arma render.php. Escrito con wp.element.createElement (sin node_modules).
 */
( function ( wp ) {
	var registerBlockType = wp.blocks.registerBlockType;
	var be = wp.blockEditor;
	var useBlockProps = be.useBlockProps;
	var useInnerBlocksProps = be.useInnerBlocksProps;
	var InnerBlocks = be.InnerBlocks;
	var useSelect = wp.data.useSelect;
	var el = wp.element.createElement;
	var Fragment = wp.element.Fragment;
	var __ = wp.i18n.__;
	var sprintf = wp.i18n.sprintf;

	var TEMPLATE = [ [ 'bisiesto/history-item' ] ];

	registerBlockType( 'bisiesto/history', {
		edit: function ( props ) {
			var clientId = props.clientId;
			var blockProps = useBlockProps( { className: 'history' } );
			var innerProps = useInnerBlocksProps(
				{ className: 'history__items' },
				{
					allowedBlocks: [ 'bisiesto/history-item' ],
					template: TEMPLATE,
					templateLock: false,
					renderAppender: InnerBlocks.ButtonBlockAppender,
				}
			);

			var years = useSelect( function ( select ) {
				return select( 'core/block-editor' )
					.getBlocks( clientId )
					.filter( function ( b ) { return b.name === 'bisiesto/history-item'; } )
					.map( function ( b ) { return b.attributes.year || ''; } );
			}, [ clientId ] );

			var nav = years.length
				? el( 'nav', { className: 'history__nav', 'aria-label': __( 'Años', 'bisiesto' ) },
					years.map( function ( year, i ) {
						var label = year || sprintf( __( 'Año %d', 'bisiesto' ), i + 1 );
						return el( Fragment, { key: i },
							i > 0 ? el( 'span', { className: 'history__nav-line', 'aria-hidden': 'true' } ) : null,
							el( 'a', {
								className: 'history__nav-link button-tag',
								href: '#',
								'data-text': label,
								onClick: function ( e ) { e.preventDefault(); },
							}, label )
						);
					} )
				)
				: null;

			return el( 'div', blockProps,
				nav,
				el( 'div', innerProps )
			);
		},

		save: function () {
			return el( InnerBlocks.Content );
		},
	} );
} )( window.wp );
