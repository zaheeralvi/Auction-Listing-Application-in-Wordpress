/**
 * This file holds javascript functions needed in frontend for the functionallity of the facebook widgets
 *
 * @author		Christian "Kriesi" Budschedl
 * @copyright	Copyright ( c ) Christian Budschedl
 * @link		http://kriesi.at
 * @link		http://aviathemes.com
 * @since		Version 1.0
 * @package 	AviaFramework
 */

"use strict";

/**
 * Avia Facebook widgets in frontend
 */
(function($)
{
	var objAviaFacebook = null;
	
	var AviaFacebook = function(){
		
		objAviaFacebook = this;
		
		this.document = $( document );
		this.confirm_page_buttons = this.document.find('.avia_fb_likebox .av_facebook_widget_confirm_link');
		
		this.bind_events();
	};
	AviaFacebook.prototype = {
		
		bind_events: function()
		{
			this.confirm_page_buttons.on( 'click', this.load_fb_page_widget );
		},
		
		create_script: function( id, src  )
		{
			var current = $('script[id="' + id + '"]');
			if( current.length > 0 )
			{
				FB.XFBML.parse();
				return;
			}
			
			var	script 	= document.createElement('script');
			script.id = id;
			script.type = 'text/javascript';	
			script.src 	= src;
			
			document.body.appendChild(script);
		},
		
		load_fb_page_widget: function( event )
		{
			event.preventDefault();
			
			var button = $(this);
			var container = button.closest('.avia_fb_likebox');
			
			var fb_page_container = container.find('.av_facebook_widget_main_wrap');
			
			fb_page_container.replaceWith( button.data('fbhtml') );
			objAviaFacebook.create_script( button.data('fbscript_id'), button.data('fbscript') );
		}
	};
	
	$(function()
	{
		new AviaFacebook();
 	});
	
})(jQuery);	 