(function($){

	TemplatorMedia = {

		init: function()
		{
			this._bind();
		},
		
		/**
		 * Binds events for the Astra Portfolio.
		 *
		 * @since 0.1.0
		 * @access private
		 * @method _bind
		 */
		_bind: function()
		{
			$( document ).on('click', '.templator-set-media', TemplatorMedia._addImage );
			$( document ).on('click', '.templator-remove-media', TemplatorMedia._removeImage );
		},

		/**
		 * Add portfolio image.
		 */
		_addImage: function( event ) {
			event.preventDefault();

			var media_file;
			var selector_image_inner = $( event.target ).parents('.templator-screenshot-inner');
			var selector_image_wrap = $( event.target ).parents('.templator-screenshot');
			var selector_image_id   = selector_image_wrap.find('.image-id' );
			var selector_image_url  = selector_image_wrap.find('.image-url' );

			// Create the media frame.
			media_file = wp.media( {
				multiple: false
			} );

			// When an image is selected, run a callback.
			media_file.on( 'select', function() {

				var attachment = media_file.state().get( 'selection' ).first().toJSON();
				
				if( attachment ) {

					var image_id  = attachment.id || '';
					var image_url = (typeof ( attachment.sizes.medium ) != 'undefined') ? attachment.sizes.medium.url : '';
					if( '' === image_url ) {
						image_url = attachment.url || '';
					}

					if( image_url && image_id ) {
						var template = wp.template('templator-remove-media');
						selector_image_inner.html( template( image_url ) );

						// Set hidden fields.
						selector_image_id.val( image_id );
						selector_image_url.val( image_url );
					}
				}
			});

			// Finally, open the modal
			media_file.open();
		},

		_removeImage: function( event ) {
			event.preventDefault();

			var selector_image_wrap = $( this ).parents('.templator-screenshot');
			var selector_image_id   = selector_image_wrap.find('.image-id' );
			var selector_image_url  = selector_image_wrap.find('.image-url' );
			var selector_image_inner = $( this ).parents('.templator-screenshot-inner');

			var image_id 	= selector_image_id.val() || '',
				image_url 	= selector_image_url.val() || '';

			if( image_url && image_id ) {

				var template = wp.template('templator-set-media');
				selector_image_inner.html( template( image_url ) );

				// Set hidden fields.
				selector_image_id.val( '' );
				selector_image_url.val( '' );
			}
		}

	};

	/**
	 * Initialize TemplatorMedia
	 */
	$(function(){
		TemplatorMedia.init();
	});

})(jQuery);