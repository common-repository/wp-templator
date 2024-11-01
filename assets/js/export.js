(function($){

	TemplatorExport = {

		/**
		 * Init
		 */
		init: function()
		{
			this._bind();
		},
		
		/**
		 * Binds events
		 */
		_bind: function()
		{
			$( document ).on('click', '.templator-open-popup-button',	TemplatorExport._open_popup);
			$( document ).on('click', '.templator-close-popup-button',	TemplatorExport._close_popup);
			$( document ).on('click', '#templator-export-popup-overlay',	TemplatorExport._close_popup);
			$( document ).on('click', '.templator-export',	TemplatorExport._export);
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

		_open_popup: function( e )
		{
			e.preventDefault();

			var popup      	   = $('#templator-export-popup-overlay, #templator-export-popup'),
				contents       = popup.find('.contents'),
				self           = $(this),
				data 		   = {
					post_id        : self.attr( 'data-post-id' ) || '',
					screenshot_id  : self.attr( 'data-screenshot-id' ) || '',
					screenshot_url : self.attr( 'data-screenshot-url' ) || '',
					license_key    : self.attr( 'data-license-key' ) || '',
					export_status  : self.attr( 'data-export-status' ) || ''
				};

			popup.show();

			contents.html('');

			if( data.license_key ) {
				if( 'success' === data.export_status ) {
					contents.append( wp.template('templator-export-success') );
				} else if ( 'in-process' === data.export_status ) {
					contents.append( wp.template('templator-export-in-process') );
				}

				var template = wp.template('templator-table-export');
				contents.append( template( data ) );
			} else {
				contents.html( wp.template('templator-license-not-active') );				
			}

			if( contents.find('.templator-table-export .description').length ) {
				contents.find('.templator-table-export .description').remove();
			}

			$('#templator-export-popup .templator-export').find('.text').text('Export');
			$('#templator-export-popup .templator-export').attr( 'data-post-id', data.post_id );
			$('#templator-export-popup .templator-export').attr( 'data-screenshot-id', data.screenshot_id );
			$('#templator-export-popup .templator-export').attr( 'data-screenshot-url', data.screenshot_url );
			$('#templator-export-popup .templator-export').attr( 'data-license-key', data.license_key );
			$('#templator-export-popup .templator-export').attr( 'data-export-status', data.export_status );

		},

		_close_popup: function( ) {

			var popup = $('#templator-export-popup-overlay, #templator-export-popup');

			if( popup.hasClass('exporting') ) {
				
				// Proceed?
				if( ! confirm( "WARNING! Template export is in process.\n\nPlease wait until the export process complete." ) ) {
					return;
				}
			}

			popup.hide();
		},

		

		/**
		 * Import
		 */
		_export: function( event )
		{
			event.preventDefault();

			var self       = $(this);
			var parent     = $('#templator-export-popup');
			var contents   = parent.find('.contents');
			var post_id    = self.data('post-id') || '';
			var args = {
				post_id        : self.attr( 'data-post-id' ) || '',
				screenshot_id  : parent.find('.image-id').val() || '',
				screenshot_url : parent.find('.image-url').val() || '',
				license_key    : self.attr( 'data-license-key' ) || '',
				export_status  : self.attr( 'data-export-status' ) || '',
				categories     : parent.find('.categories').val() || ''
			};

			parent.addClass('exporting');
			self.find('.text').text('Exporting');

			if( contents.find('.notice').length ) {
				contents.find('.notice').remove();
			}

			self.find('.templator-processing').addClass('is-active');

			$.ajax({
				url  : ajaxurl,
				type : 'POST',
				data : {
					action : 'templator_export',
					args   : args,
				},
			})
			.done(function( data, status, XHR ) {
				
				$( '.templator-open-popup-button[data-post-id="'+args.post_id+'"]' ).attr( 'data-export-status', 'in-process' );

				parent.removeClass('exporting');
				self.find('.templator-processing').removeClass('is-active');

				if( data.success ) {

					if( data.data.success ) {
						self.find('.text').text('Export In Progress..');
						contents.append('<div class="description"><div class="notice notice-info"><p>We have started exporting the template in the background. It will take just a moment to complete the process. You can close this popup and resume your work.</p></div></div>' );
					} else {
						contents.append( '<div class="notice notice-error"><p>'+data.data.message+'</p></div>' );
						self.find('.text').text('Export failed!');
					}

				} else {
					var msg = data.data.message || data.message || '';
					if( msg ) {
						contents.append( '<div class="notice notice-error"><p>'+msg+'</p></div>' );
						self.find('.text').text('Export failed!');
					}
				}

			})
			.fail(function( jqXHR, textStatus )
			{
				$(document).trigger( 'templator-api-request-fail', [args.post_id, jqXHR, textStatus] );
			})
			.always(function()
			{
				$(document).trigger( 'templator-api-request-always', [args.post_id] );
			});
		}

	};

	/**
	 * Initialization
	 */
	$(function(){
		TemplatorExport.init();
	});

})(jQuery);	