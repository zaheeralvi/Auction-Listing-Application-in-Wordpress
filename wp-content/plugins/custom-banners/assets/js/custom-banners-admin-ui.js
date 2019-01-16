var gp_initialize_custom_banners_admin = function () {
	var stamp = jQuery('#cbp-expiration-timestamp').html();
	jQuery('.edit-expiration-timestamp').click(function () {
		if (jQuery('#cbp-expiration-timestampdiv').is(":hidden")) {
			jQuery('#cbp-expiration-timestampdiv').slideDown("normal");
			jQuery(this).hide();
		}
		return false;
	});

	jQuery('.cbp-reset-expiration').click(function() {
		var confirmed = confirm('Are you sure you want to remove the expiration time from this banner?');
		
		if ( confirmed )
		{			
			/* Reset time inputs to their original state */
			jQuery('#cbp-expiration-mm').val(jQuery('#cbp-expiration-hidden_mm').val());
			jQuery('#cbp-expiration-jj').val(jQuery('#cbp-expiration-hidden_jj').val());
			jQuery('#cbp-expiration-aa').val(jQuery('#cbp-expiration-hidden_aa').val());
			jQuery('#cbp-expiration-hh').val(jQuery('#cbp-expiration-hidden_hh').val());
			jQuery('#cbp-expiration-mn').val(jQuery('#cbp-expiration-hidden_mn').val());
			
			/* Set the "Reset To Never" flag */
			jQuery('#cbp-reset-to-never').val('1');
			
			/* Reset the UI */
			jQuery('#cbp-expiration-timestampdiv').slideUp("normal");
			jQuery('#cbp-expiration-timestamp').html('Expires: <b>Never</b>');
			jQuery('.edit-expiration-timestamp').show();
			return false;
		}
	});

	jQuery('.expiration-cancel-timestamp').click(function() {
		jQuery('#cbp-expiration-timestampdiv').slideUp("normal");
		jQuery('#cbp-expiration-mm').val(jQuery('#cbp-expiration-hidden_mm').val());
		jQuery('#cbp-expiration-jj').val(jQuery('#cbp-expiration-hidden_jj').val());
		jQuery('#cbp-expiration-aa').val(jQuery('#cbp-expiration-hidden_aa').val());
		jQuery('#cbp-expiration-hh').val(jQuery('#cbp-expiration-hidden_hh').val());
		jQuery('#cbp-expiration-mn').val(jQuery('#cbp-expiration-hidden_mn').val());
		jQuery('#cbp-expiration-timestamp').html(stamp);
		jQuery('.edit-expiration-timestamp').show();
		return false;
	});

	jQuery('.expiration-save-timestamp').click(function () { // crazyhorse - multiple ok cancels
		var aa = jQuery('#cbp-expiration-aa').val(),
			mm = jQuery('#cbp-expiration-mm').val(),
			jj = jQuery('#cbp-expiration-jj').val(),
			hh = jQuery('#cbp-expiration-hh').val(),
			mn = jQuery('#cbp-expiration-mn').val();
		var newD = new Date( aa, mm - 1, jj, hh, mn );

		if ( newD.getFullYear() != aa || (1 + newD.getMonth()) != mm || newD.getDate() != jj || newD.getMinutes() != mn ) {
			jQuery('.cbp-expiration-timestamp-wrap', '#cbp-expiration-timestampdiv').addClass('form-invalid');
			return false;
		} else {
			jQuery('.cbp-expiration-timestamp-wrap', '#cbp-expiration-timestampdiv').removeClass('form-invalid');
		}

		jQuery('#cbp-expiration-timestampdiv').slideUp("normal");
		jQuery('.edit-expiration-timestamp').show();
		jQuery('#cbp-expiration-timestamp').html(
			'&nbsp;' + cbp_expires_L10n.expires + ' <b>' +
			jQuery( '#cbp-expiration-mm option[value="' + mm + '"]' ).text() + ' ' +
			jj + ', ' +
			aa + ' @ ' +
			hh + ':' +
			mn + '</b> '
		);
		
		/* Clear the "Reset To Never" flag */
		jQuery('#cbp-reset-to-never').val('0');			
		return false;
	});
};

jQuery(gp_initialize_custom_banners_admin);