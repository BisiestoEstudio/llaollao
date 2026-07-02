/**
 * Editor del bloque "Video Llaollao" (compilado a mano; equivale a src/index.js).
 * Escrito con wp.element.createElement para no depender del build de wp-scripts.
 */
( function ( wp ) {
	var registerBlockType = wp.blocks.registerBlockType;
	var be = wp.blockEditor;
	var useBlockProps = be.useBlockProps;
	var InspectorControls = be.InspectorControls;
	var MediaUpload = be.MediaUpload;
	var MediaUploadCheck = be.MediaUploadCheck;
	var PanelBody = wp.components.PanelBody;
	var Button = wp.components.Button;
	var RadioControl = wp.components.RadioControl;
	var ToggleControl = wp.components.ToggleControl;
	var __ = wp.i18n.__;
	var el = wp.element.createElement;
	var Fragment = wp.element.Fragment;

	registerBlockType( 'bisiesto/video-llaollao', {
		edit: function ( props ) {
			var a = props.attributes;
			var setAttributes = props.setAttributes;
			var videoUrl = a.videoUrl;
			var videoId = a.videoId;
			var posterUrl = a.posterUrl;
			var posterId = a.posterId;
			var posterAlt = a.posterAlt;
			var orientation = a.orientation === 'horizontal' ? 'horizontal' : 'vertical';
			var hideOnMobile = a.hideOnMobile;

			var blockProps = useBlockProps( {
				className: 'video-llaollao is-' + orientation + ( hideOnMobile ? ' is-hidden-mobile' : '' ),
			} );

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
				{ title: __( 'Imagen de inicio', 'bisiesto' ), initialOpen: true },
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
									posterUrl ? __( 'Cambiar imagen', 'bisiesto' ) : __( 'Seleccionar imagen', 'bisiesto' )
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
										__( 'Eliminar imagen', 'bisiesto' )
									)
							);
						},
					} )
				)
			);

			var orientationPanel = el(
				PanelBody,
				{ title: __( 'Orientación', 'bisiesto' ), initialOpen: true },
				el( RadioControl, {
					selected: orientation,
					options: [
						{ label: __( 'Vertical (3:4)', 'bisiesto' ), value: 'vertical' },
						{ label: __( 'Horizontal (4:3)', 'bisiesto' ), value: 'horizontal' },
					],
					onChange: function ( value ) {
						setAttributes( { orientation: value } );
					},
				} )
			);

			var visibilityPanel = el(
				PanelBody,
				{ title: __( 'Visibilidad', 'bisiesto' ), initialOpen: false },
				el( ToggleControl, {
					label: __( 'Ocultar en móvil', 'bisiesto' ),
					checked: !! hideOnMobile,
					onChange: function ( value ) {
						setAttributes( { hideOnMobile: value } );
					},
				} )
			);

			var canvas = el(
				'div',
				blockProps,
				videoUrl &&
					el( 'video', {
						className: 'video-llaollao__video',
						src: videoUrl,
						muted: true,
						loop: true,
						playsInline: true,
						poster: posterUrl || undefined,
					} ),
				posterUrl &&
					el( 'img', { className: 'video-llaollao__poster', src: posterUrl, alt: posterAlt } )
			);

			return el(
				Fragment,
				null,
				el( InspectorControls, null, videoPanel, posterPanel, orientationPanel, visibilityPanel ),
				canvas
			);
		},

		// Bloque dinámico: todo lo pinta render.php a partir de los atributos.
		save: function () {
			return null;
		},
	} );
} )( window.wp );
