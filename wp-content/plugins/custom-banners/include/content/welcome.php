<?php
// Custom Banners Welcome Page template

ob_start();
$learn_more_url = 'https://goldplugins.com/special-offers/upgrade-to-custom-banners-pro/?utm_source=custom_banners_free&utm_campaign=welcome_screen_upgrade&utm_content=col_1_learn_more';
$pro_registration_url = menu_page_url('custom-banners-settings', false) . '#tab-registration_options';
$settings_url = menu_page_url('custom-banners-settings', false);
$utm_str = '?utm_source=custom_banners_free&utm_campaign=welcome_screen_help_links';
$utm_str_2 = '?utm_source=custom_banners_free&utm_campaign=welcome_screen_upgrade_section';
$new_post_link = admin_url('post-new.php?post_type=banner&guided_tour=1');
?>

<p class="aloha_intro"><strong>Thank you for installing <?php echo $plugin_title; ?>!</strong> This page is here to help you get up and running. If you're already familiar with <?php echo $plugin_title; ?>, you can skip it and <a href="<?php echo $settings_url; ?>">continue to the Basic Settings page</a>.</p>
<p class="aloha_tip"><strong>Tip:</strong> You can always access this page via the <strong>Custom Banners Settings &raquo; About Plugin</strong> menu.</p>

<br>
<h1 id="getting_started">Getting Started With <?php echo $plugin_title; ?></h1>
<p id="jump_links" class="aloha_jump_links">
Jump To: <a href="#add_new_banners">How To Add New Banners</a> | 
<a href="#display_on_website">Displaying Banners on Your Website</a> | 
<a href="#add_slideshow">Displaying a Banner Slideshow</a> | 
<a href="#add_list_of_banners">Displaying a List of Banners</a>
<?php if (!$is_pro): ?>
| <a href="#upgrade_to_pro">Upgrade To Pro</a>
<?php endif; ?>
</p>
<br>
<br>

<h3 id="add_new_banners">How To Add New Banners</h3>
<p>On the left side of your screen, you'll see a new menu, <strong>Banners</strong> - here you can <a href="<?php echo $new_post_link; ?>">add new Banners</a>, <a href="<?php echo admin_url('edit.php?post_type=banner'); ?>">manage and update existing Banners</a>, and <a href="<?php echo admin_url('edit-tags.php?taxonomy=banner_groups&post_type=banner'); ?>">use the Banner Groups to organize them</a>.</p>

<h4>To Create a new Banner:</h4>
<ol class="aloha_instructions">
	<li>Select the Add New Banner menu to access the Add New Banner screen.</li>
	<li>Give your Banner a title. This is for your own reference only, and will not be displayed on your website.</li>
	<li>In the Banner Image meta box, select the link that says "Select Featured Image", and choose an image to use for your banner. You'll be able to change this image any time you like.</li>
	<li>In the Banner Caption meta box, enter any text you'd like to use as a caption for this image. This field is optional, and you can always change it later.</li>
	<li>In the Banner Information meta box, enter the URL that visitors should be sent to when they click on your banner into the <strong>Target URL</strong> field. If you'd like to have a call to action button displayed with this banner, enter the button text into the <strong>Call To Action Text</strong> field. "Learn More" is always a fine choice.</li>
	<li>If you're planning to add many banners, you might want to add this banner to a Banner Group. You can do this by using the Banner Group meta box, which appears on the right side of the screen. You can skip this for now if you'd like.</li>
	<li>Click the Publish button. Congratulations, our banner is now ready to use! </li>
</ol>	

<p><strong>Tip:</strong> You may want to go ahead and copy the shortcode for this banner from the Shortcodes meta box on the right side of your screen. You can paste this shortcode into any post, page, or sidebar where you'd like to display the banner. We'll also show you several other ways to display your banners in the <a href="#display_on_website">next section</a>.

<p>For more information on adding a new Banner, <a href="https://goldplugins.com/documentation/custom-banners-documentation/custom-banners-configuration-usage-instructions/<?php echo $utm_str; ?>#add_a_new_banner" target="_blank">see our documentation</a>.
</p>
<h4>Create Your First Banner Now</h4>
<p>Click the button below to create your first Banner. It will only take a moment, and it's easy to understand.</p>
<br>
<a href="<?php echo $new_post_link; ?>" class="button">Create A New Banner &raquo;</a></p>
<br>
<a href="#getting_started">Back To Top</a>
<br>
<br>
	
<h3 id="display_on_website">Displaying Your Banners on Your Website</h3>

<p>Once you've added your Banners, you'll want to display them on your website. We have several easy ways to choose from.</p>
<h4>Option 1) Add the banner shortcode to any page or post</h4>
<p>To display a single banner, simply add the banner's shortcode to any page or post on your website. You can find the shortcode for each banner by editing it, or in the <a href="<?php echo admin_url('edit.php?post_type=banner');?>">list of banners</a>. It will look like this: <span class="aloha_code">&#91;banner id="1234"]</span></p>

<p>To display a slideshow of banners or a list of banners, you can also use the <span class="aloha_code">&#91;banner]</span> shortcode, but you will want to generate the shortcodes using the Editor Buttons (as explained in the next method).</p>

<h4>Option 2) Use The Editor Buttons</h4>
<p>On any Add/Edit Post or Page screen, you'll now find a new menu - <strong>Banners</strong> - directly above the post editor. You can use this menu to add an individual Banner, a slideshow of several banners, or a list of banners. When you select any of the menu items, you'll be given options to choose which banners to use, what theme you'd like, and many more customizations.</p>

<p><strong>Tip:</strong> These menus will insert a shortcode with the options you've chosen into your page. You can copy this shortcode and use it on other posts and pages.</p>

<h4>Option 3) Display Your Banners with a Widget</h4>
<p>When you visit the Widgets screen (found under the Appearnce &raquo; Widgets menu), you'll find new widgets that you can add to your sidebars (or any other widgetized area).</p>

<p>The Single Banner widget will let you display a single banner of your choice.</p>

<p>The Banner List widget will let you display several banners at once, in a list. This is perfect for displaying your sponsors in a sidebar, for instance. You'll be able to choose which category to select banners from, whether to order them randomly or not, which theme to use and animated transition to use, and from many more options.</p>

<p>The Rotating Banner widget will let you display a slideshow of your banners. You'll be able to choose which category to select banners from, which theme to use, what animation to use when transitioning between banners, and from many more options.</p>


<h4>Further Reading</h4>
<p> For more information on displaying your banners, please <a href="https://goldplugins.com/documentation/custom-banners-documentation/custom-banners-configuration-usage-instructions/<?php echo $utm_str; ?>#add_a_new_banner">see our documentation</a>.</p>
<br>
<a href="#getting_started">Back To Top</a>
<br>
<br>
	
<h3 id="add_slideshow">Displaying A Slideshow of Banners on Your Website</h3>
<p><?php echo $plugin_title; ?> can display a Slideshow of your Banners, with an animated transition of your choice. This is perfect if you have several sponsors or banners that you'd like to display in one area. You can choose to show your Banners in a random order, or always in the same order.</p>

<p>We have two methods for you to choose from:</p>

<h4>Option 1) Use The Editor Buttons</h4>
<p>On any Add/Edit Post or Page screen, you'll now find a new menu - <strong>Banners</strong> - directly above the post editor. Select the <strong>Rotating Banner Widget</strong> from this menu.</p>

<p>You'll be given options to choose which banners to use, what order you'd like to display them in, what theme you'd like to use (if any), and many more customizations.</p>

<p><strong>Tip:</strong> These menus will insert a shortcode with the options you've chosen into your page. You can copy this shortcode and use it on other posts and pages.</p>

<h4>Option 2) Use The Rotating Banners Widget</h4>

<p>Visit the Widgets screen (found under the Appearnce &raquo; Widgets menu),  and add the Rotating Banner Widget to any of your sidebars. Select which category to select banners from, which theme to use, what animation to use when transitioning between banners, and your other options, then click Save.</p>

<h3 id="add_list_of_banners">Displaying A List of Banners on Your Website</h3>
<p><?php echo $plugin_title; ?> can also output a list of your Banners, in a vertical row. This is great if you have several sponsors or banners that you'd like to display in your sidebar. You can choose to display them in a random order, or always in the same order.</p>

<p>We have two methods for you to choose from:</p>

<h4>Option 1) Use The Editor Buttons</h4>
<p>On any Add/Edit Post or Page screen, you'll now find a new menu - <strong>Banners</strong> - directly above the post editor. Select the <strong>Banner List Widget</strong> from this menu.</p>

<p>You'll be given options to choose which banners to use, what order you'd like to display them in, what theme you'd like to use (if any), and many more customizations.</p>

<p><strong>Tip:</strong> These menus will insert a shortcode with the options you've chosen into your page. You can copy this shortcode and use it on other posts and pages.</p>

<h4>Option 2) Use The Banner List Widget</h4>

<p>Visit the Widgets screen (found under the Appearnce &raquo; Widgets menu),  and add the Banner List Widget to any of your sidebars. Select which category to select banners from, which theme to use, what animation to use when transitioning between banners, and your other options, then click Save.</p>

<br>
<a href="#getting_started">Back To Top</a>
<br>
<br>

<?php if (!$is_pro): ?>
<h3 id="upgrade_to_pro">Upgrade To Custom Banners Pro</h3>
<p><strong>Custom Banners Pro</strong> is the professional, fully-functional version of Custom Banners.</p>

<p>In addition to all of the great features of Custom Banners, it adds Impression and Click Tracking, over 50 professionally designed themes, font and color options for your banners and captions,  more animated tranisitions for your slideshows, and much more.</p>

<p><strong>Custom Banners Pro</strong> also includes techinical support and free software updates for a full-year after you purchase, and is backed by a full 30-day money back guarantee.</p>

<p><a href="https://goldplugins.com/special-offers/upgrade-to-custom-banners-pro/<?php echo $utm_str_2; ?>" class="button">Click Here To Learn More</a></p>


<br>
<a href="#getting_started">Back To Top</a>
<br>
<br>
<?php endif; ?>

<h4>Further Reading</h4>
<p> For more information on <?php echo $plugin_title; ?>, please <a href="https://goldplugins.com/documentation/custom-banners-pro-documentation/<?php echo $utm_str; ?>">see our documentation</a>.</p>

<hr>
<br>


<h1>Helpful Links</h1>
<div class="three_col">
	<div class="col">
		<?php if ($is_pro): ?>
			<h3>Custom Banners Pro: Active</h3>
			<p class="plugin_activated">Custom Banners Pro is licensed and active.</p>
			<a href="<?php echo $pro_registration_url; ?>">Registration Settings</a>
		<?php else: ?>
			<h3>Upgrade To Pro</h3>
			<p>Custom Banners Pro is the Professional, fully-functional version of Custom Banners, which features technical support and access to all features and themes.</p>
			<a class="button" href="<?php echo $learn_more_url; ?>">Click Here To Learn More</a>
		<?php endif; ?>
	</div>
	<div class="col">
		<h3>Getting Started</h3>
		<ul>
			<li><a href="<?php echo $new_post_link; ?>">Click Here To Add Your First Banner</a></li>
			<li><a href="https://goldplugins.com/documentation/custom-banners-documentation/custom-banners-configuration-usage-instructions/<?php echo $utm_str; ?>">Getting Started With <?php echo $plugin_title; ?></a></li>
			<li><a href="https://goldplugins.com/documentation/custom-banners-documentation/custom-banners-configuration-usage-instructions/<?php echo $utm_str; ?>#add_a_new_banner">How To Create Your First Banner</a></li>
			<li><a href="https://goldplugins.com/documentation/custom-banners-documentation/custom-banners-faqs/<?php echo $utm_str; ?>">Frequently Asked Questions (FAQs)</a></li>
			<li><a href="https://goldplugins.com/contact/<?php echo $utm_str; ?>">Contact Technical Support</a></li>
		</ul>
	</div>
	<div class="col">
		<h3>Further Reading</h3>
		<ul>
			<li><a href="https://goldplugins.com/documentation/custom-banners-documentation/<?php echo $utm_str; ?>"><?php echo $plugin_title; ?> Documentation</a></li>
			<li><a href="https://wordpress.org/support/plugin/custom-banners<?php echo $utm_str; ?>">WordPress Support Forum</a></li>
			<li><a href="https://goldplugins.com/documentation/custom-banners-documentation/custom-banners-pro-changelog/<?php echo $utm_str; ?>">Recent Changes</a></li>
			<li><a href="https://goldplugins.com/<?php echo $utm_str; ?>">Gold Plugins Website</a></li>
		</ul>
	</div>
</div>

<div class="continue_to_settings">
	<p><a href="<?php echo $settings_url; ?>">Continue to Basic Settings &raquo;</a></p>
</div>

<?php 
$content =  ob_get_contents();
ob_end_clean();
return $content;
?>