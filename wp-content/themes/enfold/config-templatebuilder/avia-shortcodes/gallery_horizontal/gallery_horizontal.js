// -------------------------------------------------------------------------------------------
// Horizontal Gallery
// -------------------------------------------------------------------------------------------

(function($)
{ 
	"use strict";

	$.fn.avia_hor_gallery= function(options)
	{
		var defaults =
			{
				slide_container	: '.av-horizontal-gallery-inner', //element with max width
				slide_element	: '.av-horizontal-gallery-slider', //element that gets moved
				slide_content	: '.av-horizontal-gallery-wrap',
				active			: 'av-active-gal-item',				// must be a class string without the . in front
				prev			: '.av-horizontal-gallery-prev',
				next			: '.av-horizontal-gallery-next'
			};
	
		var options = $.extend(defaults, options);
		
		var win 			= $(window),
			browserPrefix 	= $.avia_utilities.supports('transition'),
			cssActive 		= this.browserPrefix !== false ? true : false,
			isMobile 		= $.avia_utilities.isMobile,
			transform3d		= document.documentElement.className.indexOf('avia_transform3d') !== -1 ? true : false,
			transition		= {};
			
		return this.each(function()
		{
			var container 			= $(this),
				slide_container 	= container.find(options.slide_container),
				slide_element		= container.find(options.slide_element),
				slide_content		= container.find(options.slide_content),
				prev				= container.find(options.prev),
				next				= container.find(options.next),
				imgs				= container.find('img'),
				
				all_elements_width 	= 0,
				currentIndex		= false,
				initial				= container.data('av-initial'),
				
				set_up = function( init )
				{
					var sl_height = (slide_container.width() / 100 ) * slide_container.data('av-height');
					
					slide_container.css({'padding':0}).height(sl_height);
					
					//fixes img distortion when resizing browser:
					imgs.css('display','inline-block');
					setTimeout(function(){ imgs.css('display','block'); }, 10);
					
					//calculate the slidelement width based on the elements inside
					all_elements_width = 0;
					
					slide_content.each(function()
					{ 
						all_elements_width += $(this).outerWidth( true ); 
					});
					
					slide_element.css( 'min-width' , all_elements_width );
					
					if(currentIndex !== false )
					{
						change_active(currentIndex);
					}
				},
				change_active = function(index)
				{
					//scroll the tabs if there is not enough room to display them all
					var current 	= slide_element.find(options.slide_content).eq(index),
						viewport	= slide_container.width(),
						modifier	= container.data('av-enlarge') > 1  && currentIndex == index ? container.data('av-enlarge') : 1,
						outerWidth	= current.outerWidth( true ) * modifier,
						margin_right= parseInt( current.css('margin-right') , 10 ) / 2,
						left_pos	= viewport < all_elements_width ? (current.position().left * - 1) - (outerWidth / 2) + (viewport / 2): 0;
					
					//center properly
					left_pos = left_pos + margin_right;
					
					//out of bounce right side
					if(left_pos + all_elements_width < viewport) left_pos = (all_elements_width - viewport - parseInt(current.css('margin-right'),10) ) * -1;
					
					//out of bounce left side
					if(left_pos > 0) left_pos = 0;
					
					//set pos
					slide_element.css('left',left_pos );
					
					slide_container.find("." +options.active).removeClass(options.active);
					current.addClass(options.active);
					currentIndex = index;
					
				};
	
				
			 $.avia_utilities.preload({container: container , global_callback:  function()
			 {
				 // activate behavior
				set_up( 'init' );
				win.on('debouncedresize', set_up);
				if(initial) change_active(initial - 1);
				
				setTimeout(function(){
					container.addClass('av-horizontal-gallery-animated');
				},10); 
			
			  }});
				
			
			
			
			
			
			
			//swipe on mobile
			slide_element.avia_swipe_trigger({prev:options.prev, next:options.next});
			
			//element click
			slide_content.on('click', function(e)
			{
				var current = $(this);
				var index = slide_content.index(current);
				
				if(currentIndex === index)
				{
					if(container.data('av-enlarge') > 1 && !$(e.target).is('a') )
					{
						//slide_container.find("." +options.active).removeClass(options.active);
						//currentIndex = false;	
					}
					return;
				}
				
				change_active(index);
			});
			
			prev.on('click', function(e)
			{
				if(currentIndex === false) currentIndex = 1;
				var index = currentIndex - 1;
				if(index < 0) index = 0;
				
				change_active(index);
			});
			
			next.on('click', function(e)
			{
				if(currentIndex === false) currentIndex = -1;
				var index = currentIndex + 1;
				if(index > slide_content.length - 1) index = slide_content.length - 1;
				
				change_active(index);
			});
			
			//if its a desktop browser add arrow navigation, otherwise add touch nav
			if(!isMobile)
			{
				container.avia_keyboard_controls({ 37: options.prev, 39: options.next });
			}
			else
			{
				container.avia_swipe_trigger({next: options.next, prev: options.prev});
			}
			
			
		
		});
	};


	
}(jQuery));


