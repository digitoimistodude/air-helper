/*
* @Author: Timi Wahalahti
* @Date:   2018-12-04 13:35:35
* @Last Modified by:   Timi Wahalahti
* @Last Modified time: 2018-12-04 15:29:22
*/

jQuery(document).ready( function($) {
	jQuery( '#dashboard-widgets #air-helper-help .support-form form' ).submit( function(e) {
		e.preventDefault();

		var send = true;

		// reset field styles
		$(this).find( 'input[name="subject"]' ).removeClass( 'error' );
		$(this).find( 'textarea' ).removeClass( 'error' );

		// content
		var subject = $(this).find( 'input[name="subject"]' ).val()
		var content = $(this).find( 'textarea' ).val();

		// check if subject is empty
		if ( ! $.trim( subject ) ) {
			$(this).find( 'input[name="subject"]' ).addClass( 'error' );
			send = false;
		}

		// check if content is empty
		if ( ! $.trim( content) ) {
			$(this).find( 'textarea' ).addClass( 'error' );
			send = false;
		}

		// fields are empty, show error message
		if ( ! send ) {
			$('#dashboard-widgets #air-helper-help .support-form p.message-field-error').show();
		} else {
			// send

			// reset messages
			$('#dashboard-widgets #air-helper-help .support-form p.message-field-error').hide();
			$(this).find( 'input[name="subject"]' ).removeClass( 'error' );
			$(this).find( 'textarea' ).removeClass( 'error' );

			// data to send
	 	  var data = {
				'action': 'air_helper_send_ticket',
				'subject': subject,
				'content': content,
				'ticket_nonce': $(this).find( 'input[name="nonce"]' ).val(),
			};

			// send the data
			// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
			jQuery.post(ajaxurl, data, function(response) {
				if ( response.success ) {
					$( '#dashboard-widgets #air-helper-help .support-form form input[name="subject"]' ).val( '' );
					$( '#dashboard-widgets #air-helper-help .support-form form textarea' ).val( '' );
					$('#dashboard-widgets #air-helper-help .support-form form' ).hide();
					$('#dashboard-widgets #air-helper-help .support-form p.message-error').hide();
					$('#dashboard-widgets #air-helper-help .support-form p.message-success').show();
				} else {
					$('#dashboard-widgets #air-helper-help .support-form p.message-error').show();
				}
			} ).fail( function() {
    		$('#dashboard-widgets #air-helper-help .support-form p.message-error').show();
  		} );
		}
	} );
} );
