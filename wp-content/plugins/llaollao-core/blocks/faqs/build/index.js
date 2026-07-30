/**
 * Editor del bloque "FAQs" (compilado a mano; equivale a src/index.js).
 * Cabecera libre con InnerBlocks y lista de preguntas editable. El front lo
 * arma render.php. Escrito con wp.element.createElement (sin node_modules).
 */
( function ( wp ) {
	var registerBlockType = wp.blocks.registerBlockType;
	var be                = wp.blockEditor;
	var useBlockProps     = be.useBlockProps;
	var InnerBlocks       = be.InnerBlocks;
	var RichText          = be.RichText;
	var Button            = wp.components.Button;
	var el                = wp.element.createElement;
	var __                = wp.i18n.__;

	var EMPTY_ITEM = { question: '', answer: '' };

	function chevron() {
		return el( 'svg', {
			className:       'faq__chevron',
			viewBox:         '0 0 20 20',
			strokeWidth:     '1.5',
			strokeLinecap:   'round',
			strokeLinejoin:  'round',
			'aria-hidden':   'true',
		}, el( 'path', { d: 'M5 7.5L10 12.5L15 7.5' } ) );
	}

	registerBlockType( 'llaollao/faqs', {
		edit: function ( props ) {
			var setAttributes = props.setAttributes;
			var items         = props.attributes.items || [];
			var blockProps    = useBlockProps( { className: 'faqs' } );

			function updateItem( index, patch ) {
				setAttributes( {
					items: items.map( function ( item, i ) {
						return i === index ? Object.assign( {}, item, patch ) : item;
					} ),
				} );
			}

			function addItem() {
				setAttributes( { items: items.concat( [ Object.assign( {}, EMPTY_ITEM ) ] ) } );
			}

			function removeItem( index ) {
				setAttributes( {
					items: items.filter( function ( _item, i ) {
						return i !== index;
					} ),
				} );
			}

			var faqs = items.map( function ( item, index ) {
				return el( 'div', { key: index, className: 'faq faq--editing' },
					el( 'div', { className: 'faq__summary' },
						el( RichText, {
							tagName:        'span',
							className:      'faq__question',
							value:          item.question,
							onChange:       function ( question ) {
								updateItem( index, { question: question } );
							},
							placeholder:    __( 'Pregunta…', 'llaollao-core' ),
							allowedFormats: [ 'core/bold', 'core/italic' ],
						} ),
						chevron()
					),
					el( RichText, {
						tagName:     'p',
						className:   'faq__answer',
						value:       item.answer,
						onChange:    function ( answer ) {
							updateItem( index, { answer: answer } );
						},
						placeholder: __( 'Respuesta…', 'llaollao-core' ),
					} ),
					el( Button, {
						onClick:       function () {
							removeItem( index );
						},
						variant:       'link',
						isDestructive: true,
						style:         { fontSize: 11, marginBottom: '0.5rem' },
					}, __( 'Eliminar', 'llaollao-core' ) )
				);
			} );

			faqs.push( el( Button, {
				key:     'add',
				onClick: addItem,
				variant: 'primary',
				style:   { marginTop: '0.5rem', alignSelf: 'flex-start' },
			}, __( '+ Añadir FAQ', 'llaollao-core' ) ) );

			return el( 'div', blockProps,
				el( 'div', { className: 'faqs__header' }, el( InnerBlocks ) ),
				el( 'div', { className: 'faqs__list' }, faqs )
			);
		},

		// Bloque dinámico: el render lo hace render.php. Solo persistimos los
		// InnerBlocks de la cabecera (las FAQs salen de los atributos en el
		// servidor). Así evitamos el error de validación / recuperación al
		// traducir con Polylang.
		save: function () {
			return el( InnerBlocks.Content );
		},
	} );
} )( window.wp );
