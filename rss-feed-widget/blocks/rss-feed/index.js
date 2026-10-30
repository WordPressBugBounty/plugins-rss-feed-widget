( function ( wp ) {
	'use strict';
	var el = wp.element.createElement;
	var __ = wp.i18n.__;
	var useBlockProps = wp.blockEditor.useBlockProps;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var PanelBody = wp.components.PanelBody;
	var TextControl = wp.components.TextControl;
	var RangeControl = wp.components.RangeControl;
	var SelectControl = wp.components.SelectControl;
	var ToggleControl = wp.components.ToggleControl;
	var ServerSideRender = wp.serverSideRender;

	function opts( pairs ) {
		return pairs.map( function ( p ) { return { value: p[ 0 ], label: p[ 1 ] }; } );
	}

	wp.blocks.registerBlockType( 'rss-feed-widget/feed', {
		edit: function ( props ) {
			var a = props.attributes;
			var set = props.setAttributes;
			var blockProps = useBlockProps();

			function toggle( key, label ) {
				return el( ToggleControl, {
					label: label,
					checked: !! a[ key ],
					onChange: function ( v ) { var o = {}; o[ key ] = v; set( o ); }
				} );
			}

			return el(
				'div',
				blockProps,
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: __( 'Feed', 'rss-feed-widget' ), initialOpen: true },
						el( TextControl, {
							label: __( 'Feed URL', 'rss-feed-widget' ),
							type: 'url',
							value: a.rss_url,
							placeholder: 'https://example.com/feed/',
							onChange: function ( v ) { set( { rss_url: v } ); }
						} ),
						el( TextControl, {
							label: __( 'Title (optional)', 'rss-feed-widget' ),
							value: a.title,
							onChange: function ( v ) { set( { title: v } ); }
						} ),
						el( RangeControl, {
							label: __( 'Number of items', 'rss-feed-widget' ),
							min: 1, max: 50, value: a.number,
							onChange: function ( v ) { set( { number: v || 1 } ); }
						} ),
						el( SelectControl, {
							label: __( 'Layout', 'rss-feed-widget' ),
							value: a.list_type,
							options: opts( [ [ 'list', __( 'List', 'rss-feed-widget' ) ], [ 'slider', __( 'Slider', 'rss-feed-widget' ) ] ] ),
							onChange: function ( v ) { set( { list_type: v } ); }
						} ),
						el( SelectControl, {
							label: __( 'Sort order', 'rss-feed-widget' ),
							value: a.content_order,
							options: opts( [
								[ 'default', __( 'Feed order', 'rss-feed-widget' ) ],
								[ 'date_ad', __( 'Newest first', 'rss-feed-widget' ) ],
								[ 'date_aa', __( 'Oldest first', 'rss-feed-widget' ) ],
								[ 'aa', __( 'Alphabetical A-Z', 'rss-feed-widget' ) ],
								[ 'ad', __( 'Alphabetical Z-A', 'rss-feed-widget' ) ],
								[ 'rand', __( 'Random', 'rss-feed-widget' ) ]
							] ),
							onChange: function ( v ) { set( { content_order: v } ); }
						} )
					),
					el(
						PanelBody,
						{ title: __( 'Appearance', 'rss-feed-widget' ), initialOpen: false },
						el( SelectControl, {
							label: __( 'Display', 'rss-feed-widget' ),
							value: a.content_display,
							options: opts( [
								[ 'default', __( 'Default (image + text)', 'rss-feed-widget' ) ],
								[ 'title_only', __( 'Title only', 'rss-feed-widget' ) ],
								[ 'linked_title', __( 'Linked title only', 'rss-feed-widget' ) ],
								[ 'text_only', __( 'Text only', 'rss-feed-widget' ) ],
								[ 'image_only', __( 'Image only', 'rss-feed-widget' ) ]
							] ),
							onChange: function ( v ) { set( { content_display: v } ); }
						} ),
						el( RangeControl, {
							label: __( 'Words to show', 'rss-feed-widget' ),
							min: 5, max: 200, value: a.feed_words,
							onChange: function ( v ) { set( { feed_words: v || 40 } ); }
						} ),
						toggle( 'show_feed_title', __( 'Show item titles', 'rss-feed-widget' ) ),
						toggle( 'keep_feed_link', __( 'Link items to the original', 'rss-feed-widget' ) ),
						toggle( 'new_tab', __( 'Open links in a new tab', 'rss-feed-widget' ) ),
						toggle( 'show_date', __( 'Show date', 'rss-feed-widget' ) ),
						toggle( 'show_source', __( 'Show feed name', 'rss-feed-widget' ) ),
						toggle( 'autoplay', __( 'Slider: autoplay', 'rss-feed-widget' ) )
					)
				),
				a.rss_url
					? el( ServerSideRender, { block: 'rss-feed-widget/feed', attributes: a } )
					: el( 'p', null, __( 'RSS Feed: add a feed URL in the block settings.', 'rss-feed-widget' ) )
			);
		},
		save: function () { return null; }
	} );
}( window.wp ) );
