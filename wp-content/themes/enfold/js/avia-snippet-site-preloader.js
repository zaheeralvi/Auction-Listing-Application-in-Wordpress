(function($)
{	
    "use strict";
    
    //fix back forward cache issue: http://stackoverflow.com/questions/11979156/mobile-safari-back-button
    $(window).bind("pageshow", function(event) {
	    if (event.originalEvent.persisted) {
	     avia_site_preloader();
	    }
	});
    
    $(document).ready(function()
    {	
		//site preloader script
        avia_site_preloader();
    });
	
	
	function avia_site_preloader()
	{
		var win = $(window), preloader_active = $('html.av-preloader-active'), pre_wrap;
		
		if( preloader_active.length )
		{	
			var hide = function()
			{
				pre_wrap.avia_animate({opacity:0}, function()
	    		{
	    			preloader_active.removeClass('av-preloader-active');
	    		});
			};
			
			
			pre_wrap = $('.av-siteloader-wrap');
			setTimeout(function()
			{
				$.avia_utilities.preload({container: preloader_active, global_callback: hide });
				
				//fallback
				setTimeout(function()
				{
					if(preloader_active.is('.av-preloader-active')) 
					{ 
						hide(); 
						$.avia_utilities.log('Hide Preloader (Fallback)');
					}
				
				},4000);
				
				if( pre_wrap.is('.av-transition-enabled') )
				{
					var comp = new RegExp(location.host), 
						exclude = " .no-transition, .mfp-iframe, .lightbox-added, a.avianolink, .grid-links-ajax a, #menu-item-search a, .wp-playlist-caption";
					
					preloader_active.on('click', 'a:not('+exclude+')',function(e)
					{	
						if(!e.metaKey && !e.ctrlKey && !e.altKey && !e.shiftKey)
						{	
							var link = this; 
							if(comp.test(link.href) && link.href.split('#')[0] != location.href.split('#')[0] && link.target == "")
							{
								if(link.href.indexOf('mailto:') == -1 && link.href.indexOf('add-to-cart=') == -1 )
								{
							       	e.preventDefault();
							       	preloader_active.addClass('av-preloader-active av-preloader-reactive');
									pre_wrap.avia_animate({opacity:1}, function()
									{
										window.location = link.href;
									});
								}
							}
						}
					});
				}
				
				
			}, 500);
		}
	}


})(jQuery);




