var gold_plugins_init_mailchimp_form = function () {
	var $form = jQuery('#mc-embedded-subscribe-form');
	if ($form.length > 0) {	
	
		//if already subscribed, cut to the chase
		if ( (jQuery('#gold_plugins_already_subscribed').val() == 1) ) {
			gold_plugins_replace_form_with_coupon_box($form);
		}
	
		// bind to form's submit action to reveal coupon box
		$form.bind('submit', function () {
			alert("is this working?");
			
			gold_plugins_replace_form_with_coupon_box($form);
			
			// tell wordpress to always show the coupons from now on
			setUserSetting( '_c_b_ml_has_subscribed', '1' );
			return true;
		});
	}
};

var gold_plugins_replace_form_with_coupon_box = function ($form) {
	var coupon_box = gold_plugins_get_coupon_box();
	$form.after(coupon_box);
	$form.css('display', 'none');
};

var gold_plugins_get_coupon_box = function () {
	var coupon_html = '<div id="mc-show-coupon-codes"> <h3>Redeem Your Discount Now:</h3> <p class="thx">Thanks for subscribing! Please use the links below to save 20% on @plugin_name.</p> <div class="upgrade_links"> <div class="upgrade_link smallBlueButton"> <div class="package"> <a href="@personal_url" target="_blank">Personal License - <strike>$59</strike> $47.20</a> </div> <div class="desc"> <a href="@personal_url" target="_blank">Use it on a single website</a> </div> </div> <div class="upgrade_link smallBlueButton"> <div class="package"> <a href="@biz_url" target="_blank">Business License - <strike>$99</strike> $79.20</a> </div> <div class="desc"> <a href="@biz_url" target="_blank">Use it on any 3 websites!</a> </div> </div> <div class="upgrade_link smallBlueButton"> <div class="package"> <a href="@dev_url" target="_blank">Developer License - <strike>$199</strike> $159.20</a> </div> <div class="desc"> <a href="@dev_url" target="_blank">Use it on unlimited websites!</a> </div> </div> </div> </div>';
	
	// replace links in the HTML before inserting it
	$plugin_name = jQuery('#mc-upgrade-plugin-name').val();
	$personal_url = jQuery('#mc-upgrade-link-per').val();
	$biz_url = jQuery('#mc-upgrade-link-biz').val();
	$dev_url = jQuery('#mc-upgrade-link-dev').val();
	coupon_html = coupon_html.replace(/@plugin_name/g, $plugin_name);
	coupon_html = coupon_html.replace(/@personal_url/g, $personal_url);
	coupon_html = coupon_html.replace(/@biz_url/g, $biz_url);
	coupon_html = coupon_html.replace(/@dev_url/g, $dev_url);						
	var coupon_div = jQuery(coupon_html);

	// make the whole buttons clickable
	coupon_div.on('click', '.upgrade_link', function (e) {
		if( !jQuery("a").is(e.target) ) {
			$href = jQuery(this).find('a:first').attr('href');
			// try to open in a new tab
			window.open(
			  $href,
			  '_blank'
			);
			return false;			
		}
		return true;
	});	
	return coupon_div;
};