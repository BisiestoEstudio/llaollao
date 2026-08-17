/**
 * Editor del bloque "Vista producto con slide vertical". Escrito con
 * wp.element.createElement (sin node_modules): este archivo y build/index.js
 * son el mismo código.
 *
 * No guarda marcado (save: null); lo pinta render.php, así que la vista previa
 * es un ServerSideRender. El producto se elige en la barra lateral; si no se
 * elige ninguno, el bloque tira del producto que se esté mostrando, que es lo
 * que permite usarlo también dentro de una plantilla de single.
 */
( function ( wp ) {
	var registerBlockType = wp.blocks.registerBlockType;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var useBlockProps     = wp.blockEditor.useBlockProps;
	var PanelBody         = wp.components.PanelBody;
	var ComboboxControl   = wp.components.ComboboxControl;
	var Spinner           = wp.components.Spinner;
	var RangeControl      = wp.components.RangeControl;
	var TextControl       = wp.components.TextControl;
	var ToggleControl     = wp.components.ToggleControl;
	var Placeholder       = wp.components.Placeholder;
	var useSelect         = wp.data.useSelect;
	var ServerSideRender  = wp.serverSideRender;
	var el                = wp.element.createElement;
	var Fragment          = wp.element.Fragment;
	var __                = wp.i18n.__;

	function Aviso() {
		return el( Placeholder, {
			icon: 'images-alt',
			label: __( 'Vista producto con slide vertical', 'llaollao-core' ),
			instructions: __( 'Elige un producto en la barra lateral. Sin elegir ninguno, el bloque usa el producto que se esté mostrando, así que aquí no hay nada que pintar.', 'llaollao-core' ),
		} );
	}

	registerBlockType( 'llaollao/vista-producto', {
		edit: function ( props ) {
			var attributes    = props.attributes;
			var setAttributes = props.setAttributes;

			// Id del producto que se está editando, si lo hay. El endpoint de
			// block-renderer lo usa para montar el contexto y que render.php
			// encuentre el producto; en el editor del sitio no existe.
			var postId = useSelect( function ( select ) {
				var editor = select( 'core/editor' );
				return editor && editor.getCurrentPostId ? editor.getCurrentPostId() : null;
			}, [] );

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

			var opciones = ( productos || [] ).map( function ( p ) {
				return {
					value: p.id,
					label: ( p.title && p.title.rendered ) || __( '(sin título)', 'llaollao-core' ),
				};
			} );

			var inspector = el( InspectorControls, null,
				el( PanelBody, { title: __( 'Producto', 'llaollao-core' ), initialOpen: true },
					null === productos
						? el( Spinner, null )
						: el( ComboboxControl, {
							label: __( 'Producto a mostrar', 'llaollao-core' ),
							help: __( 'Sin elegir ninguno, se usa el producto que se esté mostrando (útil dentro de una plantilla de single).', 'llaollao-core' ),
							value: attributes.productId || null,
							options: opciones,
							onChange: function ( value ) {
								setAttributes( { productId: value ? parseInt( value, 10 ) : 0 } );
							},
							__nextHasNoMarginBottom: true,
						} )
				),
				el( PanelBody, { title: __( 'Columnas', 'llaollao-core' ), initialOpen: true },
					el( RangeControl, {
						label: __( 'Separación entre columnas (%)', 'llaollao-core' ),
						value: attributes.columnGap,
						onChange: function ( v ) { setAttributes( { columnGap: v === undefined ? 7 : v } ); },
						min: 0,
						max: 20,
						step: 0.5,
						__nextHasNoMarginBottom: true,
					} ),
					el( RangeControl, {
						label: __( 'Ancho máximo de la columna izquierda (px)', 'llaollao-core' ),
						value: attributes.leftMaxWidth,
						onChange: function ( v ) { setAttributes( { leftMaxWidth: v || 400 } ); },
						min: 240,
						max: 720,
						step: 10,
						__nextHasNoMarginBottom: true,
					} )
				),
				el( PanelBody, { title: __( 'Etiquetas, hermanos y alérgenos', 'llaollao-core' ), initialOpen: false },
					el( ToggleControl, {
						label: __( 'Mostrar la nube de etiquetas de Tipo', 'llaollao-core' ),
						checked: !! attributes.showTipos,
						onChange: function ( v ) { setAttributes( { showTipos: !! v } ); },
						__nextHasNoMarginBottom: true,
					} ),
					el( ToggleControl, {
						label: __( 'Mostrar los hermanos como etiquetas', 'llaollao-core' ),
						help: __( 'Solo salen si el producto tiene padre y algún hermano más.', 'llaollao-core' ),
						checked: !! attributes.showSiblings,
						onChange: function ( v ) { setAttributes( { showSiblings: !! v } ); },
						__nextHasNoMarginBottom: true,
					} ),
					el( TextControl, {
						label: __( 'Título del desplegable de alérgenos', 'llaollao-core' ),
						value: attributes.alergenosLabel,
						onChange: function ( v ) { setAttributes( { alergenosLabel: v } ); },
						__nextHasNoMarginBottom: true,
					} )
				)
			);

			return el( Fragment, null,
				inspector,
				el( 'div', useBlockProps(),
					el( ServerSideRender, {
						block: 'llaollao/vista-producto',
						attributes: attributes,
						urlQueryArgs: postId ? { post_id: postId } : undefined,
						EmptyResponsePlaceholder: Aviso,
						ErrorResponsePlaceholder: Aviso,
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
