/**
 * Panel de "Datos del producto" en la barra lateral del editor.
 *
 * Campos: Recursos (imágenes/vídeos), Variantes (repeater de textos) y
 * Alérgenos (texto). Escrito a mano con wp.element.createElement, sin build.
 */
( function ( wp ) {
	var el       = wp.element.createElement;
	var Fragment = wp.element.Fragment;
	var __       = wp.i18n.__;
	var c        = wp.components;
	var media    = wp.blockEditor;

	var registerPlugin = wp.plugins.registerPlugin;
	var useSelect      = wp.data.useSelect;
	var useEntityProp  = wp.coreData.useEntityProp;

	// PluginDocumentSettingPanel: en WP 6.6+ vive en wp.editor; antes en wp.editPost.
	var PluginDocumentSettingPanel =
		( wp.editor && wp.editor.PluginDocumentSettingPanel ) ||
		( wp.editPost && wp.editPost.PluginDocumentSettingPanel );

	if ( ! PluginDocumentSettingPanel ) {
		return;
	}

	function ProductFieldsPanel() {
		var postType = useSelect( function ( select ) {
			return select( 'core/editor' ).getCurrentPostType();
		}, [] );

		var entity  = useEntityProp( 'postType', 'producto', 'meta' );
		var meta    = entity[ 0 ] || {};
		var setMeta = entity[ 1 ];

		var recursos  = meta.llao_recursos || [];
		var variantes = meta.llao_variantes || [];
		var alergenos = meta.llao_alergenos || '';

		// Resolver los adjuntos para la previsualización.
		var mediaItems = useSelect( function ( select ) {
			return recursos.map( function ( id ) {
				return select( 'core' ).getMedia( id );
			} );
		}, [ recursos.join( ',' ) ] );

		if ( postType !== 'producto' ) {
			return null;
		}

		function update( key, value ) {
			var next = Object.assign( {}, meta );
			next[ key ] = value;
			setMeta( next );
		}

		// --- Recursos -------------------------------------------------------
		function onSelectRecursos( items ) {
			update( 'llao_recursos', items.map( function ( i ) { return i.id; } ) );
		}
		function removeRecurso( id ) {
			update( 'llao_recursos', recursos.filter( function ( r ) { return r !== id; } ) );
		}

		var recursosPreview = el(
			'div',
			{ style: { display: 'flex', flexWrap: 'wrap', gap: '8px', marginTop: '8px' } },
			mediaItems.map( function ( m ) {
				if ( ! m ) {
					return null;
				}
				var isVideo = m.mime_type && m.mime_type.indexOf( 'video' ) === 0;
				var thumb   = ( ! isVideo && m.media_details && m.media_details.sizes && m.media_details.sizes.thumbnail )
					? m.media_details.sizes.thumbnail.source_url
					: m.source_url;

				return el(
					'div',
					{ key: m.id, style: { position: 'relative', width: '72px' } },
					isVideo
						? el( 'div', {
							style: {
								width: '72px', height: '72px', display: 'flex',
								alignItems: 'center', justifyContent: 'center',
								background: '#f0f0f1', borderRadius: '4px',
								fontSize: '11px', textAlign: 'center', padding: '4px',
								boxSizing: 'border-box'
							}
						}, __( 'Vídeo', 'llaollao-core' ) )
						: el( 'img', {
							src: thumb,
							alt: '',
							style: { width: '72px', height: '72px', objectFit: 'cover', borderRadius: '4px', display: 'block' }
						} ),
					el( c.Button, {
						icon: 'no-alt',
						label: __( 'Quitar', 'llaollao-core' ),
						onClick: function () { removeRecurso( m.id ); },
						style: {
							position: 'absolute', top: '-8px', right: '-8px',
							minWidth: '24px', width: '24px', height: '24px', padding: 0,
							background: '#fff', borderRadius: '50%', boxShadow: '0 0 0 1px #ccc'
						}
					} )
				);
			} )
		);

		var recursosField = el(
			Fragment,
			null,
			el( media.MediaUploadCheck, null,
				el( media.MediaUpload, {
					multiple: true,
					gallery: false,
					allowedTypes: [ 'image', 'video' ],
					value: recursos,
					onSelect: onSelectRecursos,
					render: function ( o ) {
						return el( c.Button, { variant: 'secondary', onClick: o.open },
							recursos.length
								? __( 'Editar recursos', 'llaollao-core' )
								: __( 'Añadir recursos', 'llaollao-core' )
						);
					}
				} )
			),
			recursos.length ? recursosPreview : null
		);

		// --- Variantes ------------------------------------------------------
		function setVariante( index, value ) {
			var next = variantes.slice();
			next[ index ] = value;
			update( 'llao_variantes', next );
		}
		function removeVariante( index ) {
			update( 'llao_variantes', variantes.filter( function ( _v, i ) { return i !== index; } ) );
		}
		function addVariante() {
			update( 'llao_variantes', variantes.concat( [ '' ] ) );
		}

		var variantesField = el(
			Fragment,
			null,
			variantes.map( function ( v, i ) {
				return el(
					'div',
					{ key: i, style: { display: 'flex', alignItems: 'flex-end', gap: '4px' } },
					el( 'div', { style: { flex: 1 } },
						el( c.TextControl, {
							value: v,
							placeholder: __( 'Variante', 'llaollao-core' ),
							onChange: function ( val ) { setVariante( i, val ); },
							__nextHasNoMarginBottom: true
						} )
					),
					el( c.Button, {
						icon: 'trash',
						label: __( 'Eliminar variante', 'llaollao-core' ),
						isDestructive: true,
						onClick: function () { removeVariante( i ); }
					} )
				);
			} ),
			el( c.Button, {
				variant: 'secondary',
				onClick: addVariante,
				style: { marginTop: '8px' }
			}, __( 'Añadir variante', 'llaollao-core' ) )
		);

		// --- Alérgenos ------------------------------------------------------
		var alergenosField = el( c.TextareaControl, {
			value: alergenos,
			rows: 2,
			onChange: function ( val ) { update( 'llao_alergenos', val ); },
			__nextHasNoMarginBottom: true
		} );

		// --- Panel ----------------------------------------------------------
		return el(
			PluginDocumentSettingPanel,
			{ name: 'llao-product-fields', title: __( 'Datos del producto', 'llaollao-core' ), initialOpen: true },
			el( c.BaseControl, {
				label: __( 'Recursos', 'llaollao-core' ),
				help: __( 'Imágenes o vídeos.', 'llaollao-core' ),
				__nextHasNoMarginBottom: true
			}, recursosField ),
			el( 'hr', { style: { margin: '16px 0' } } ),
			el( c.BaseControl, {
				label: __( 'Variantes', 'llaollao-core' ),
				__nextHasNoMarginBottom: true
			}, variantesField ),
			el( 'hr', { style: { margin: '16px 0' } } ),
			el( c.BaseControl, {
				label: __( 'Alérgenos', 'llaollao-core' ),
				__nextHasNoMarginBottom: true
			}, alergenosField )
		);
	}

	registerPlugin( 'llao-product-fields', { render: ProductFieldsPanel } );
} )( window.wp );
