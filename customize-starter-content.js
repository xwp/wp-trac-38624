/* global wp, jQuery */
( function( api, $ ) {

	api.bind( 'ready', function () {
		api.panel( 'themes', function( panel ) {
			panel.deferred.embedded.done( function() {
				var starterContentRow;
				panel.headContainer.addClass( 'has-starter-content' );
				starterContentRow = $( wp.template( 'customize-starter-content-actions' )() );
				panel.headContainer.append( starterContentRow );

				starterContentRow.on( 'click', 'button', function( event ) {
					var request, button = $( this );
					event.preventDefault();
					$( '.wp-full-overlay' ).addClass( 'customize-loading' ); // @todo This isn't working anymore.
					request = wp.ajax.post( 'customize_load_starter_content', api.previewer.query() );
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
