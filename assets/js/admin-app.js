( function () {
    'use strict';

    const { useState, useEffect, useCallback, useRef } = wp.element;
    const { render, createElement: el } = wp.element;
    const API  = wpmlAdmin.restUrl;
    const ROOT = document.getElementById( 'wpml-admin-root' );

    if ( ! ROOT ) return;

    /* ------------------------------------------------------------------ */
    /* Debounce hook                                                        */
    /* ------------------------------------------------------------------ */
    function useDebounce( value, delay ) {
        const [ debounced, setDebounced ] = useState( value );
        useEffect( () => {
            const id = setTimeout( () => setDebounced( value ), delay );
            return () => clearTimeout( id );
        }, [ value, delay ] );
        return debounced;
    }

    /* ------------------------------------------------------------------ */
    /* IconCard                                                             */
    /* ------------------------------------------------------------------ */
    function IconCard( { icon, onInsert } ) {
        const [ hovered, setHovered ] = useState( false );
        const [ copied,  setCopied  ] = useState( false );

        function copyShortcode() {
            const code = `[wpml_icon id="${ icon.id }"]`;
            navigator.clipboard.writeText( code ).then( () => {
                setCopied( true );
                setTimeout( () => setCopied( false ), 1500 );
            } );
        }

        const isPremiumLocked = icon.is_premium && ! wpmlAdmin.isPremium;

        return el( 'div', {
                className: 'wpml-card' + ( isPremiumLocked ? ' wpml-card--locked' : '' ),
                onMouseEnter: () => setHovered( true ),
                onMouseLeave: () => setHovered( false ),
            },
            isPremiumLocked && el( 'span', { className: 'wpml-badge-lock', title: 'Premium' }, '🔒' ),

            el( 'div', { className: 'wpml-card__preview' },
                icon.type === 'svg'
                    ? el( 'img', {
                        src: wpmlAdmin.iconsUrl + icon.file,
                        alt: icon.name,
                        className: 'wpml-card__img' + ( hovered ? ' wpml-card__img--animate' : '' ),
                      } )
                    : el( 'span', { className: 'wpml-card__lottie-placeholder' }, '▶ Lottie' )
            ),

            el( 'p', { className: 'wpml-card__name' }, icon.name ),
            el( 'span', { className: 'wpml-card__category' }, icon.category ),

            ! isPremiumLocked && el( 'div', { className: 'wpml-card__actions' },
                el( 'button', { className: 'button button-small', onClick: copyShortcode },
                    copied ? '✓ Copied!' : 'Copy Shortcode'
                ),
                el( 'button', {
                    className: 'button button-primary button-small',
                    onClick: () => onInsert( icon ),
                }, 'Insert' )
            )
        );
    }

    /* ------------------------------------------------------------------ */
    /* App                                                                  */
    /* ------------------------------------------------------------------ */
    function App() {
        const [ icons,      setIcons      ] = useState( [] );
        const [ categories, setCategories ] = useState( [] );
        const [ search,     setSearch     ] = useState( '' );
        const [ category,   setCategory   ] = useState( '' );
        const [ loading,    setLoading    ] = useState( true );

        const debouncedSearch = useDebounce( search, 300 );

        const fetchIcons = useCallback( () => {
            setLoading( true );
            const params = new URLSearchParams();
            if ( debouncedSearch ) params.set( 'search',   debouncedSearch );
            if ( category        ) params.set( 'category', category );

            fetch( `${ API }?${ params }`, {
                headers: { 'X-WP-Nonce': wpmlAdmin.nonce },
            } )
                .then( r => r.json() )
                .then( data => {
                    setIcons( data );
                    // Collect unique categories from full list only (no filter active).
                    if ( ! debouncedSearch && ! category ) {
                        const cats = [ ...new Set( data.map( i => i.category ) ) ].sort();
                        setCategories( cats );
                    }
                } )
                .finally( () => setLoading( false ) );
        }, [ debouncedSearch, category ] );

        useEffect( () => { fetchIcons(); }, [ fetchIcons ] );

        function handleInsert( icon ) {
            // Stub: integrations will hook into wpmlInsertIcon.
            if ( typeof window.wpmlInsertIcon === 'function' ) {
                window.wpmlInsertIcon( icon );
            }
        }

        return el( 'div', { className: 'wpml-dashboard' },

            /* Sidebar */
            el( 'aside', { className: 'wpml-sidebar' },
                el( 'h3', null, 'Categories' ),
                el( 'ul', { className: 'wpml-cat-list' },
                    el( 'li', null,
                        el( 'button', {
                            className: 'wpml-cat-btn' + ( '' === category ? ' is-active' : '' ),
                            onClick: () => setCategory( '' ),
                        }, 'All' )
                    ),
                    categories.map( cat =>
                        el( 'li', { key: cat },
                            el( 'button', {
                                className: 'wpml-cat-btn' + ( category === cat ? ' is-active' : '' ),
                                onClick: () => setCategory( cat ),
                            }, cat )
                        )
                    )
                )
            ),

            /* Main */
            el( 'main', { className: 'wpml-main' },
                el( 'input', {
                    type:        'search',
                    className:   'wpml-search',
                    placeholder: 'Search icons…',
                    value:       search,
                    onChange:    e => setSearch( e.target.value ),
                } ),

                loading
                    ? el( 'p', { className: 'wpml-loading' }, 'Loading…' )
                    : icons.length === 0
                        ? el( 'p', { className: 'wpml-empty' }, 'No icons found.' )
                        : el( 'div', { className: 'wpml-grid' },
                            icons.map( icon =>
                                el( IconCard, { key: icon.id, icon, onInsert: handleInsert } )
                            )
                          )
            )
        );
    }

    render( el( App ), ROOT );
} )();
