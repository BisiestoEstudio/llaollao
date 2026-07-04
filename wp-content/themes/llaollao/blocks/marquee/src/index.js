import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import metadata from '../block.json';

/**
 * Bloque "Marquee". Los textos se editan en el panel lateral (attribute
 * `items`); el lienzo muestra la cinta real con las mismas clases que el front,
 * así que hereda style.css y se ve/anima igual. Bloque dinámico → save null.
 */
registerBlockType( metadata.name, {
	edit( { attributes, setAttributes } ) {
		const items = attributes.items || [];
		const blockProps = useBlockProps( { className: 'marquee' } );

		const update = ( i, val ) => {
			const next = items.slice();
			next[ i ] = val;
			setAttributes( { items: next } );
		};
		const remove = ( i ) => {
			const next = items.slice();
			next.splice( i, 1 );
			setAttributes( { items: next } );
		};
		const add = () => setAttributes( { items: items.concat( [ '' ] ) } );

		const filled = items.filter( Boolean );
		const groupChildren = () =>
			filled.length
				? filled.map( ( t, i ) => (
					<span key={ i } className="marquee__item">{ t }</span>
				) )
				: (
					<span className="marquee__item marquee__item--placeholder">
						{ __( 'Añade textos en el panel lateral', 'bisiesto' ) }
					</span>
				);

		return (
			<>
				<InspectorControls>
					<PanelBody title={ __( 'Textos', 'bisiesto' ) } initialOpen>
						{ items.map( ( text, i ) => (
							<div
								key={ i }
								style={ { display: 'flex', alignItems: 'flex-end', gap: 8, marginBottom: 8 } }
							>
								<div style={ { flex: 1 } }>
									<TextControl
										label={ `${ __( 'Texto', 'bisiesto' ) } ${ i + 1 }` }
										value={ text }
										onChange={ ( v ) => update( i, v ) }
										__nextHasNoMarginBottom
									/>
								</div>
								<Button
									onClick={ () => remove( i ) }
									variant="secondary"
									isDestructive
									icon="trash"
									label={ __( 'Eliminar', 'bisiesto' ) }
								/>
							</div>
						) ) }
						<Button onClick={ add } variant="primary">
							{ __( 'Añadir texto', 'bisiesto' ) }
						</Button>
					</PanelBody>
				</InspectorControls>

				<div { ...blockProps }>
					<div className="marquee__group">{ groupChildren() }</div>
					<div className="marquee__group" aria-hidden="true">{ groupChildren() }</div>
				</div>
			</>
		);
	},

	// Dinámico: render.php genera la cinta.
	save() {
		return null;
	},
} );
