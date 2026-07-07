import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, useInnerBlocksProps, InnerBlocks } from '@wordpress/block-editor';
import { useSelect } from '@wordpress/data';
import { Fragment } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import metadata from '../block.json';

/**
 * Bloque "History": contenedor de años (bisiesto/history-item). Dinámico: el
 * front lo arma render.php (incluida la navegación de años). En el editor
 * mostramos una nav en vivo (leyendo los años de los hijos con useSelect) y los
 * inner blocks dentro de .history__items (apilados por editor.css).
 */
const TEMPLATE = [ [ 'bisiesto/history-item' ] ];

registerBlockType( metadata.name, {
	edit( { clientId } ) {
		const blockProps = useBlockProps( { className: 'history' } );
		const innerProps = useInnerBlocksProps(
			{ className: 'history__items' },
			{
				allowedBlocks: [ 'bisiesto/history-item' ],
				template: TEMPLATE,
				templateLock: false,
				renderAppender: InnerBlocks.ButtonBlockAppender,
			}
		);

		const years = useSelect(
			( select ) =>
				select( 'core/block-editor' )
					.getBlocks( clientId )
					.filter( ( b ) => b.name === 'bisiesto/history-item' )
					.map( ( b ) => b.attributes.year || '' ),
			[ clientId ]
		);

		return (
			<div { ...blockProps }>
				{ years.length > 0 && (
					<nav className="history__nav" aria-label={ __( 'Años', 'bisiesto' ) }>
						{ years.map( ( year, i ) => {
							const label = year || sprintf( __( 'Año %d', 'bisiesto' ), i + 1 );
							return (
								<Fragment key={ i }>
									{ i > 0 && <span className="history__nav-line" aria-hidden="true" /> }
									<a
										className="history__nav-link button-tag"
										href="#"
										data-text={ label }
										onClick={ ( e ) => e.preventDefault() }
									>
										{ label }
									</a>
								</Fragment>
							);
						} ) }
					</nav>
				) }
				<div { ...innerProps } />
			</div>
		);
	},

	// Dinámico: render.php arma el wrapper, la nav y los ítems.
	save() {
		return <InnerBlocks.Content />;
	},
} );
