import { registerBlockType } from '@wordpress/blocks';
import {
	useBlockProps,
	InnerBlocks,
	InspectorControls,
	MediaUpload,
	MediaUploadCheck,
} from '@wordpress/block-editor';
import { PanelBody, Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import metadata from '../block.json';

const ALLOWED_BLOCKS = [ 'core/heading', 'core/paragraph', 'core/buttons' ];
const TEMPLATE = [
	[ 'core/heading', { level: 1, placeholder: __( 'Título del hero…', 'bisiesto' ) } ],
];

registerBlockType( metadata.name, {
	edit( { attributes, setAttributes } ) {
		const { videoUrl, videoId, posterUrl, posterId, posterAlt } = attributes;

		const blockProps = useBlockProps( { className: 'video-hero' } );

		return (
			<>
				<InspectorControls>
					<PanelBody title={ __( 'Vídeo', 'bisiesto' ) } initialOpen={ true }>
						<MediaUploadCheck>
							<MediaUpload
								onSelect={ ( media ) =>
									setAttributes( { videoUrl: media.url, videoId: media.id } )
								}
								allowedTypes={ [ 'video' ] }
								value={ videoId }
								render={ ( { open } ) => (
									<div style={ { display: 'flex', flexDirection: 'column', gap: 8 } }>
										{ videoUrl && (
											<video src={ videoUrl } muted controls style={ { width: '100%', borderRadius: 4 } } />
										) }
										<Button onClick={ open } variant={ videoUrl ? 'secondary' : 'primary' }>
											{ videoUrl
												? __( 'Cambiar vídeo', 'bisiesto' )
												: __( 'Seleccionar vídeo', 'bisiesto' ) }
										</Button>
										{ videoUrl && (
											<Button
												onClick={ () => setAttributes( { videoUrl: '', videoId: undefined } ) }
												variant="link"
												isDestructive
											>
												{ __( 'Eliminar vídeo', 'bisiesto' ) }
											</Button>
										) }
									</div>
								) }
							/>
						</MediaUploadCheck>
					</PanelBody>

					<PanelBody title={ __( 'Portada (opcional)', 'bisiesto' ) } initialOpen={ false }>
						<p style={ { marginTop: 0 } }>
							{ __( 'Si la dejas vacía, se usa el primer frame del vídeo.', 'bisiesto' ) }
						</p>
						<MediaUploadCheck>
							<MediaUpload
								onSelect={ ( media ) =>
									setAttributes( {
										posterUrl: media.url,
										posterId:  media.id,
										posterAlt: media.alt || '',
									} )
								}
								allowedTypes={ [ 'image' ] }
								value={ posterId }
								render={ ( { open } ) => (
									<div style={ { display: 'flex', flexDirection: 'column', gap: 8 } }>
										{ posterUrl && (
											<img src={ posterUrl } alt={ posterAlt } style={ { width: '100%', borderRadius: 4 } } />
										) }
										<Button onClick={ open } variant={ posterUrl ? 'secondary' : 'primary' }>
											{ posterUrl
												? __( 'Cambiar portada', 'bisiesto' )
												: __( 'Seleccionar portada', 'bisiesto' ) }
										</Button>
										{ posterUrl && (
											<Button
												onClick={ () =>
													setAttributes( { posterUrl: '', posterId: undefined, posterAlt: '' } )
												}
												variant="link"
												isDestructive
											>
												{ __( 'Eliminar portada', 'bisiesto' ) }
											</Button>
										) }
									</div>
								) }
							/>
						</MediaUploadCheck>
					</PanelBody>
				</InspectorControls>

				<div { ...blockProps }>
					{ videoUrl && (
						<video
							className="video-hero__video"
							src={ videoUrl }
							muted
							loop
							playsInline
							poster={ posterUrl || undefined }
						/>
					) }
					{ posterUrl && (
						<img className="video-hero__poster" src={ posterUrl } alt={ posterAlt } />
					) }
					<div className="video-hero__content">
						<InnerBlocks allowedBlocks={ ALLOWED_BLOCKS } template={ TEMPLATE } />
					</div>
				</div>
			</>
		);
	},

	// Bloque dinámico: el wrapper (vídeo, portada) lo genera render.php.
	// Solo persistimos los InnerBlocks (el heading superpuesto).
	save() {
		return <InnerBlocks.Content />;
	},
} );
