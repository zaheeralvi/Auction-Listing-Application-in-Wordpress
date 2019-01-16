(function($)
{	
    "use strict";

    $(document).ready(function()
    {	
		// set sidebar main menu option
        avia_sidebar_menu();
    });
	
	
	function avia_sidebar_menu()
	{
		var win				= $(window),
			main			= $('#main'),
			sb_header		= $('.html_header_sidebar #header_main'),
            sidebar			= $('.html_header_sidebar #header.av_conditional_sticky');
            
        if(!sb_header.length) return;
        // main.css({"min-height":sb_header.outerHeight()});
		
	
            
        if(!sidebar.length) return;
        
        var innerSidebar	= $('#header_main'),
       	 	wrap			= $('#wrap_all'),
       	 	fixed_frame		= $('.av-frame-top').height() * 2,
       	 	subtract 		= parseInt($('html').css('margin-top'), 10),
            calc_values 	= function()
            {	
            	if(innerSidebar.outerHeight() + fixed_frame < win.height()) 
				{ 	
					sidebar.addClass('av_always_sticky'); 
				}
				else
				{
					sidebar.removeClass('av_always_sticky'); 
				}
				
				wrap.css({'min-height': win.height() - subtract});
            };
        
        calc_values(); 
        win.on("debouncedresize av-height-change",  calc_values);
	}
	
	


})(jQuery);




