(function ( $ ) {

	"use strict";

	$(function () {

		var mts_log_generated = false;

		function init_support_buttons() {
			if ( ! $( '.mts-support-copy' ).length ) {
				return false;
			}

			var clipboard = new Clipboard('.mts-support-copy', {
				target: function(trigger) {
					return $( '#mts-debug-data-field' )[0];
				},
			});

			$( '#mts-debug-data-field' ).click(function(event) {
				$( this ).select();
			});
			return true;
		};

		function mts_load_support_log() {

			$.ajax({
				url: ajaxurl, 
				method: 'post',
				data: {
					'action' : 'mts_get_debug_log'
				},
				success: function(data) {
					$('#mts-debug-data-field').val( data ).prop( 'disabled', false );
					$('.mts-support-copy').prop( 'disabled', false );
				},
				error: function(data) {
					$('#mts-debug-data-field').val( 'Something went wrong.' );
				}
			});

			mts_log_generated = true;
		}

		function mtsGetParameterByName(name, url) {
			if (!url) {
				url = window.location.href;
			}
			name = name.replace(/[\[\]]/g, "\\$&");
			var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
				results = regex.exec(url);
			if (!results) return null;
			if (!results[2]) return '';
			return decodeURIComponent(results[2].replace(/\+/g, " "));
		}

		init_support_buttons();

		if ( 'support' == mtsGetParameterByName('tab') || $('#last_tab').val() == 'support' ) {
			mts_load_support_log();
		}

		$('#support_section_group_li_a').click(function() {
			if ( ! mts_log_generated ) {
				mts_load_support_log();
			}
		});
	});

}(jQuery));
