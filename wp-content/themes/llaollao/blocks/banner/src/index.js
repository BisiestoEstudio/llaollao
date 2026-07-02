import { registerBlockType } from '@wordpress/blocks';
import {
	useBlockProps,
	InnerBlocks,
	InspectorControls,
	MediaUpload,
	MediaUploadCheck,
} from '@wordpress/block-editor';
import { PanelBody, Button, FocalPointPicker } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import metadata from '../block.json';

registerBlockType( metadata.name, {
	edit( { attributes, setAttributes } ) {
		const { imageUrl, imageId, imageAlt, focalPoint } = attributes;

		const bgStyle = imageUrl
			? {
				backgroundImage:    `url(${ imageUrl })`,
				backgroundPosition: `${ Math.round( focalPoint.x * 100 ) }% ${ Math.round( focalPoint.y * 100 ) }%`,
			}
			: {};

		const blockProps = useBlockProps( {
			className: 'banner',
			style: bgStyle,
		} );

		return (
			<>
				<InspectorControls>
					<PanelBody title={ __( 'Imagen de fondo', 'bisiesto' ) }>
						<MediaUploadCheck>
							<MediaUpload
								onSelect={ ( media ) =>
									setAttributes( {
										imageUrl: media.url,
										imageId:  media.id,
										imageAlt: media.alt || '',
									} )
								}
								allowedTypes={ [ 'image' ] }
								value={ imageId }
								render={ ( { open } ) => (
									<div style={ { display: 'flex', flexDirection: 'column', gap: 8 } }>
										{ imageUrl && (
											<img src={ imageUrl } alt={ imageAlt } style={ { width: '100%', borderRadius: 4 } } />
										) }
										<Button onClick={ open } variant={ imageUrl ? 'secondary' : 'primary' }>
											{ imageUrl
												? __( 'Cambiar imagen', 'bisiesto' )
												: __( 'Seleccionar imagen', 'bisiesto' ) }
										</Button>
										{ imageUrl && (
											<Button
												onClick={ () =>
													setAttributes( { imageUrl: '', imageId: undefined, imageAlt: '' } )
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
						{ imageUrl && (
							<FocalPointPicker
								url={ imageUrl }
								value={ focalPoint }
								onChange={ ( val ) => setAttributes( { focalPoint: val } ) }
								style={ { marginTop: 16 } }
							/>
						) }
					</PanelBody>
				</InspectorControls>

				<div { ...blockProps }>
					<InnerBlocks />
				</div>
			</>
		);
	},

	// Bloque dinámico: el wrapper (fondo, focal point, aria) lo genera
	// render.php. Solo persistimos los InnerBlocks. Evita el error de
	// validación al traducir con Polylang.
	save() {
		return <InnerBlocks.Content />;
	},
} );
