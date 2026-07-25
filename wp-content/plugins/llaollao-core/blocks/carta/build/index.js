/**
 * Editor del bloque "Carta". Escrito con wp.element.createElement (sin
 * node_modules): este archivo y build/index.js son el mismo código.
 *
 * El bloque no guarda marcado (save: null); todo lo pinta render.php, así que
 * la vista previa del editor es un ServerSideRender. En la barra lateral: la
 * lista de productos elegidos a mano —que es también su orden en el mosaico— y
 * los ajustes de la nube de etiquetas y la separación.
 */
( function ( wp ) {
	var registerBlockType  = wp.blocks.registerBlockType;
	var InspectorControls  = wp.blockEditor.InspectorControls;
	var useBlockProps      = wp.blockEditor.useBlockProps;
	var PanelBody          = wp.components.PanelBody;
	var ComboboxControl    = wp.components.ComboboxControl;
	var RangeControl       = wp.components.RangeControl;
	var TextControl        = wp.components.TextControl;
	var ToggleControl      = wp.components.ToggleControl;
	var Button             = wp.components.Button;
	var Spinner            = wp.components.Spinner;
	var useSelect          = wp.data.useSelect;
	var ServerSideRender   = wp.serverSideRender;
	var el                 = wp.element.createElement;
	var Fragment           = wp.element.Fragment;
	var __                 = wp.i18n.__;

	// Mueve un elemento del array de una posición a otra (devuelve copia).
	function move( list, from, to ) {
		if ( to < 0 || to >= list.length ) {
			return list;
		}
		var next = list.slice();
		var item = next.splice( from, 1 )[ 0 ];
		next.splice( to, 0, item );
		return next;
	}

	registerBlockType( 'llaollao/carta', {
		edit: function ( props ) {
			var attributes    = props.attributes;
			var setAttributes = props.setAttributes;
			var ids           = attributes.productIds || [];

			// Todos los productos publicados, para el selector. per_page tope
			// 100: la REST API no acepta -1.
			var productos = useSelect( function ( select ) {
				return select( 'core' ).getEntityRecords( 'postType', 'producto', {
					per_page: 100,
					status:   'publish',
					orderby:  'title',
					order:    'asc',
					_fields:  'id,title',
				} );
			}, [] );

			var cargando = null === productos;

			// Índice id → título, para pintar la lista de elegidos.
			var titulos = {};
			( productos || [] ).forEach( function ( p ) {
				titulos[ p.id ] = ( p.title && p.title.rendered ) || __( '(sin título)', 'llaollao-core' );
			} );

			// Opciones del combobox: solo los que no están ya en la lista.
			var opciones = ( productos || [] )
				.filter( function ( p ) { return ids.indexOf( p.id ) === -1; } )
				.map( function ( p ) {
					return { value: p.id, label: titulos[ p.id ] };
				} );

			function setIds( next ) {
				setAttributes( { productIds: next } );
			}

			var listaProductos = ids.length
				? el( 'ul', { className: 'llao-carta-list' },
					ids.map( function ( id, i ) {
						return el( 'li', { key: id, className: 'llao-carta-list__item' },
							el( 'span', { className: 'llao-carta-list__label' },
								titulos[ id ] || ( cargando ? '…' : __( '(producto no disponible)', 'llaollao-core' ) )
							),
							el( Button, {
								icon: 'arrow-up-alt2',
								label: __( 'Subir', 'llaollao-core' ),
								size: 'small',
								disabled: 0 === i,
								onClick: function () { setIds( move( ids, i, i - 1 ) ); },
							} ),
							el( Button, {
								icon: 'arrow-down-alt2',
								label: __( 'Bajar', 'llaollao-core' ),
								size: 'small',
								disabled: i === ids.length - 1,
								onClick: function () { setIds( move( ids, i, i + 1 ) ); },
							} ),
							el( Button, {
								icon: 'no-alt',
								label: __( 'Quitar', 'llaollao-core' ),
								size: 'small',
								isDestructive: true,
								onClick: function () {
									setIds( ids.filter( function ( x ) { return x !== id; } ) );
								},
							} )
						);
					} )
				)
				: el( 'p', { className: 'llao-carta-list__empty' },
					__( 'Sin selección: se muestran todos los productos publicados, por el campo Orden.', 'llaollao-core' )
				);

			var inspector = el( InspectorControls, null,
				el( PanelBody, { title: __( 'Productos', 'llaollao-core' ), initialOpen: true },
					listaProductos,
					cargando
						? el( Spinner, null )
						: el( ComboboxControl, {
							label: __( 'Añadir producto', 'llaollao-core' ),
							help: __( 'El orden de la lista es el orden del mosaico.', 'llaollao-core' ),
							value: null,
							options: opciones,
							onChange: function ( value ) {
								var id = parseInt( value, 10 );
								if ( id && ids.indexOf( id ) === -1 ) {
									setIds( ids.concat( [ id ] ) );
								}
							},
							__nextHasNoMarginBottom: true,
						} )
				),
				el( PanelBody, { title: __( 'Etiquetas', 'llaollao-core' ), initialOpen: false },
					el( ToggleControl, {
						label: __( 'Mostrar la nube de etiquetas', 'llaollao-core' ),
						checked: !! attributes.showFilters,
						onChange: function ( v ) { setAttributes( { showFilters: !! v } ); },
						__nextHasNoMarginBottom: true,
					} ),
					attributes.showFilters && el( TextControl, {
						label: __( 'Texto de la etiqueta "todos"', 'llaollao-core' ),
						value: attributes.allLabel,
						onChange: function ( v ) { setAttributes( { allLabel: v } ); },
						__nextHasNoMarginBottom: true,
					} )
				),
				el( PanelBody, { title: __( 'Mosaico', 'llaollao-core' ), initialOpen: false },
					el( RangeControl, {
						label: __( 'Separación entre cards (px)', 'llaollao-core' ),
						value: attributes.gap,
						onChange: function ( v ) { setAttributes( { gap: v === undefined ? 20 : v } ); },
						min: 0,
						max: 80,
						step: 2,
						__nextHasNoMarginBottom: true,
					} )
				)
			);

			return el( Fragment, null,
				inspector,
				el( 'div', useBlockProps(),
					el( ServerSideRender, {
						block: 'llaollao/carta',
						attributes: attributes,
					} )
				)
			);
		},

		// Dinámico: el marcado lo arma render.php.
		save: function () {
			return null;
		},
	} );
} )( window.wp );
