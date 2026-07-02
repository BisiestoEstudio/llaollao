import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, RangeControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import { __ } from '@wordpress/i18n';
import metadata from '../block.json';

registerBlockType( metadata.name, {
	edit( { attributes, setAttributes } ) {
		const blockProps = useBlockProps();
		const { count } = attributes;

		return (
			<div { ...blockProps }>
				<InspectorControls>
					<PanelBody title={ __( 'Related posts', 'baygo' ) }>
						<RangeControl
							label={ __( 'Number of posts', 'baygo' ) }
							value={ count }
							min={ 1 }
							max={ 6 }
							onChange={ ( value ) =>
								setAttributes( { count: value } )
							}
						/>
					</PanelBody>
				</InspectorControls>

				<ServerSideRender
					block={ metadata.name }
					attributes={ attributes }
				/>
			</div>
		);
	},
	save() {
		return null;
	},
} );
