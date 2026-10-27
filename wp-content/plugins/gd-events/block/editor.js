( function ( wp ) {
	var el = wp.element.createElement,
		registerBlockType = wp.blocks.registerBlockType,
		InspectorControls = wp.blockEditor.InspectorControls,
		useBlockProps = wp.blockEditor.useBlockProps,
		PanelBody = wp.components.PanelBody,
		SelectControl = wp.components.SelectControl,
		TextControl = wp.components.TextControl,
		ServerSideRender = wp.serverSideRender;

	registerBlockType( 'gd/events', {
		edit: function ( props ) {
			var a = props.attributes, set = props.setAttributes;
			return el( 'div', useBlockProps(),
				el( InspectorControls, {},
					el( PanelBody, { title: 'Events settings' },
						el( SelectControl, { label: 'Show', value: a.scope, options: [ { label: 'Upcoming', value: 'upcoming' }, { label: 'Past', value: 'past' }, { label: 'All', value: 'all' } ], onChange: function ( v ) { set( { scope: v } ); } } ),
						el( TextControl, { label: 'Limit (-1 = all)', type: 'number', value: a.limit, onChange: function ( v ) { set( { limit: parseInt( v, 10 ) || -1 } ); } } ),
						el( TextControl, { label: 'Text when empty', value: a.empty, onChange: function ( v ) { set( { empty: v } ); } } )
					)
				),
				el( ServerSideRender, { block: 'gd/events', attributes: a } )
			);
		},
		save: function () { return null; }
	} );
} )( window.wp );
