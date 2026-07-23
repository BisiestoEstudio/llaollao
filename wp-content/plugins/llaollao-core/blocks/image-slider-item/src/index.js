import { registerBlockType } from '@wordpress/blocks';
import {
	useBlockProps,
	InspectorControls,
	BlockControls,
	MediaPlaceholder,
	MediaReplaceFlow,
} from '@wordpress/block-editor';
import { PanelBody, SelectControl, TextControl, FocalPointPicker, Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import metadata from '../block.json';

/**
 * Una imagen del slider. La proporción se elige por imagen ("Original" usa la
 * del archivo) y se aplica como aspect-ratio en el <figure>: como el alto lo
 * fija la fila del padre, el ancho de cada imagen sale de su proporción. El
 * punto focal decide qué parte se ve cuando la proporción recorta. Dinámico →
 * render.php sirve la imagen con srcset a partir del ID.
 */
const RATIOS = [
	{ label: __( 'Original del archivo', 'llaollao-core' ), value: '' },
	{ label: __( 'Cuadrada · 1:1', 'llaollao-core' ),        value: '1/1' },
	{ label: __( 'Vertical · 3:4', 'llaollao-core' ),        value: '3/4' },
	{ label: __( 'Vertical · 2:3', 'llaollao-core' ),        value: '2/3' },
	{ label: __( 'Vertical · 9:16', 'llaollao-core' ),       value: '9/16' },
	{ label: __( 'Apaisada · 4:3', 'llaollao-core' ),        value: '4/3' },
	{ label: __( 'Apaisada · 3:2', 'llaollao-core' ),        value: '3/2' },
	{ label: __( 'Apaisada · 16:9', 'llaollao-core' ),       value: '16/9' },
];

/** Atributos a partir de un adjunto del selector de medios. */
function mediaToAttrs( media ) {
	const large = media.sizes && media.sizes.large ? media.sizes.large : null;
	return {
		id:     media.id,
		url:    ( large && large.url ) || media.url || '',
		alt:    media.alt || '',
		width:  media.width || ( large && large.width ) || 0,
		height: media.height || ( large && large.height ) || 0,
	};
}

registerBlockType( metadata.name, {
	edit( { attributes, setAttributes } ) {
		const { id, url, alt, width, height, ratio, focalPoint } = attributes;

		// La elegida en el panel o, si es "Original", la del propio archivo.
		const shown = ratio || ( width && height ? `${ width }/${ height }` : '' );

		const classes = [ 'llao-slider__item' ];
		if ( ! url ) {
			classes.push( 'llao-slider__item--empty' );
		}
		if ( ! shown ) {
			classes.push( 'llao-slider__item--auto' );
		}

		const blockProps = useBlockProps( {
			className: classes.join( ' ' ),
			style: shown ? { aspectRatio: shown } : undefined,
		} );

		const onSelect = ( media ) => {
			if ( ! media || ! media.id ) {
				return;
			}
			setAttributes( mediaToAttrs( media ) );
		};

		if ( ! url ) {
			return (
				<figure { ...blockProps }>
					<MediaPlaceholder
						icon="format-image"
						labels={ { title: __( 'Imagen del slider', 'llaollao-core' ) } }
						onSelect={ onSelect }
						accept="image/*"
						allowedTypes={ [ 'image' ] }
					/>
				</figure>
			);
		}

		return (
			<>
				<BlockControls>
					<MediaReplaceFlow
						mediaId={ id }
						mediaURL={ url }
						accept="image/*"
						allowedTypes={ [ 'image' ] }
						onSelect={ onSelect }
					/>
				</BlockControls>

				<InspectorControls>
					<PanelBody title={ __( 'Imagen', 'llaollao-core' ) } initialOpen>
						<SelectControl
							label={ __( 'Proporción', 'llaollao-core' ) }
							value={ ratio }
							options={ RATIOS }
							onChange={ ( v ) => setAttributes( { ratio: v } ) }
							help={ __( 'El alto lo marca el slider; la proporción decide el ancho de esta imagen.', 'llaollao-core' ) }
							__nextHasNoMarginBottom
						/>
						<TextControl
							label={ __( 'Texto alternativo', 'llaollao-core' ) }
							value={ alt }
							onChange={ ( v ) => setAttributes( { alt: v } ) }
							__nextHasNoMarginBottom
						/>
						<FocalPointPicker
							label={ __( 'Punto focal', 'llaollao-core' ) }
							url={ url }
							value={ focalPoint }
							onChange={ ( v ) => setAttributes( { focalPoint: v } ) }
							__nextHasNoMarginBottom
						/>
						<Button
							onClick={ () =>
								setAttributes( { id: undefined, url: '', alt: '', width: 0, height: 0 } )
							}
							variant="link"
							isDestructive
						>
							{ __( 'Quitar imagen', 'llaollao-core' ) }
						</Button>
					</PanelBody>
				</InspectorControls>

				<figure { ...blockProps }>
					<img
						className="llao-slider__img"
						src={ url }
						alt={ alt }
						style={ {
							objectPosition: `${ Math.round( ( focalPoint?.x ?? 0.5 ) * 100 ) }% ${ Math.round( ( focalPoint?.y ?? 0.5 ) * 100 ) }%`,
						} }
					/>
				</figure>
			</>
		);
	},

	// Dinámico: render.php pinta la imagen (srcset/lazy) desde el ID.
	save() {
		return null;
	},
} );
