// -------------------------------------------------------------------------------------------
// Big Number animation shortcode javascript
// -------------------------------------------------------------------------------------------

(function($)
{
	// options.simple_up = dont prepend leading zeros, options.instant_start = trigger counting instantly, options.start_timer = delay when to start counting
	$.fn.avia_sc_animated_number = function(options) 
	{
		if(!this.length) return;
		if(this.is('.avia_sc_animated_number_active')) return;
		
		this.addClass('avia_sc_animated_number_active');
	
		var skipStep = false,
			simple_upcount 	= (options && options.simple_up) ? true : false,
			start_timer 	= (options && options.start_timer) ? options.start_timer : 300,
		start_count = function(element, countTo, increment, current, fakeCountTo)
		{
			
			
			//calculate the new number
			var newCount = current + increment;
			
			//if the number is bigger than our final number set the number and finish
			if(newCount >= fakeCountTo) 
			{
				element.text(countTo); //exit
				
			}
			else
			{
				var prepend = "", addZeros = countTo.toString().length - newCount.toString().length
				
				//if the number has less digits than the final number some zeros where omitted. add them to the front
				for(var i = addZeros; i > 0; i--){ prepend += "0"; }
				
				if(simple_upcount) prepend = 0;
				element.text(prepend + newCount);
				
				window.requestAnimationFrame(function(){ start_count(element, countTo, increment, newCount, fakeCountTo) });
			}
		};
	
		return this.each(function()
		{
			var number_container = $(this), elements = number_container.find('.__av-single-number'), countTimer = number_container.data('timer') || 3000;
			
			//prepare elements
			elements.each(function(i)
			{
				var element = $(this), text = element.text();
				if(window.addEventListener) element.text( text.replace(/./g, "0")); /*https://github.com/AviaThemes/wp-themes/issues/812*/
			});
			
			//trigger number animation
			number_container.addClass('number_prepared').on('avia_start_animation', function()
			{
				if(number_container.is('.avia_animation_done')) return;
				number_container.addClass('avia_animation_done');
				
				elements.each(function(i)
				{
					var element = $(this), countTo = element.data('number'), fakeCountTo = countTo, current = parseInt(element.text(),10), zeroOnly = /^0+$/.test(countTo), increment = 0;
					
					//fallback for decimals like 00 or 000
					if(zeroOnly && countTo !== 0) fakeCountTo = countTo.replace(/0/g, '9');
					
					increment = Math.round( fakeCountTo * 32 / countTimer);
					if(increment == 0 || increment % 10 == 0) increment += 1;
					
					setTimeout(function(){ start_count(element, countTo, increment, current, fakeCountTo);}, start_timer);
				});
			});
			
			if(options && options.instant_start == true)
			{
				number_container.trigger('avia_start_animation');
			}
			
			
		});
	}
})(jQuery);