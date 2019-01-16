(function($)
{ 
	"use strict";
	
	// -------------------------------------------------------------------------------------------
	// Progress bar shortcode javascript
	// -------------------------------------------------------------------------------------------
	
	$.fn.avia_sc_progressbar = function(options)
	{
		return this.each(function()
		{
			var container = $(this), elements = container.find('.avia-progress-bar');
			
			
			//trigger displaying of progress bar
			container.on('avia_start_animation', function()
			{
				elements.each(function(i)
				{
					var element = $(this)
					
					setTimeout(function()
					{ 
						element.find('.progress').addClass('avia_start_animation') 
						element.find('.progressbar-percent').avia_sc_animated_number(
						{
							instant_start:true, simple_up:true, start_timer: 10
						});
						
					}, (i * 250));
				});
			});
		});
	}
	
}(jQuery));