import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, InnerBlocks, RichText } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
import metadata from '../block.json';

/**
 * Año de la línea de tiempo History: un año editable (RichText, texto plano) y
 * un cuerpo con inner blocks libres. Bloque dinámico → render.php genera el
 * marcado; save solo persiste el año (atributo) y los inner blocks.
 */
registerBlockType( metadata.name, {
	edit( { attributes, setAttributes } ) {
		const { year } = attributes;
		const blockProps = useBlockProps( { className: 'history-item' } );

		return (
			<div { ...blockProps }>
				<RichText
					tagName="span"
					className="history-item__year"
					value={ year }
					allowedFormats={ [] }
					onChange={ ( v ) => setAttributes( { year: v } ) }
					placeholder={ __( 'Año', 'bisiesto' ) }
				/>
				<div className="history-item__body">
					<InnerBlocks templateLock={ false } />
				</div>
			</div>
		);
	},

	save() {
		// Dinámico: render.php arma el marcado. Persistimos los inner blocks.
		return <InnerBlocks.Content />;
	},
} );
