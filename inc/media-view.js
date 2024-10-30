/**
 * Nav Menu Images Media View.
 *
 * @since 2.0
 *
 * @package Nav Menu Images
 * @subpackage Media View
 */
jQuery(document).ready(function($){
	// Workaround to make "Uploaded to this post" default
	// Based on http://wordpress.stackexchange.com/a/76213
	$(document).ajaxStop(function() {
		$('.media-toolbar select.attachment-filters option[value="uploaded"]').attr( 'selected', true ).parent().trigger('change');
	});

    // Prepare the variable that holds our custom media manager.
	// Based on wp.media.featuredImage
	wp.media.jqem = {
		get: function() {
			return wp.media.view.settings.post.featuredImageId;
		},

		set: function( id ) {
			var settings = wp.media.view.settings;

			settings.post.featuredImageId = id;

			wp.media.post( 'set-post-thumbnail', {
				json:         true,
				post_id:      settings.post.id,
				thumbnail_id: settings.post.featuredImageId,
				_wpnonce:     settings.post.nonce,
				thumb_was:    settings.post.featuredExisted,
				jqem_request:  true
			}).done( function( html ) {
				if ( 1 == settings.post.featuredExisted )
					$( '.jqem-upload-link', "li#menu-item-"+settings.post.id ).show();
				else
					$( '.jqem-upload-link', "li#menu-item-"+settings.post.id ).hide();
				$( '.jqem-current-image', "li#menu-item-"+settings.post.id ).html( html );
			});
		},

		frame: function() {
			/*if ( this._frame )
				return this._frame;*/

			this._frame = wp.media({
				state: 'featured-image',
				states: [ new wp.media.controller.FeaturedImage() ]
			});

			this._frame.on( 'toolbar:create:featured-image', function( toolbar ) {
				this.createSelectToolbar( toolbar, {
					text: wp.media.view.l10n.setFeaturedImage
				});
			}, this._frame );

			this._frame.state('featured-image').on( 'select', this.select );
			return this._frame;
		},

		select: function() {
			var settings = wp.media.view.settings,
				selection = this.get('selection').single();

			if ( ! settings.post.featuredImageId )
				return;

			wp.media.jqem.set( selection ? selection.id : -1 );
		},

		init: function() {
			// Open the content media manager to the 'featured image' tab when
			// the post thumbnail is clicked.
			$('.jqem-div').on( 'click', '.add_media', function( event ) {
				event.preventDefault();
				// Stop propagation to prevent thickbox from activating.
				event.stopPropagation();

				var jqem_clicked_item_id = $(this).data('id');
				wp.media.view.settings = $.extend({}, wp.media.view.settings, jqem_settings[jqem_clicked_item_id]);

				wp.media.jqem.frame().open();
			// Update the featured image id when the 'remove' link is clicked.
			}).on( 'click', '.jqem_remove', function() {
				var jqem_clicked_item_id = $(this).data('id');
				jqem_settings[jqem_clicked_item_id].post.featuredImageId = -1;
			});
		}
	};

	$( wp.media.jqem.init );

	// Based on WPRemoveThumbnail
	jqemRemoveThumbnail = function(nonce,post_id){
		$.post(ajaxurl, {
			action:"set-post-thumbnail", post_id: post_id, thumbnail_id: -1, _ajax_nonce: nonce, cookie: encodeURIComponent(document.cookie), jqem_request: true
		}, function(str){
			if ( str == '0' ) {
				alert( setPostThumbnailL10n.error );
			} else {
				$( '.jqem-upload-link', "li#menu-item-"+post_id ).hide();
				$( '.jqem-current-image', "li#menu-item-"+post_id ).html( str );
			}
		}
		);
	};

});