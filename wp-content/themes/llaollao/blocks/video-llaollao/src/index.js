import { registerBlockType } from '@wordpress/blocks';
import {
	useBlockProps,
	InspectorControls,
	MediaUpload,
	MediaUploadCheck,
} from '@wordpress/block-editor';
import { PanelBody, Button, RadioControl, ToggleControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import metadata from '../block.json';

registerBlockType( metadata.name, {
	edit( { attributes, setAttributes } ) {
		const { videoUrl, videoId, posterUrl, posterId, posterAlt, orientation, hideOnMobile } = attributes;

		const blockProps = useBlockProps( {
			className: `video-llaollao is-${ orientation === 'horizontal' ? 'horizontal' : 'vertical' }${ hideOnMobile ? ' is-hidden-mobile' : '' }`,
		} );

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
											{ videoUrl ? __( 'Cambiar vídeo', 'bisiesto' ) : __( 'Seleccionar vídeo', 'bisiesto' ) }
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

					<PanelBody title={ __( 'Imagen de inicio', 'bisiesto' ) } initialOpen={ true }>
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
											{ posterUrl ? __( 'Cambiar imagen', 'bisiesto' ) : __( 'Seleccionar imagen', 'bisiesto' ) }
										</Button>
										{ posterUrl && (
											<Button
												onClick={ () =>
													setAttributes( { posterUrl: '', posterId: undefined, posterAlt: '' } )
												}
												variant="link"
												isDestructive
											>
												{ __( 'Eliminar imagen', 'bisiesto' ) }
											</Button>
										) }
									</div>
								) }
							/>
						</MediaUploadCheck>
					</PanelBody>

					<PanelBody title={ __( 'Orientación', 'bisiesto' ) } initialOpen={ true }>
						<RadioControl
							selected={ orientation || 'vertical' }
							options={ [
								{ label: __( 'Vertical (3:4)', 'bisiesto' ), value: 'vertical' },
								{ label: __( 'Horizontal (4:3)', 'bisiesto' ), value: 'horizontal' },
							] }
							onChange={ ( value ) => setAttributes( { orientation: value } ) }
						/>
					</PanelBody>

					<PanelBody title={ __( 'Visibilidad', 'bisiesto' ) } initialOpen={ false }>
						<ToggleControl
							label={ __( 'Ocultar en móvil', 'bisiesto' ) }
							checked={ !! hideOnMobile }
							onChange={ ( value ) => setAttributes( { hideOnMobile: value } ) }
						/>
					</PanelBody>
				</InspectorControls>

				<div { ...blockProps }>
					{ videoUrl && (
						<video
							className="video-llaollao__video"
							src={ videoUrl }
							muted
							loop
							playsInline
							poster={ posterUrl || undefined }
						/>
					) }
					{ posterUrl && (
						<img className="video-llaollao__poster" src={ posterUrl } alt={ posterAlt } />
					) }
				</div>
			</>
		);
	},

	// Bloque dinámico: todo lo pinta render.php a partir de los atributos.
	save() {
		return null;
	},
} );
