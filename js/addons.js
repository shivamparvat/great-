(function( $ ) {

	'use strict';

	$(function() {

		var data = {};
		data.mts_plugins_list = 'yes';
		$.ajax({
			url: document.URL,
			cache: false,
			type: "get",
			data: data,
			success: function(response) {

				if( $( response ).find('.mts-addons-list').length > 0 ) {

					$('.mts-addons-list').replaceWith( $( response ).find('.mts-addons-list') );
				}
			}
		});

		// Ajax update, wp-admin\js\updates.js
		$('#plugin-filter').on( 'click', '.update-now-alt', function( event ) {
			var $button = $( event.target );
			event.preventDefault();

			if ( $button.hasClass( 'updating-message' ) || $button.hasClass( 'button-disabled' ) ) {
				return;
			}

			wp.updates.maybeRequestFilesystemCredentials( event );

			mtsUpdatePlugin( {
				plugin: $button.data( 'plugin' ),
				slug:   $button.data( 'slug' ),
			} );
		} );

		var mtsUpdatePlugin = function( args ) {
			var $updateRow, $card, $message, message;

			args = _.extend( {
				success: mtsUpdatePluginSuccess,
				error: mtsUpdatePluginError
			}, args );
			
			$card    = $( '.plugin-card-' + args.slug );
			$message = $card.find( '.update-now-alt' ).addClass( 'updating-message' );
			message  = wp.updates.l10n.pluginUpdatingLabel.replace( '%s', $message.data( 'name' ) );

			// Remove previous error messages, if any.
			$card.removeClass( 'plugin-card-update-failed' ).find( '.notice.notice-error' ).remove();

			if ( $message.html() !== wp.updates.l10n.updating ) {
				$message.data( 'originaltext', $message.html() );
			}

			$message
				.attr( 'aria-label', message )
				.text( wp.updates.l10n.updating );

			$( document ).trigger( 'wp-plugin-updating', args );

			return wp.updates.ajax( 'update-plugin', args );
		};

		var mtsUpdatePluginSuccess = function( response ) {
			var $pluginRow, $updateMessage, newText;

			$pluginRow     = $( 'tr[data-plugin="' + response.plugin + '"]' )
				.removeClass( 'update' )
				.addClass( 'updated' );
			$updateMessage = $( '.plugin-card-' + response.slug ).find( '.update-now-alt' )
				.removeClass( 'updating-message' )
				.addClass( 'button-disabled updated-message' );
				
			$updateMessage
				.attr( 'aria-label', wp.updates.l10n.pluginUpdatedLabel.replace( '%s', response.pluginName ) )
				.text( wp.updates.l10n.pluginUpdated );

			wp.a11y.speak( wp.updates.l10n.updatedMsg, 'polite' );

			//wp.updates.decrementCount( 'plugin' );

			$( document ).trigger( 'wp-plugin-update-success', response );
		};

		var mtsUpdatePluginError = function( response ) {
			var $card, $message, errorMessage;

			if ( ! wp.updates.isValidResponse( response, 'update' ) ) {
				return;
			}

			if ( wp.updates.maybeHandleCredentialError( response, 'update-plugin' ) ) {
				return;
			}

			errorMessage = wp.updates.l10n.updateFailed.replace( '%s', response.errorMessage );

			$card = $( '.plugin-card-' + response.slug )
					.addClass( 'plugin-card-update-failed' )
					.append( wp.updates.adminNotice( {
						className: 'update-message notice-error notice-alt is-dismissible',
						message:   errorMessage
					} ) );

			$card.find( '.update-now-alt' )
				.text( wp.updates.l10n.updateFailedShort ).removeClass( 'updating-message' );

			if ( response.pluginName ) {
				$card.find( '.update-now-alt' )
					.attr( 'aria-label', wp.updates.l10n.updateFailedLabel.replace( '%s', response.pluginName ) );
			} else {
				$card.find( '.update-now-alt' ).removeAttr( 'aria-label' );
			}

			$card.on( 'click', '.notice.is-dismissible .notice-dismiss', function() {

				// Use same delay as the total duration of the notice fadeTo + slideUp animation.
				setTimeout( function() {
					$card
						.removeClass( 'plugin-card-update-failed' )
						.find( '.column-name a' ).focus();

					$card.find( '.update-now-alt' )
						.attr( 'aria-label', false )
						.text( wp.updates.l10n.updateNow );
				}, 200 );
			} );

			wp.a11y.speak( errorMessage, 'assertive' );

			$( document ).trigger( 'wp-plugin-update-error', response );
		};

		// Replace Activate button href with the one from tgmpa after plugin install
		$( document ).on( 'wp-plugin-install-success', function( e, response ) {

			if ( response.activateUrl ) {
				setTimeout( function() {
					$( '.plugin-card-' + response.slug + ' .activate-now' ).each(function() {
						$(this).attr( 'href', $(this).attr('data-activate-url') );
					});
					
				}, 1300 );
			}
		});
	});

})( jQuery );
