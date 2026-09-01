/**
 * Editor del bloque "Locales". Escrito con wp.element.createElement (sin
 * node_modules): este archivo y build/index.js son el mismo código.
 *
 * Lleva InnerBlocks al principio (por ejemplo, para meter un encabezado antes
 * del buscador/listado/mapa); por eso save() ya no es null, sino
 * InnerBlocks.Content. El resto del bloque lo sigue pintando render.php —
 * la vista previa en el editor es un ServerSideRender debajo del área de
 * InnerBlocks. En la barra lateral solo hay dos ajustes: el texto del
 * buscador y qué país se abre por defecto (el resto —listado, botones de
 * país, mapa— sale solo de los locales publicados).
 */
( function ( wp ) {
	var registerBlockType   = wp.blocks.registerBlockType;
	var InspectorControls   = wp.blockEditor.InspectorControls;
	var useBlockProps       = wp.blockEditor.useBlockProps;
	var useInnerBlocksProps = wp.blockEditor.useInnerBlocksProps;
	var InnerBlocks         = wp.blockEditor.InnerBlocks;
	var PanelBody           = wp.components.PanelBody;
	var TextControl         = wp.components.TextControl;
	var SelectControl       = wp.components.SelectControl;
	var Spinner             = wp.components.Spinner;
	var useSelect           = wp.data.useSelect;
	var ServerSideRender    = wp.serverSideRender;
	var el                  = wp.element.createElement;
	var Fragment            = wp.element.Fragment;
	var __                  = wp.i18n.__;

	registerBlockType( 'llaollao/locales', {
		edit: function ( props ) {
			var attributes    = props.attributes;
			var setAttributes = props.setAttributes;

			// Términos de la taxonomía "país" que ya tienen algún local, para
			// ofrecerlos como país por defecto. per_page tope 100: la REST API
			// no acepta -1.
			var paises = useSelect( function ( select ) {
				return select( 'core' ).getEntityRecords( 'taxonomy', 'pais', {
					per_page: 100,
					orderby:  'name',
					order:    'asc',
					_fields:  'id,name,slug',
				} );
			}, [] );

			var cargando = null === paises;

			var opciones = [
				{ value: '', label: __( '(Automático: el primero con locales)', 'llaollao-core' ) },
			].concat(
				( paises || [] ).map( function ( t ) {
					return { value: t.slug, label: t.name };
				} )
			);

			var inspector = el( InspectorControls, null,
				el( PanelBody, { title: __( 'Locales', 'llaollao-core' ), initialOpen: true },
					el( TextControl, {
						label: __( 'Texto del buscador', 'llaollao-core' ),
						help: __( 'Vacío usa "Barcelona".', 'llaollao-core' ),
						value: attributes.searchPlaceholder,
						onChange: function ( v ) { setAttributes( { searchPlaceholder: v } ); },
						__nextHasNoMarginBottom: true,
					} ),
					cargando
						? el( Spinner, null )
						: el( SelectControl, {
							label: __( 'País por defecto', 'llaollao-core' ),
							value: attributes.defaultPais,
							options: opciones,
							onChange: function ( v ) { setAttributes( { defaultPais: v } ); },
							__nextHasNoMarginBottom: true,
						} )
				)
			);

			var innerBlocksProps = useInnerBlocksProps(
				{ className: 'llao-locales__innerblocks' },
				{
					template: [ [ 'core/heading', { placeholder: __( 'Título', 'llaollao-core' ) } ] ],
					templateLock: false,
				}
			);

			return el( Fragment, null,
				inspector,
				el( 'div', useBlockProps(),
					el( 'div', innerBlocksProps ),
					el( ServerSideRender, {
						block: 'llaollao/locales',
						attributes: attributes,
					} )
				)
			);
		},

		// El área de InnerBlocks sí se guarda (encabezado y lo que se añada
		// antes del buscador/listado/mapa); el resto del bloque sigue siendo
		// dinámico y lo pinta render.php a partir de ese contenido ($content).
		save: function () {
			return el( InnerBlocks.Content );
		},
	} );
} )( window.wp );
