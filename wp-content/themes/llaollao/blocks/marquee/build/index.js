/**
 * Editor del bloque "Marquee" (compilado a mano; equivale a src/index.js).
 * Los textos se editan en el panel lateral (InspectorControls); el lienzo
 * muestra la cinta real con las mismas clases que el front (hereda style.css).
 * Escrito con wp.element.createElement (sin node_modules).
 */
( function ( wp ) {
	var registerBlockType = wp.blocks.registerBlockType;
	var be = wp.blockEditor;
	var useBlockProps = be.useBlockProps;
	var InspectorControls = be.InspectorControls;
	var cmp = wp.components;
	var PanelBody = cmp.PanelBody;
	var TextControl = cmp.TextControl;
	var Button = cmp.Button;
	var __ = wp.i18n.__;
	var el = wp.element.createElement;
	var Fragment = wp.element.Fragment;

	registerBlockType( 'bisiesto/marquee', {
		edit: function ( props ) {
			var items = props.attributes.items || [];
			var setAttributes = props.setAttributes;
			var blockProps = useBlockProps( { className: 'marquee' } );

			function update( i, val ) {
				var next = items.slice();
				next[ i ] = val;
				setAttributes( { items: next } );
			}
			function remove( i ) {
				var next = items.slice();
				next.splice( i, 1 );
				setAttributes( { items: next } );
			}
			function add() {
				setAttributes( { items: items.concat( [ '' ] ) } );
			}

			var filled = items.filter( Boolean );
			function groupChildren() {
				if ( filled.length ) {
					return filled.map( function ( t, i ) {
						return el( 'span', { key: i, className: 'marquee__item' }, t );
					} );
				}
				return el( 'span',
					{ className: 'marquee__item marquee__item--placeholder' },
					__( 'Añade textos en el panel lateral', 'bisiesto' )
				);
			}

			var rows = items.map( function ( text, i ) {
				return el( 'div',
					{ key: i, style: { display: 'flex', alignItems: 'flex-end', gap: 8, marginBottom: 8 } },
					el( 'div', { style: { flex: 1 } },
						el( TextControl, {
							label: __( 'Texto', 'bisiesto' ) + ' ' + ( i + 1 ),
							value: text,
							onChange: function ( v ) { update( i, v ); },
							__nextHasNoMarginBottom: true,
						} )
					),
					el( Button, {
						onClick: function () { remove( i ); },
						variant: 'secondary',
						isDestructive: true,
						icon: 'trash',
						label: __( 'Eliminar', 'bisiesto' ),
					} )
				);
			} );

			var inspector = el( InspectorControls, {},
				el( PanelBody, { title: __( 'Textos', 'bisiesto' ), initialOpen: true },
					rows,
					el( Button, { onClick: add, variant: 'primary' }, __( 'Añadir texto', 'bisiesto' ) )
				)
			);

			var canvas = el( 'div', blockProps,
				el( 'div', { className: 'marquee__group' }, groupChildren() ),
				el( 'div', { className: 'marquee__group', 'aria-hidden': 'true' }, groupChildren() )
			);

			return el( Fragment, {}, inspector, canvas );
		},

		save: function () {
			return null;
		},
	} );
} )( window.wp );
