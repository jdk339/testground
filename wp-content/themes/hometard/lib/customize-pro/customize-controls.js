( function( api ) {

	// Extends our custom "hometard" section.
	api.sectionConstructor['hometard'] = api.Section.extend( {

		// No events for this type of section.
		attachEvents: function () {},

		// Always make the section active.
		isContextuallyActive: function () {
			return true;
		}
	} );

} )( wp.customize );
