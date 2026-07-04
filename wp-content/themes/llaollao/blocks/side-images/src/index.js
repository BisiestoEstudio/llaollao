import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, useInnerBlocksProps, InnerBlocks } from '@wordpress/block-editor';
import metadata from '../block.json';

/**
 * Bloque "Side images": contenedor de dos columnas independientes
 * (bisiesto/side-image-column). La maquetación (flex, gap 20px, hover 70/30
 * y el colapso de botones) vive en style.css. La plantilla está bloqueada a
 * dos columnas; el contenido de cada una se edita dentro del bloque hijo.
 */
const TEMPLATE = [
	[ 'bisiesto/side-image-column', {} ],
	[ 'bisiesto/side-image-column', {} ],
];

registerBlockType( metadata.name, {
	edit() {
		const blockProps = useBlockProps( { className: 'side-images' } );
		// useInnerBlocksProps funde wrapper e inner blocks en un mismo elemento:
		// así las columnas son hijas directas del flex (sin el wrapper intermedio
		// que Gutenberg añadiría) y se ven en dos columnas también en el editor.
		const innerProps = useInnerBlocksProps( blockProps, {
			template: TEMPLATE,
			templateLock: 'all',
		} );
		return <div { ...innerProps } />;
	},

	save() {
		const blockProps = useBlockProps.save( { className: 'side-images' } );
		return (
			<div { ...blockProps }>
				<InnerBlocks.Content />
			</div>
		);
	},
} );
