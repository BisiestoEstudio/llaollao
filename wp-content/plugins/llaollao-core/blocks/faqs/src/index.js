import { registerBlockType } from '@wordpress/blocks';
import {
    useBlockProps,
    InnerBlocks,
    RichText,
} from '@wordpress/block-editor';
import { Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import metadata from '../block.json';

const EMPTY_ITEM = { question: '', answer: '' };

function ChevronIcon() {
    return (
        <svg
            className="faq__chevron"
            viewBox="0 0 20 20"
            strokeWidth="1.5"
            strokeLinecap="round"
            strokeLinejoin="round"
            aria-hidden="true"
        >
            <path d="M5 7.5L10 12.5L15 7.5" />
        </svg>
    );
}

registerBlockType( metadata.name, {
    edit( { attributes, setAttributes } ) {
        const { items } = attributes;
        const blockProps = useBlockProps( { className: 'faqs' } );

        const updateItem = ( index, patch ) =>
            setAttributes( {
                items: items.map( ( item, i ) => ( i === index ? { ...item, ...patch } : item ) ),
            } );

        const addItem    = () => setAttributes( { items: [ ...items, { ...EMPTY_ITEM } ] } );
        const removeItem = ( index ) =>
            setAttributes( { items: items.filter( ( _, i ) => i !== index ) } );

        return (
            <div { ...blockProps }>
                { /* Zona superior libre */ }
                <div className="faqs__header is-layout-flow">
                    <InnerBlocks />
                </div>

                { /* Lista de preguntas */ }
                <div className="faqs__list">
                    { items.map( ( item, index ) => (
                        <div key={ index } className="faq faq--editing">
                            <div className="faq__summary">
                                <RichText
                                    tagName="span"
                                    className="faq__question"
                                    value={ item.question }
                                    onChange={ ( question ) => updateItem( index, { question } ) }
                                    placeholder={ __( 'Pregunta…', 'llaollao-core' ) }
                                    allowedFormats={ [ 'core/bold', 'core/italic' ] }
                                />
                                <ChevronIcon />
                            </div>
                            <RichText
                                tagName="p"
                                className="faq__answer"
                                value={ item.answer }
                                onChange={ ( answer ) => updateItem( index, { answer } ) }
                                placeholder={ __( 'Respuesta…', 'llaollao-core' ) }
                            />
                            <Button
                                onClick={ () => removeItem( index ) }
                                variant="link"
                                isDestructive
                                style={ { fontSize: 11, marginBottom: '0.5rem' } }
                            >
                                { __( 'Eliminar', 'llaollao-core' ) }
                            </Button>
                        </div>
                    ) ) }

                    <Button
                        onClick={ addItem }
                        variant="primary"
                        style={ { marginTop: '0.5rem', alignSelf: 'flex-start' } }
                    >
                        { __( '+ Añadir FAQ', 'llaollao-core' ) }
                    </Button>
                </div>
            </div>
        );
    },

    // Bloque dinámico: el render lo hace render.php. Solo persistimos
    // los InnerBlocks de la cabecera (las FAQs salen de los atributos
    // en el servidor). Así evitamos el error de validación /
    // recuperación al traducir con Polylang.
    save() {
        return <InnerBlocks.Content />;
    },
} );
