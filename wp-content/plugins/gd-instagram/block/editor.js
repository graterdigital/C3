( function ( wp ) {
	var el = wp.element.createElement, P = wp.components;
	wp.blocks.registerBlockType( 'gd/instagram', {
		edit: function ( props ) {
			var a = props.attributes, set = props.setAttributes;
			return el( 'div', wp.blockEditor.useBlockProps(),
				el( wp.blockEditor.InspectorControls, {},
					el( P.PanelBody, { title: 'Instagram settings' },
						el( P.RangeControl, { label: 'Posts', min: 1, max: 24, value: a.limit, onChange: function ( v ) { set( { limit: v } ); } } ),
						el( P.RangeControl, { label: 'Columns', min: 2, max: 6, value: a.columns, onChange: function ( v ) { set( { columns: v } ); } } ),
						el( P.ToggleControl, { label: 'Show handle + follow button', checked: a.header, onChange: function ( v ) { set( { header: v } ); } } ),
						el( P.ToggleControl, { label: 'Show captions on hover', checked: a.captions, onChange: function ( v ) { set( { captions: v } ); } } )
					)
				),
				el( wp.serverSideRender, { block: 'gd/instagram', attributes: a } )
			);
		},
		save: function () { return null; }
	} );
} )( window.wp );
