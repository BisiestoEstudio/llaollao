import { registerBlockType } from '@wordpress/blocks';
import {
    useBlockProps,
    InnerBlocks,
    RichText,
    InspectorControls,
    MediaUpload,
    MediaUploadCheck,
} from '@wordpress/block-editor';
import { PanelBody, RangeControl, TextControl, Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import metadata from '../block.json';

const EMPTY_ITEM = { imageId: 0, imageUrl: '', imageAlt: '', text: '', url: '' };

registerBlockType( metadata.name, {
    edit( { attributes, setAttributes } ) {
        const { items, gap } = attributes;
        const blockProps = useBlockProps( { className: 'llao-grid-links' } );

        const updateItem = ( index, patch ) =>
            setAttributes( {
                items: items.map( ( item, i ) => ( i === index ? { ...item, ...patch } : item ) ),
            } );

        const addItem    = () => setAttributes( { items: [ ...items, { ...EMPTY_ITEM } ] } );
        const removeItem = ( index ) =>
            setAttributes( { items: items.filter( ( _, i ) => i !== index ) } );

        return (
            <>
                <InspectorControls>
                    <PanelBody title={ __( 'Grid links', 'llaollao-core' ) } initialOpen>
                        <RangeControl
                            label={ __( 'Separación entre elementos (px)', 'llaollao-core' ) }
                            value={ gap }
                            onChange={ ( v ) => setAttributes( { gap: v === undefined ? 20 : v } ) }
                            min={ 0 }
                            max={ 80 }
                            step={ 2 }
                            __nextHasNoMarginBottom
                        />
                    </PanelBody>
                </InspectorControls>

                <div { ...blockProps }>
                    { /* Zona superior libre */ }
                    <div className="llao-grid-links__header is-layout-flow">
                        <InnerBlocks />
                    </div>

                    { /* Rejilla de enlaces */ }
                    <div className="llao-grid-links__grid">
                        { items.map( ( item, index ) => (
                            <div key={ index } className="llao-grid-links__cell">
                                <div
                                    className={ `llao-grid-links__item${
                                        item.imageUrl ? '' : ' llao-grid-links__item--empty'
                                    }` }
                                >
                                    { item.imageUrl && (
                                        <img
                                            className="llao-grid-links__img"
                                            src={ item.imageUrl }
                                            alt={ item.imageAlt || '' }
                                        />
                                    ) }
                                    <RichText
                                        tagName="span"
                                        className="llao-grid-links__text"
                                        value={ item.text }
                                        onChange={ ( text ) => updateItem( index, { text } ) }
                                        placeholder={ __( 'Título…', 'llaollao-core' ) }
                                        allowedFormats={ [ 'core/bold', 'core/italic' ] }
                                    />
                                </div>

                                <div className="llao-grid-links__edit">
                                    <TextControl
                                        label={ __( 'Enlace (URL)', 'llaollao-core' ) }
                                        type="url"
                                        value={ item.url || '' }
                                        onChange={ ( url ) => updateItem( index, { url } ) }
                                        placeholder="https://"
                                        __nextHasNoMarginBottom
                                    />
                                    <div className="llao-grid-links__edit-row">
                                        <MediaUploadCheck>
                                            <MediaUpload
                                                allowedTypes={ [ 'image' ] }
                                                value={ item.imageId }
                                                onSelect={ ( media ) =>
                                                    updateItem( index, {
                                                        imageId:  media.id,
                                                        imageUrl: media.url,
                                                        imageAlt: media.alt || '',
                                                    } )
                                                }
                                                render={ ( { open } ) => (
                                                    <Button onClick={ open } variant="secondary" size="small">
                                                        { item.imageUrl
                                                            ? __( 'Cambiar imagen', 'llaollao-core' )
                                                            : __( 'Seleccionar imagen', 'llaollao-core' ) }
                                                    </Button>
                                                ) }
                                            />
                                        </MediaUploadCheck>
                                        <Button
                                            onClick={ () => removeItem( index ) }
                                            variant="link"
                                            isDestructive
                                            style={ { fontSize: 11 } }
                                        >
                                            { __( 'Eliminar', 'llaollao-core' ) }
                                        </Button>
                                    </div>
                                </div>
                            </div>
                        ) ) }
                    </div>

                    <Button onClick={ addItem } variant="primary" style={ { marginTop: '1rem' } }>
                        { __( '+ Añadir enlace', 'llaollao-core' ) }
                    </Button>
                </div>
            </>
        );
    },

    // Dinámico: render.php arma la rejilla a partir de los atributos. Solo
    // persistimos los InnerBlocks de la zona libre.
    save() {
        return <InnerBlocks.Content />;
    },
} );
