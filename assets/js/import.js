(function($){

	TemplatorImport = {

		doc     : $( document ),
		wrap    : $( '#templator-templates' ),
		inner   : $( '#templator-templates' ).find( '.inner' ),
		post_id : $( '#post_ID' ).val(),

		_api_params		: {},

		/**
		 * Init
		 */
		init: function()
		{
			this._add_import_button();
			this._bind();
		},

		_add_import_button: function()
		{
			if( ! $('body').hasClass( 'post-type-elementor_library' ) ) {
				return;
			}

			setTimeout(function() {
				$( '#wpbody-content' ).find( '.page-title-action:last' ).after( wp.template('templator-add-to-library') );				
			}, 100);
		},
		
		/**
		 * Binds events
		 */
		_bind: function()
		{
			var self = TemplatorImport;

			self.doc.on('keyup input'           , '#templator-search', self._search_template_by_search_term );
			self.doc.on('click'                 , '#templator-list .filter-links a', self._search_template_by_category );
			self.doc.on('click'                 , '#templator-templates .close',	self._close_popup);
			self.doc.on('click'                 , '.templator-import',	self._import_template);

			self.doc.on('trigger-category'      ,	self._category_result);
			self.doc.on('trigger-search-result' ,	self._search_result);
			self.doc.on('trigger-on-load'       ,	self._on_load);

			self.doc.on('click'                 , '.templator-load-templates',	self._laod_templates);
			self.doc.on('click'                 , '.templator-load-templates-library',	self._laod_templates);

			self.doc.on( 'load_categories-done', self._load_categories_done );
			self.doc.on( 'add_template_to_library-done', self._add_template_to_library_done);
			self.doc.on( 'add_template_to_page-done', self._add_template_to_page_done);
			
		},

		_category_result: function( event, data ) {
			event.preventDefault();
			var self = TemplatorImport;

			var template = wp.template('templator-list');
			if( data.success ) {
				$('#templator-list').find( '.filter-count .count' ).html( data.data.templates_count );
				self.inner.find( '.themes' ).html( template( data.data ) );
			} else {
				self.inner.find( '.themes' ).html( wp.template('templator-no-templates') );
			}
		},

		_search_result: function( event, data ) {
			event.preventDefault();

			var self = TemplatorImport;
			var template = wp.template('templator-list');
			if( data.success ) {
				$('#templator-list').find( '.filter-count .count' ).html( data.data.templates_count );
				self.inner.find( '.themes' ).html( template( data.data ) );
			} else {
				self.inner.find( '.themes' ).html( wp.template('templator-no-templates') );
			}
		},

		_on_load: function( event, data )
		{
			event.preventDefault();
			var self = TemplatorImport;

			var list = wp.template('templator-list');
			if( data.success ) {
				$('#templator-list').find( '.filter-count .count' ).html( data.data.templates_count );
				self.inner.find( '.themes' ).html( list( data.data ) );
			} else {
				self.inner.find( '.themes' ).html( wp.template('templator-no-templates') );
			}
		},

		_close_popup: function() {
			$('#templator-templates').fadeOut();
			$('body').removeClass('templator-popup-is-open');
		},

		/**
		 * Import
		 */
		_search_template_by_category: function( event )
		{
			event.preventDefault();

			var self = TemplatorImport;
			
			$( this ).parents('.filter-links').find('a').removeClass('current');
			$( this ).addClass('current');

			self.inner.find( '.themes' ).html( wp.template('templator-searching-templates') );

			self._load_templates( self._get_api_args(), 'trigger-category' );
		},

		_search_template_by_search_term: function( event )
		{
			event.preventDefault();

			var self = TemplatorImport;

			self.inner.find( '.themes' ).html( wp.template('templator-searching-templates') );

			self._load_templates( self._get_api_args(), 'trigger-search-result' );
		},

		_post_auto_save: function() {
			var post_title       = $( '#title' );
			var post_prompt_text = $( '#title-prompt-text' );
			var self = TemplatorImport;

			if ( ! post_title.val() ) {
				post_title.val( 'Templator #' + self.post_id );
				if( post_prompt_text.length ) {
					post_prompt_text.remove();
				}
			}

			if ( wp.autosave ) {
				wp.autosave.server.triggerSave();
			}
		},

		_get_api_args: function() {
			var search_val = $('#templator-search').val() || '';
			var args = {
						'search'   : search_val
					};

			var cat_id = $('#templator-list .filter-links').find('.current').data('id') || '';
			if( cat_id && cat_id !== 'all' ) {
				args['templator-category'] = cat_id;
			}

			// @todo Load only elementor templates.
			// if( $( 'body' ).hasClass( 'post-type-elementor_library' ) ) {
			// 	args['templator-page-builder'] = 4;
			// }

			return args;
		},

		_laod_templates: function()
		{
			if( $( 'body' ).hasClass( 'post-type-page' ) && $( 'body' ).hasClass( 'post-new-php' ) ) {
				TemplatorImport._post_auto_save();
			}

			var self = TemplatorImport;

			self.wrap.fadeIn();

			$('body').addClass('templator-popup-is-open');

			self.inner.html( wp.template('templator-loading-templates') );

			var data = {
				action  : 'templator_load_categories',
				args    : self._get_api_args(),
			};

			// Triggers.
			// self.doc.on( 'load_categories-done' );
			// self.doc.on( 'load_categories-fail' );
			// self.doc.on( 'load_categories-always' );
			self._ajax( data, 'load_categories' );		
		},

		_load_categories_done: function( event, items, status, XHR ) {
			event.preventDefault();
			var self = TemplatorImport;
			if( items.success ) {
				var list = wp.template('templator-list-popup');
				self.inner.html( list( items.data ) );
				self.inner.find( '.themes' ).html( wp.template('templator-searching-templates') );

				self._load_templates( self._get_api_args(), 'trigger-on-load' );
			} else {
				var error = wp.template('templator-error');
				self.inner.html( error( items.data.data.message ) );
			}
		},

		_ajax: function( data, trigger ) {

			var self = TemplatorImport;

			$.ajax({
				url  : ajaxurl,
				type : 'POST',
				data : data,
			})
			.done(function( request, status, XHR )
			{
				self.doc.trigger( trigger + '-done', [request, status, XHR] );
			})
			.fail(function( jqXHR, textStatus )
			{
				self.doc.trigger( trigger + '-fail', [jqXHR, textStatus] );
			})
			.always(function()
			{
				self.doc.trigger( trigger + '-always' );
			});

		},

		_load_templates: function( args, trigger_name ) {
			var self = TemplatorImport;

			if( $('body').hasClass('processing') ) {
				return;
			}

			$('body').addClass('processing');

			$.ajax({
				url  : ajaxurl,
				type : 'POST',
				data : {
					action  : 'templator_load_templates',
					args    : args,
				},
			})
			.done(function( items, status, XHR )
			{
				self.doc.trigger( trigger_name, [items] );
				$('body').removeClass('processing');
			})
			.fail(function( jqXHR, textStatus )
			{
			})
			.always(function()
			{
			});
		},

		_import_template: function()
		{
			var template_id = $(this).data('template-id') || '';
			var self        = TemplatorImport;

			// Process Import Page.
			if( $( 'body' ).hasClass( 'post-type-page' ) )
			{
				self.inner.html( wp.template('templator-importing') );

				var data = {
					action      : 'templator_import',
					post_id     : self.post_id,
					template_id : template_id,
				}

				// Import Template AJAX.
				self._ajax( data, 'add_template_to_page' );
			}

			// Process Import Library.
			if( $( 'body' ).hasClass( 'post-type-elementor_library' ) )
			{
				self.inner.html( wp.template('templator-importing') );

				var data = {
					action        : 'templator_add_to_library',
					post_id       : self.post_id,
					template_id   : template_id,
					template_type : 'elementor',
				};

				// Import Template AJAX.
				self._ajax( data, 'add_template_to_library' );
			}
			
		},

		_add_template_to_page_done: function( event, items, status, XHR ) {
			event.preventDefault();
			var self = TemplatorImport;

			if( items.success ) {
				
				var templator_link = $('.templator-load-templates').attr( 'data-elementor-link' ) || '';
				if( templator_link ) {

					if ( wp.autosave ) {
						wp.autosave.server.triggerSave();
					}

					$( document ).on( 'heartbeat-tick.autosave', function() {
						$( window ).off( 'beforeunload.edit-post' );
						self.inner.html( wp.template('templator-redirect-to-elementor') );
						window.location = templator_link;
					} );

				} else {

					// Imported.
					self.inner.html( wp.template('templator-imported') );

					setTimeout(function() {
						self._close_popup();
					}, 3000);
				}

			} else {
				var error = wp.template('templator-error');
				self.inner.html( error( items.data.message ) );
			}

		},

		_add_template_to_library_done: function( event, items, status, XHR ) {
			event.preventDefault();

			var self = TemplatorImport;

			// Imported.
			self.inner.html( wp.template('templator-imported') );
			location.reload();

			setTimeout(function() {
				self._close_popup();
			}, 3000);
		}
	};

	/**
	 * Initialization
	 */
	$(function(){
		TemplatorImport.init();
	});

})(jQuery);	