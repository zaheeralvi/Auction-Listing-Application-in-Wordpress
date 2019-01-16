(function($)
{	
    "use strict";
    
    $(document).ready(function()
    {	
		// decreases header size when user scrolls down
        avia_header_size();
    });
    
    
    function av_change_class($element, change_method, class_name)
	{	
		if($element[0].classList)
		{
			if(change_method == "add") 
			{
				$element[0].classList.add(class_name);
			}
			else
			{
				$element[0].classList.remove(class_name);
			}
		}
		else
		{
			if(change_method == "add") 
			{
				$element.addClass(class_name);
			}
			else
			{
				$element.removeClass(class_name);
			}
		}
	}
	
	
	function avia_header_size()
    {
        var win				= $(window),
            header          = $('.html_header_top.html_header_sticky #header'),
            unsticktop		= $('.av_header_unstick_top');
            
        if(!header.length && !unsticktop.length) return;
        
        var logo            = $('#header_main .container .logo img, #header_main .container .logo a'),
            elements        = $('#header_main .container:not(#header_main_alternate>.container), #header_main .main_menu ul:first-child > li > a:not(.avia_mega_div a, #header_main_alternate a), #header_main #menu-item-shop .cart_dropdown_link'),
            el_height       = $(elements).filter(':first').height(),
            isMobile        = $.avia_utilities.isMobile,
            scroll_top		= $('#scroll-top-link'),
            transparent 	= header.is('.av_header_transparency'),
            shrinking		= header.is('.av_header_shrinking'),
            topbar_height	= header.find('#header_meta').outerHeight(),
            set_height      = function()
            {	
                var st = win.scrollTop(), newH = 0, st_real = st;
				
				if(unsticktop) st -= topbar_height; 
				if(st < 0) st = 0;
				
				if(shrinking && !isMobile)
                {
	                if(st < el_height/2)
	                {
	                    newH = el_height - st;
	                    if(st <= 0){
							newH = el_height;
					    }
	                    
	                    av_change_class(header, 'remove', 'header-scrolled');
	                    //header.removeClass('header-scrolled');
	                }
	                else
	                {
	                    newH = el_height/2;
	                    //header.addClass('header-scrolled');
	                    av_change_class(header, 'add', 'header-scrolled');
	                }
	                
	                if(st - 30 < el_height)
	                {
	                    av_change_class(header, 'remove', 'header-scrolled-full');
	                }
	                else
	                {
	                    av_change_class(header, 'add', 'header-scrolled-full');
	                }
	                
	                
	                elements.css({'height': newH + 'px', 'lineHeight': newH + 'px'});
                	logo.css({'maxHeight': newH + 'px'});
                }
                
                if(unsticktop.length)
            	{
                	if( st <= 0)
                	{
	                	if(st_real <= 0) st_real = 0;
                		unsticktop.css({"margin-top":"-"+st_real+"px"});
					}
					else
					{
                		unsticktop.css({"margin-top":"-"+topbar_height+"px"});
					}
            	}
                
                if(transparent)
                {	
                	if(st > 50)
                	{	
                		//header.removeClass('av_header_transparency');
                		av_change_class(header, 'remove', 'av_header_transparency');
                	}
                	else
                	{
                		//header.addClass('av_header_transparency');
                		av_change_class(header, 'add', 'av_header_transparency');
                	}
                }

               
            };

            if($('body').is('.avia_deactivate_menu_resize')) shrinking = false;
            
            if(!transparent && !shrinking && !unsticktop.length) return;
            
			win.on( 'debouncedresize',  function(){ el_height = $(elements).attr('style',"").filter(':first').height(); set_height(); } );
            win.on( 'scroll',  function(){ window.requestAnimationFrame( set_height )} );
            set_height();
    }


})(jQuery);




