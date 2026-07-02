import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, InnerBlocks } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
import metadata from '../block.json';

const TEMPLATE = [
	[ 'core/group', {
		className: 'site-footer__brand',
		layout: { type: 'default' },
	}, [
		[ 'core/image', {} ],
	] ],
	[ 'core/group', {
		className: 'site-footer__nav',
		layout: { type: 'default' },
	}, [
		[ 'core/columns', { isStackedOnMobile: false }, [
			[ 'core/column', {}, [ [ 'core/navigation', {} ] ] ],
			[ 'core/column', {}, [ [ 'core/navigation', {} ] ] ],
			[ 'core/column', {}, [ [ 'core/navigation', {} ] ] ],
		] ],
		[ 'core/social-links', {} ],
	] ],
];

registerBlockType( metadata.name, {
	edit() {
		const blockProps = useBlockProps( { className: 'site-footer' } );

		return (
			<footer { ...blockProps }>
				<InnerBlocks
					template={ TEMPLATE }
					templateLock="all"
				/>
			</footer>
		);
	},

	save() {
		const blockProps = useBlockProps.save( { className: 'site-footer' } );

		return (
			<footer { ...blockProps }>
				<InnerBlocks.Content />
			</footer>
		);
	},
} );
