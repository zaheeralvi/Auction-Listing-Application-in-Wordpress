(function($)
{ 
	"use strict";
	
	// -------------------------------------------------------------------------------------------
	// Tab Section
	// -------------------------------------------------------------------------------------------
	
	$.fn.avia_sc_tab_section= function()
	{
		var win 			= $(window),
			browserPrefix 	= $.avia_utilities.supports('transition'),
			cssActive 		= this.browserPrefix !== false ? true : false,
			isMobile 		= $.avia_utilities.isMobile,
			transform3d		= document.documentElement.className.indexOf('avia_transform3d') !== -1 ? true : false,
			transition		= {};
			
		return this.each(function()
		{
			var container 		= $(this),
				tabs			= container.find('.av-section-tab-title'),
			    tab_outer		= container.find('.av-tab-section-outer-container'),
				tab_wrap		= container.find('.av-tab-section-tab-title-container'),
				tab_nav			= container.find('.av_tab_navigation'), 
				content_wrap	= container.find('.av-tab-section-inner-container'),
				single_tabs		= container.find('.av-animation-delay-container'), //for elements inside the tab that receive waypoint animation
				inner_content	= container.find('.av-layout-tab-inner'),
				sliding_active  = container.is('.av-tab-slide-transition'),
				flexible    	= container.is('.av-tab-content-auto'),
				current_content = container.find('.__av_init_open'),
				min_width		= 0,
				change_tab 		= function(e, prevent_hash)
				{
					e.preventDefault();
					
					var current_tab 	= $(e.currentTarget),
						current_arrow	= current_tab.find('.av-tab-arrow-container span'),
						tab_nr			= current_tab.data('av-tab-section-title');
						
						current_content = container.find('[data-av-tab-section-content="'+tab_nr+'"]');
					
					var new_bg			= current_content.data('av-tab-bg-color'),
						new_font		= current_content.data('av-tab-color'),
						prev_container 	= container.find('.av-active-tab-content').not('[data-av-tab-section-content="'+tab_nr+'"]');
	
					tabs.attr('style','').removeClass('av-active-tab-title');
					current_tab.removeClass('no-scroll');
					current_tab.addClass('av-active-tab-title');
					current_content.addClass("av-active-tab-content");
					
					if(new_bg !== "") current_arrow.css('background-color', new_bg);
					if(new_font !== "") current_tab.css('color', new_font);
						
					var new_pos = ((parseInt(tab_nr,10) - 1) * -100 );
					    if ($('body').hasClass('rtl')) {
	        				new_pos = ((parseInt(tab_nr,10) - 1) * 100 );
	    					}
					
					if(cssActive)
					{
						//move the slides
						new_pos = new_pos / tabs.length;
						transition['transform']  = transform3d ? "translate3d(" + new_pos  + "%, 0, 0)" : "translate(" + new_pos + "%,0)"; //3d or 2d transform?
						transition['left'] = "0%";
						content_wrap.css(transition);
					}
					else
					{
						content_wrap.css('left',  new_pos + "%");
					}
					
					set_tab_titlte_pos();
					set_slide_height();
					
					if(!prevent_hash) location.hash = current_tab.attr('href');
					
					setTimeout(function()
					{
						current_content.trigger('avia_start_animation_if_current_slide_is_active');
						single_tabs.not(current_content).trigger('avia_remove_animation');
						
					}, 600);	
					
				},
				set_min_width = function()
				{
					min_width = 0;
					tabs.each(function()
					{ 
						min_width += $(this).outerWidth(); 
					});
					
					tab_wrap.css('min-width',min_width);
				},
				
				set_slide_height = function()
				{				
					if(current_content.length && flexible)
					{
						var old_height = inner_content.height();
						inner_content.height('auto');
						
						var height = current_content.find('.av-layout-tab-inner').height(),
						    add_height = tab_wrap.height();
						
						tab_outer.css('max-height', height + add_height + 100);
						inner_content.height(old_height);
						inner_content.height(height);
						
						inner_content.css( 'overflow', 'hidden' );
						
						setTimeout(function() { win.trigger('av-height-change'); }, 600);
					}
				},
				
				set_tab_titlte_pos = function()
				{
					//	scroll the tabs if there is not enough room to display them all - rtl allign right to left !!
					var current_tab = container.find('.av-active-tab-title'),
						viewport	= container.width(),
						left_pos	= ( current_tab.position().left * - 1) - (current_tab.outerWidth() / 2) + (viewport / 2);
				
					if( ! $('body').hasClass("rtl") )
					{
						if( viewport >= min_width )
						{
							left_pos = 0;
						}
						
						if(left_pos + min_width < viewport) left_pos = (min_width - viewport) * -1;
						if(left_pos > 0) left_pos = 0;
					
						tab_wrap.css('left',left_pos );
					}
					else
					{
						var right_pos = 0;
						
						if( viewport < min_width )
						{
							if( left_pos + min_width > viewport )
							{
								if( left_pos > 0 ) left_pos = 0;
								
								var right_pos = (left_pos + min_width - viewport) * -1;
								tab_wrap.css('left', 'auto' );
								tab_wrap.css('right', right_pos );
							}
						}
						tab_wrap.css('left', 'auto' );
						tab_wrap.css('right', right_pos );
					}
				},
				switch_to_next_prev = function(e)
				{
					if(!isMobile) return;
					
					var clicked 		= $(e.currentTarget),
						current_tab 	= container.find('.av-active-tab-title');
						
						if(clicked.is('.av_prev_tab_section'))
						{
							current_tab.prev('.av-section-tab-title').trigger('click');
						}
						else
						{
							current_tab.next('.av-section-tab-title').trigger('click');
						}
				},
				
				get_init_open = function()
				{
					if(!hash && window.location.hash) var hash = window.location.hash;
		            		
					var open = tabs.filter('[href="'+hash+'"]');
					
					if(open.length)
					{
						if(!open.is('.active_tab')) open.trigger('click');
					}
					else
					{
						//set correct color
						container.find('.av-active-tab-title').trigger('click', true);
					}
				};
					
			$.avia_utilities.preload({
				
				container: current_content , 
				single_callback:  function(){ 
				
					tabs.on('click', change_tab);
					tab_nav.on('click', switch_to_next_prev);
					win.on('debouncedresize', set_tab_titlte_pos);	
					
					/**
					 * We had to remove av-height-change because this event is recursivly triggered in set_slide_height and lead to performance problems 
					 * AND broken layout - content was not displayed completly
					 * 
					 * Content elements that can can change their height and trigger av-height-change should trigger this additional event after to
					 * allow layout elements like tab section to react on this and then call av-height-change by themself
					 * 
					 * @since 4.2.3
					 */
					win.on('debouncedresize av-content-el-height-changed', set_slide_height);	
					
					set_min_width();
					set_slide_height(); 
					get_init_open();
				}
				
			});	
			
			content_wrap.avia_swipe_trigger({prev:'.av_prev_tab_section', next:'.av_next_tab_section'});
				
		});
	};
	
	
}(jQuery));