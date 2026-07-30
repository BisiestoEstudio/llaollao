import { registerBlockType } from '@wordpress/blocks';
import {
    useBlockProps,
    InnerBlocks,
    InspectorControls,
    MediaUpload,
    MediaUploadCheck,
} from '@wordpress/block-editor';
import { PanelBody, RangeControl, Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import metadata from '../block.json';

registerBlockType( metadata.name, {
    edit( { attributes, setAttributes } ) {
        const { images = [], speed } = attributes;
        const blockProps = useBlockProps( { className: 'llao-landing' } );

        // Mismo reparto por turnos que render.php, para que lo que se ve en el
        // editor case con el front.
        const columns = [ [], [], [] ];
        images.forEach( ( image, i ) => columns[ i % 3 ].push( { image, index: i } ) );

        const addImages = ( media ) => {
            const list = Array.isArray( media ) ? media : [ media ];
            const next = list
                .filter( ( m ) => m && m.id )
                .map( ( m ) => ( { id: m.id, url: m.url, alt: m.alt || '' } ) );
            setAttributes( { images: [ ...images, ...next ] } );
        };

        const removeImage = ( index ) =>
            setAttributes( { images: images.filter( ( _, i ) => i !== index ) } );

        return (
            <>
                <InspectorControls>
                    <PanelBody title={ __( 'Landing', 'llaollao-core' ) } initialOpen>
                        <RangeControl
                            label={ __( 'Duración del recorrido (segundos)', 'llaollao-core' ) }
                            help={ __( 'Cuanto más alto, más despacio se mueven las imágenes.', 'llaollao-core' ) }
                            value={ speed }
                            onChange={ ( v ) => setAttributes( { speed: v === undefined ? 30 : v } ) }
                            min={ 5 }
                            max={ 120 }
                            step={ 1 }
                            __nextHasNoMarginBottom
                        />
                    </PanelBody>
                </InspectorControls>

                <div { ...blockProps }>
                    { /* Zona libre */ }
                    <div className="llao-landing__content is-layout-flow">
                        <InnerBlocks />
                    </div>

                    <div>
                        { images.length ? (
                            <div className="llao-landing__media llao-landing__media--editing">
                                { columns.map( ( column, colIndex ) => (
                                    <div
                                        key={ colIndex }
                                        className={ `llao-landing__col llao-landing__col--${
                                            colIndex === 1 ? 'up' : 'down'
                                        }` }
                                    >
                                        <div className="llao-landing__track">
                                            { column.map( ( { image, index } ) => (
                                                <div key={ index } className="llao-landing__thumb">
                                                    <img
                                                        className="llao-landing__img"
                                                        src={ image.url }
                                                        alt={ image.alt || '' }
                                                    />
                                                    <button
                                                        type="button"
                                                        className="llao-landing__remove"
                                                        onClick={ () => removeImage( index ) }
                                                        aria-label={ __( 'Quitar imagen', 'llaollao-core' ) }
                                                    >
                                                        ×
                                                    </button>
                                                </div>
                                            ) ) }
                                        </div>
                                    </div>
                                ) ) }
                            </div>
                        ) : (
                            <div className="llao-landing__media llao-landing__media--empty">
                                { __( 'Todavía no hay imágenes.', 'llaollao-core' ) }
                            </div>
                        ) }

                        <div className="llao-landing__picker">
                            <MediaUploadCheck>
                                <MediaUpload
                                    multiple
                                    gallery
                                    allowedTypes={ [ 'image' ] }
                                    value={ images.map( ( image ) => image.id ) }
                                    onSelect={ addImages }
                                    render={ ( { open } ) => (
                                        <Button onClick={ open } variant="primary">
                                            { __( 'Añadir imágenes', 'llaollao-core' ) }
                                        </Button>
                                    ) }
                                />
                            </MediaUploadCheck>
                        </div>
                    </div>
                </div>
            </>
        );
    },

    // Dinámico: render.php arma las columnas y duplica las pistas. Solo
    // persistimos los InnerBlocks de la zona libre.
    save() {
        return <InnerBlocks.Content />;
    },
} );
