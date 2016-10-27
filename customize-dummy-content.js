/* global wp, jQuery */
( function( api, $ ) {

	api.bind( 'ready', function () {
		if ( api.settings.theme.active ) {
			return;
		}

		api.panel( 'themes', function( panel ) {
			panel.deferred.embedded.done( function() {
				var dummyContentRow;
				panel.headContainer.addClass( 'has-dummy-content' );
				dummyContentRow = $( wp.template( 'customize-dummy-content-actions' )() );
				panel.headContainer.append( dummyContentRow );

				dummyContentRow.on( 'click', 'button', function( event ) {
					var request, button = $( this );
					event.preventDefault();
					$( '.wp-full-overlay' ).addClass( 'customize-loading' ); // @todo This isn't working anymore.
					request = wp.ajax.post( 'customize_load_dummy_content', api.previewer.query() );
					button.prop( 'disabled', true );
					request.done( function() {
						panel.loadThemePreview( api.settings.theme.stylesheet ).fail( function() {
							button.prop( 'disabled', false );
						} );
					} );
					request.fail( function() {
						button.prop( 'disabled', false );
					} );
				} );
			} );
		} );
	} );

} )( wp.customize, jQuery );
