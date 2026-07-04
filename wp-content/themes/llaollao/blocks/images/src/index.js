import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, InnerBlocks } from '@wordpress/block-editor';
import metadata from '../block.json';

/**
 * Estructura fija (plantilla bloqueada):
 * - Fila 1: 3 columnas 24% / 52% / 24%. Laterales con Video Llaollao; centro libre.
 * - Fila 2: 2 columnas, cada una con Video Llaollao.
 * templateLock 'all' fija la maquetación; la columna central se desbloquea
 * (templateLock false) para poder añadir contenido libre.
 */
const TEMPLATE = [
	[ 'core/columns', { className: 'images__row images__row--top' }, [
		[ 'core/column', { width: '24%' }, [ [ 'bisiesto/video-llaollao' ] ] ],
		[ 'core/column', { width: '52%', templateLock: false }, [] ],
		[ 'core/column', { width: '24%' }, [ [ 'bisiesto/video-llaollao' ] ] ],
	] ],
	[ 'core/columns', { className: 'images__row images__row--bottom' }, [
		[ 'core/column', {}, [ [ 'bisiesto/video-llaollao' ] ] ],
		[ 'core/column', {}, [ [ 'bisiesto/video-llaollao' ] ] ],
	] ],
];

registerBlockType( metadata.name, {
	edit() {
		const blockProps = useBlockProps( { className: 'images' } );
		return (
			<div { ...blockProps }>
				<InnerBlocks template={ TEMPLATE } templateLock="all" />
			</div>
		);
	},

	save() {
		const blockProps = useBlockProps.save( { className: 'images' } );
		return (
			<div { ...blockProps }>
				<InnerBlocks.Content />
			</div>
		);
	},
} );
