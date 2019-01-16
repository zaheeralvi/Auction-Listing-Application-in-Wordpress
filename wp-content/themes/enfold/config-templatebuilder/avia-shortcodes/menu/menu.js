(function($)
{ 
	"use strict";
	$.avia_utilities = $.avia_utilities || {};
	
	$(document).ready(function()
    {	
		 //activates the sticky submenu
		$.avia_utilities = $.avia_utilities || {};
        if($.avia_utilities.avia_sticky_submenu)
		$.avia_utilities.avia_sticky_submenu(); 
    });
	
	
	$.avia_utilities.avia_sticky_submenu = function()
	{
		var win 		= $(window),
			html 		= $('html:first'),
			header  	= $('.html_header_top.html_header_sticky #header'),
			html_margin = parseInt( $('html:first').css('margin-top'), 10),
			setWitdth	= $('.html_header_sidebar #main, .boxed #main'),
			menus		= $('.av-submenu-container'),
			bordermod	= html.is('.html_minimal_header') ? 0 : 1,
			fixed_frame	= $('.av-frame-top').height(),
			calc_margin	= function()
			{
				html_margin = parseInt( html.css('margin-top'), 10);
				if(!$('.mobile_menu_toggle:visible').length)
				{
					$('.av-open-submenu').removeClass('av-open-submenu');
				}
				
				menus.filter('.av-sticky-submenu').each(function()
				{
					$(this).next('.sticky_placeholder').height($(this).height());
				});
				
			},
			calc_values	= function()
			{
				var content_width = setWitdth.width();
				html_margin = parseInt( html.css('margin-top'), 10);
				menus.width(content_width);
			},
			check 		= function(placeholder, no_timeout)
			{
				var menu_pos	= this.offset().top,
					top_pos 	= placeholder.offset().top,
					scrolled	= win.scrollTop(),
					modifier 	= html_margin, fixed = false;
										
					if(header.length) 
					{
						modifier += header.outerHeight() + parseInt( header.css('margin-top'), 10);
					}	
					
					if(fixed_frame)
					{
						modifier += fixed_frame;
					}
					
					if(scrolled + modifier > top_pos)
					{
						if(!fixed)
						{
							this.css({top: modifier - bordermod, position: 'fixed'}); fixed = true
						}
					}
					else
					{
						this.css({top: 'auto', position: 'absolute'}); fixed = false
					}
					
			},
			toggle = function(e)
			{
				e.preventDefault();
				
				var clicked = $(this), 
					menu 	= clicked.siblings('.av-subnav-menu');
				
					if(menu.hasClass('av-open-submenu'))
					{
						menu.removeClass('av-open-submenu');
					}
					else
					{
						menu.addClass('av-open-submenu');
					}
			};
		
		win.on("debouncedresize av-height-change",  calc_margin ); calc_margin();
			
		if(setWitdth.length)
		{
			win.on("debouncedresize av-height-change",  calc_values );
			calc_values();
		}
		
		
		menus.each(function()
        {
             var menu = $(this), sticky = menu.filter('.av-sticky-submenu'),  placeholder = menu.next('.sticky_placeholder'), mobile_button = menu.find('.mobile_menu_toggle');
             
             
             if(sticky.length) win.on( 'scroll',  function(){ window.requestAnimationFrame( $.proxy( check, sticky, placeholder) )} );

             if(mobile_button.length)
             {
                mobile_button.on( 'click',  toggle );
             }
        });
		
		
		html.on('click', '.av-submenu-hidden .av-open-submenu li a', function()
		{
			var current = $(this);
			
			var list_item = current.siblings('ul, .avia_mega_div');
			if(list_item.length)
			{
				if(list_item.hasClass('av-visible-sublist'))
				{
				    list_item.removeClass('av-visible-sublist');
				}
				else
				{
				    list_item.addClass('av-visible-sublist');
				}
				return false;
			}
		});
		
		$('.avia_mobile').on('click', '.av-menu-mobile-disabled li a', function()
		{
			var current = $(this);
			var list_item = current.siblings('ul');
			if(list_item.length)
			{
				if(list_item.hasClass('av-visible-mobile-sublist'))
				{
				    
				}
				else
				{
					$('.av-visible-mobile-sublist').removeClass('av-visible-mobile-sublist');
				    list_item.addClass('av-visible-mobile-sublist');
				    return false;
				}
				
			}
		});
		
		
		
	}
	
	
}(jQuery));