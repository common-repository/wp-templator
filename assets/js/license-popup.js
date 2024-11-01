(function($){

	TemplatorLicense = {

		/**
		 * Init
		 */
		init: function()
		{
			this._check_popup();
			this._bind();
		},

		_check_popup: function()
		{
			var self = TemplatorLicense;
			var open_popup = self._getUrlParameter('templator-license-popup') || '';
			if( open_popup ) {
				self._open_popup();
			}
		},
		
		/**
		 * Binds events
		 */
		_bind: function()
		{
			$( document ).on('click', '.templator-license-popup-open-button',	TemplatorLicense._export_button_click);
			$( document ).on('click', '.templator-close-popup-button',	TemplatorLicense._close_popup);
			$( document ).on('click', '#templator-license-popup-overlay',	TemplatorLicense._close_popup);
			$( document ).on('click', '.templator-activate-license',	TemplatorLicense._activate_license);
			$( document ).on('click', '.templator-deactivate-license',	TemplatorLicense._deactivate_license);
		},

		/**
		 * Debugging.
		 * 
		 * @param  {mixed} data Mixed data.
		 */
		_log: function( data ) {
			
			var date = new Date();
			var time = date.toLocaleTimeString();

			if (typeof data == 'object') { 
				console.log('%c ' + JSON.stringify( data ) + ' ' + time, 'background: #ededed; color: #444');
			} else {
				console.log('%c ' + data + ' ' + time, 'background: #ededed; color: #444');
			}
		},

		_export_button_click: function( e ) {
			e.preventDefault();
			TemplatorLicense._open_popup();
		},

		_open_popup: function() {

			var popup  	    = $('#templator-license-popup-overlay, #templator-license-popup'),
				license_key = $('#templator-license-popup').attr('data-license-key') || '',
				contents    = popup.find( '.contents' );

			// Add validate license window.
			if( license_key ) {
				contents.html( wp.template( 'templator-deactivate-license' ) );
			} else {
				contents.html( wp.template( 'templator-activate-license' ) );
			}

			popup.show();
		},

		_close_popup: function( ) {

			var popup = $('#templator-license-popup-overlay, #templator-license-popup');

			if( popup.hasClass('validating') ) {
				
				// Proceed?
				if( ! confirm( "WARNING! Demo is not exported!\n\nPlease wait for a moment until the export complete." ) ) {
					return;
				}
			}

			popup.hide();
		},

		

		/**
		 * Import
		 */
		_activate_license: function( event )
		{
			event.preventDefault();
			var self = TemplatorLicense;
			var btn        = $(this);
			var parent      = $('#templator-license-popup');
			var contents   = parent.find('.contents');
			var license_btn = $('.templator-license-popup-open-button');
			var license_key = parent.find('.license_key').val() || '';

			parent.addClass('validating');
			btn.find('.text').text('Validating');

			if( contents.find('.notice').length ) {
				contents.find('.notice').remove();
			}

			btn.find('.templator-processing').addClass('is-active');

			$.ajax({
				url  : ajaxurl,
				type : 'POST',
				data : {
					action      : 'templator_activate_license',
					license_key : license_key,
				},
			})
			.done(function( data, status, XHR ) {

				parent.removeClass('validating');

				btn.find('.templator-processing').removeClass('is-active');
				
				if( data.success ) {

					license_btn.removeClass('active').addClass('inactive').text('Deactivate API');
					btn.find('.text').text('Successfully Activated');
					parent.attr('data-license-key', license_key);

					setTimeout(function() {
						TemplatorLicense._close_popup();
					}, 2500);

				} else {

					var msg = data.data.message || '';
					if( msg ) {
						contents.append( '<div class="notice notice-error"><p>' + msg + '</p></div>' );
					} else {
						contents.append( '<div class="notice notice-error"><p>' + data.response + '</p></div>' );
					}

					btn.find('.text').text('Failed!');
				}

				// tb_remove();
			})
			.fail(function( jqXHR, textStatus )
			{
			})
			.always(function()
			{
			});
		},

		/**
		 * Import
		 */
		_deactivate_license: function( event )
		{
			event.preventDefault();

			var self        = $(this);
			var license_btn = $('.templator-license-popup-open-button');
			var parent      = $('#templator-license-popup');
			var license_key = parent.find('.license_key').val() || '';

			parent.addClass('validating');
			self.find('.text').text('Deactivating');

			self.find('.templator-processing').addClass('is-active');

			$.ajax({
				url  : ajaxurl,
				type : 'POST',
				data : {
					action : 'templator_deactivate_license'
				},
			})
			.done(function( data, status, XHR ) {

				parent.removeClass('validating');
			
				self.find('.templator-processing').removeClass('is-active');
				
				if( data.success ) {

					license_btn.removeClass('inactive').addClass('active').text('Activate API');

					self.find('.text').text('Successfully Deactivated');
					parent.attr('data-license-key', '');

					setTimeout(function() {
						TemplatorLicense._close_popup();
					}, 2500);

				} else {

					var msg = data.data.message || '';
					if( msg ) {
						parent.find('.submit-button-td').prepend( '<div class="notice notice-error"><p>' + msg + '</p></div>' );
					} else {
						parent.find('.submit-button-td').prepend( '<div class="notice notice-error"><p>' + data.response + '</p></div>' );
					}

					self.find('.text').text('Failed!');
				}

				// tb_remove();
			})
			.fail(function( jqXHR, textStatus )
			{
			})
			.always(function()
			{
			});
		},

		_getUrlParameter: function( param ) {
		    var page_url = decodeURIComponent( window.location.search.substring(1) ),
		        url_variables = page_url.split('&'),
		        parameter_name,
		        i;

		    for ( i = 0; i < url_variables.length; i++ ) {
		        parameter_name = url_variables[i].split('=');

		        if (parameter_name[0] === param) {
		            return parameter_name[1] === undefined ? true : parameter_name[1];
		        }
		    }
		}

	};

	/**
	 * Initialization
	 */
	$(function(){
		TemplatorLicense.init();
	});

})(jQuery);	