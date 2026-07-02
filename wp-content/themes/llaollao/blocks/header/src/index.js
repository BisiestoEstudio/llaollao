import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, InnerBlocks, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, ToggleControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import metadata from '../block.json';

registerBlockType( metadata.name, {
	edit( { attributes, setAttributes } ) {
		const { showLanguageSwitcher } = attributes;
		const blockProps = useBlockProps( { className: 'site-header' } );

		return (
			<>
				<InspectorControls>
					<PanelBody title={ __( 'Opciones', 'bisiesto' ) } initialOpen={ true }>
						<ToggleControl
							label={ __( 'Mostrar selector de idiomas', 'bisiesto' ) }
							checked={ showLanguageSwitcher }
							onChange={ ( val ) => setAttributes( { showLanguageSwitcher: val } ) }
						/>
					</PanelBody>
				</InspectorControls>

				<header { ...blockProps }>
					<div className="site-header__bar">
						<div className="site-header__logo">
							<span style={ { opacity: 0.35, fontSize: 11, fontWeight: 600 } }>
								{ __( 'LOGO', 'bisiesto' ) }
							</span>
						</div>

						<nav className="site-header__nav">
							<InnerBlocks
								allowedBlocks={ [ 'core/navigation', 'core/group', 'bisiesto/megamenu' ] }
								placeholder={
									<p style={ { opacity: 0.4, fontSize: 12, margin: 0 } }>
										{ __( 'Añade aquí el bloque de navegación', 'bisiesto' ) }
									</p>
								}
							/>
						</nav>

						{ showLanguageSwitcher && (
							<div className="site-header__lang">
								<span style={ { opacity: 0.35, fontSize: 11, fontWeight: 600 } }>
									{ __( 'IDIOMAS', 'bisiesto' ) }
								</span>
							</div>
						) }
					</div>
				</header>
			</>
		);
	},

	save() {
		return <InnerBlocks.Content />;
	},
} );
