/* 
 * This file holds javascript functions needed in frontend for the functionallity of the Google Maps widgets and shortcodes
 * Handles conditional loading of Google API script.
 *
 * @author		Christian "Kriesi" Budschedl
 * @copyright	Copyright ( c ) Christian Budschedl
 * @link		http://kriesi.at
 * @link		http://aviathemes.com
 * @since		Version 1.0
 * @package 	AviaFramework
 * 
 */

"use strict";

/**
 * Avia Google Maps in frontend
 */
(function($)
{
	var objAviaGoogleMaps = null;
	
	var AviaGoogleMaps = function(){
		
		if( 'undefined' == typeof window.av_google_map || 'undefined' == typeof avia_framework_globals )
		{
			return;
		}
		
		if( objAviaGoogleMaps != null )
		{
			return;
		}
		
		objAviaGoogleMaps = this;
		
		this.document = $( document );
		this.script_loading = false;
		this.script_loaded = false;
		this.script_source = avia_framework_globals.gmap_avia_api;
		this.maps = {};
		this.loading_icon_html = '<div class="ajax_load"><span class="ajax_load_inner"></span></div>';
		
		this.LoadAviaMapsAPIScript();
	};
	
	AviaGoogleMaps.prototype = {
		
		LoadAviaMapsAPIScript: function()
		{
			this.maps = $('body').find( '.avia-google-map-container' );
			if( this.maps.length == 0 )
			{
				return;
			}
			
			//	Check if we need to load the api or we have only links to Google Maps page
			var needToLoad = false;
			this.maps.each(function( index ) {
						var container = $(this);
						if( container.hasClass('av_gmaps_show_unconditionally') || container.hasClass('av_gmaps_show_delayed') )
						{
							needToLoad = true;
							return false;
						}
					});
					
			if( ! needToLoad )
			{
				return;
			}
			
			//check if maps are disabled by user setting via cookie. 
			if(document.cookie.match(/aviaPrivacyGoogleMapsDisabled/))
			{
				$('.av_gmaps_main_wrap').addClass('av-maps-user-disabled');
				return;
			}
			
			//	Check if our API already loaded
			if( typeof $.AviaMapsAPI != 'undefined' )
			{
				this.AviaMapsScriptLoaded();
				return;
			}
			
			$('body').on( 'avia-google-maps-api-script-loaded', $.proxy( this.AviaMapsScriptLoaded, this ));
			
			this.script_loading = true;
			
			var script 	= document.createElement('script');
					script.id = 'avia-gmaps-api-script';
					script.type = 'text/javascript';	
					script.src 	= this.script_source;

      		document.body.appendChild(script);
		},
		
		AviaMapsScriptLoaded: function()
		{
			this.script_loading = false;
			this.script_loaded = true;
			
			var object = this;
			
			// Now we bind maps with AviaMapsAPI via aviaMaps
			this.maps.each(function( index ) {
						var container = $(this);
						
						if( container.hasClass('av_gmaps_show_page_only') )
						{
							return;
						}
						
						var mapid = container.data('mapid');
						
						//	skip container if no map info found
						if( 'undefined' == typeof window.av_google_map[mapid] )
						{
							console.log( 'Map cannot be displayed because no info: ' + mapid);
							return;
						}
						
						if( container.hasClass('av_gmaps_show_unconditionally') )
						{
							container.aviaMaps();
//							container.removeClass('av_gmaps_show_unconditionally');
						}
						else if( container.hasClass('av_gmaps_show_delayed') )
						{
							var wrap = container.closest('.av_gmaps_main_wrap');
							var confirm = wrap.find('a.av_text_confirm_link');
							
							confirm.on('click', object.AviaMapsLoadConfirmed );
						}
						else
						{
							console.log( 'Map cannot be displayed because missing display class: ' + mapid);
						}
					});
		},
		
		AviaMapsLoadConfirmed: function( event )
		{
			event.preventDefault();
			
			var confirm = $(this);
			var container = confirm.closest('.av_gmaps_main_wrap').find('.avia-google-map-container');
			container.aviaMaps();
		}
	};
	
	$(function()
	{
		new AviaGoogleMaps();
 	});
	
})(jQuery);	 