/**
 * Editor del bloque "Video hero" (compilado a mano; equivale a src/index.js).
 * Escrito con wp.element.createElement para no depender del build de wp-scripts.
 */
( function ( wp ) {
	var registerBlockType = wp.blocks.registerBlockType;
	var be = wp.blockEditor;
	var useBlockProps = be.useBlockProps;
	var InnerBlocks = be.InnerBlocks;
	var InspectorControls = be.InspectorControls;
	var MediaUpload = be.MediaUpload;
	var MediaUploadCheck = be.MediaUploadCheck;
	var PanelBody = wp.components.PanelBody;
	var Button = wp.components.Button;
	var __ = wp.i18n.__;
	var el = wp.element.createElement;
	var Fragment = wp.element.Fragment;

	var ALLOWED_BLOCKS = [ 'core/heading', 'core/paragraph', 'core/buttons' ];
	var TEMPLATE = [
		[ 'core/heading', { level: 1, placeholder: __( 'Título del hero…', 'bisiesto' ) } ],
	];

	registerBlockType( 'bisiesto/video-hero', {
		edit: function ( props ) {
			var a = props.attributes;
			var setAttributes = props.setAttributes;
			var videoUrl = a.videoUrl;
			var videoId = a.videoId;
			var posterUrl = a.posterUrl;
			var posterId = a.posterId;
			var posterAlt = a.posterAlt;

			var blockProps = useBlockProps( { className: 'video-hero' } );

			var videoPanel = el(
				PanelBody,
				{ title: __( 'Vídeo', 'bisiesto' ), initialOpen: true },
				el(
					MediaUploadCheck,
					null,
					el( MediaUpload, {
						onSelect: function ( media ) {
							setAttributes( { videoUrl: media.url, videoId: media.id } );
						},
						allowedTypes: [ 'video' ],
						value: videoId,
						render: function ( o ) {
							return el(
								'div',
								{ style: { display: 'flex', flexDirection: 'column', gap: 8 } },
								videoUrl &&
									el( 'video', {
										src: videoUrl,
										muted: true,
										controls: true,
										style: { width: '100%', borderRadius: 4 },
									} ),
								el(
									Button,
									{ onClick: o.open, variant: videoUrl ? 'secondary' : 'primary' },
									videoUrl ? __( 'Cambiar vídeo', 'bisiesto' ) : __( 'Seleccionar vídeo', 'bisiesto' )
								),
								videoUrl &&
									el(
										Button,
										{
											onClick: function () {
												setAttributes( { videoUrl: '', videoId: undefined } );
											},
											variant: 'link',
											isDestructive: true,
										},
										__( 'Eliminar vídeo', 'bisiesto' )
									)
							);
						},
					} )
				)
			);

			var posterPanel = el(
				PanelBody,
				{ title: __( 'Portada (opcional)', 'bisiesto' ), initialOpen: false },
				el(
					'p',
					{ style: { marginTop: 0 } },
					__( 'Si la dejas vacía, se usa el primer frame del vídeo.', 'bisiesto' )
				),
				el(
					MediaUploadCheck,
					null,
					el( MediaUpload, {
						onSelect: function ( media ) {
							setAttributes( {
								posterUrl: media.url,
								posterId: media.id,
								posterAlt: media.alt || '',
							} );
						},
						allowedTypes: [ 'image' ],
						value: posterId,
						render: function ( o ) {
							return el(
								'div',
								{ style: { display: 'flex', flexDirection: 'column', gap: 8 } },
								posterUrl &&
									el( 'img', {
										src: posterUrl,
										alt: posterAlt,
										style: { width: '100%', borderRadius: 4 },
									} ),
								el(
									Button,
									{ onClick: o.open, variant: posterUrl ? 'secondary' : 'primary' },
									posterUrl ? __( 'Cambiar portada', 'bisiesto' ) : __( 'Seleccionar portada', 'bisiesto' )
								),
								posterUrl &&
									el(
										Button,
										{
											onClick: function () {
												setAttributes( { posterUrl: '', posterId: undefined, posterAlt: '' } );
											},
											variant: 'link',
											isDestructive: true,
										},
										__( 'Eliminar portada', 'bisiesto' )
									)
							);
						},
					} )
				)
			);

			var canvas = el(
				'div',
				blockProps,
				videoUrl &&
					el( 'video', {
						className: 'video-hero__video',
						src: videoUrl,
						muted: true,
						loop: true,
						playsInline: true,
						poster: posterUrl || undefined,
					} ),
				posterUrl &&
					el( 'img', { className: 'video-hero__poster', src: posterUrl, alt: posterAlt } ),
				el(
					'div',
					{ className: 'video-hero__content' },
					el( InnerBlocks, { allowedBlocks: ALLOWED_BLOCKS, template: TEMPLATE } )
				)
			);

			return el(
				Fragment,
				null,
				el( InspectorControls, null, videoPanel, posterPanel ),
				canvas
			);
		},

		// Bloque dinámico: el vídeo/portada los pinta render.php; solo persistimos InnerBlocks.
		save: function () {
			return el( InnerBlocks.Content );
		},
	} );
} )( window.wp );
