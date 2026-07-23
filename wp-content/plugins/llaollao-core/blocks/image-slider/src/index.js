import { registerBlockType, createBlock } from '@wordpress/blocks';
import {
	useBlockProps,
	useInnerBlocksProps,
	InnerBlocks,
	InspectorControls,
	MediaUpload,
	MediaUploadCheck,
} from '@wordpress/block-editor';
import { PanelBody, RangeControl, Button } from '@wordpress/components';
import { useDispatch } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import metadata from '../block.json';

/**
 * Bloque "Slider de imágenes": contenedor de imágenes (llaollao/image-slider-item)
 * en una fila horizontal. El alto de la fila y la separación se controlan aquí y
 * viajan como custom properties; la proporción la pone cada imagen. Dinámico:
 * el front lo arma render.php. En el editor la fila se parte en varias líneas
 * (editor.css) para no recortar las barras de herramientas.
 */
const ITEM = 'llaollao/image-slider-item';

/** Atributos del hijo a partir de un adjunto del selector de medios. */
export function mediaToAttrs( media ) {
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
	edit( { attributes, setAttributes, clientId } ) {
		const { height, heightMobile, gap } = attributes;
		const { insertBlocks } = useDispatch( 'core/block-editor' );

		const blockProps = useBlockProps( {
			className: 'llao-slider',
			style: {
				'--llao-slider-h':        `${ height }px`,
				'--llao-slider-h-mobile': `${ heightMobile }px`,
				'--llao-slider-gap':      `${ gap }px`,
			},
		} );

		// useInnerBlocksProps funde la pista y los inner blocks en un mismo
		// elemento: así cada imagen es hija directa del flex, sin el wrapper
		// intermedio que Gutenberg añadiría, y la fila se ve igual en el editor.
		const innerProps = useInnerBlocksProps(
			{ className: 'llao-slider__track' },
			{
				allowedBlocks:  [ ITEM ],
				template:       [ [ ITEM ] ],
				templateLock:   false,
				orientation:    'horizontal',
				renderAppender: InnerBlocks.ButtonBlockAppender,
			}
		);

		// Alta en lote: una imagen del selector = un bloque hijo, al final.
		const addImages = ( media ) => {
			const list = Array.isArray( media ) ? media : [ media ];
			const blocks = list
				.filter( ( m ) => m && m.id )
				.map( ( m ) => createBlock( ITEM, mediaToAttrs( m ) ) );
			if ( blocks.length ) {
				insertBlocks( blocks, undefined, clientId );
			}
		};

		return (
			<>
				<InspectorControls>
					<PanelBody title={ __( 'Slider', 'llaollao-core' ) } initialOpen>
						<RangeControl
							label={ __( 'Alto de la fila (px)', 'llaollao-core' ) }
							value={ height }
							onChange={ ( v ) => setAttributes( { height: v || 480 } ) }
							min={ 160 }
							max={ 900 }
							step={ 10 }
							__nextHasNoMarginBottom
						/>
						<RangeControl
							label={ __( 'Alto en móvil (px)', 'llaollao-core' ) }
							value={ heightMobile }
							onChange={ ( v ) => setAttributes( { heightMobile: v || 260 } ) }
							min={ 120 }
							max={ 600 }
							step={ 10 }
							__nextHasNoMarginBottom
						/>
						<RangeControl
							label={ __( 'Separación entre imágenes (px)', 'llaollao-core' ) }
							value={ gap }
							onChange={ ( v ) => setAttributes( { gap: v === undefined ? 20 : v } ) }
							min={ 0 }
							max={ 80 }
							step={ 2 }
							__nextHasNoMarginBottom
						/>
					</PanelBody>
					<PanelBody title={ __( 'Añadir imágenes', 'llaollao-core' ) } initialOpen>
						<MediaUploadCheck>
							<MediaUpload
								multiple
								allowedTypes={ [ 'image' ] }
								onSelect={ addImages }
								render={ ( { open } ) => (
									<Button onClick={ open } variant="primary">
										{ __( 'Seleccionar varias imágenes', 'llaollao-core' ) }
									</Button>
								) }
							/>
						</MediaUploadCheck>
					</PanelBody>
				</InspectorControls>

				<div { ...blockProps }>
					<div { ...innerProps } />
				</div>
			</>
		);
	},

	// Dinámico: render.php arma el wrapper y la pista.
	save() {
		return <InnerBlocks.Content />;
	},
} );
