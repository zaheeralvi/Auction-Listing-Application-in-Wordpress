// -------------------------------------------------------------------------------------------
// 
// AVIA Magazine function for magazine sorting
// 
// -------------------------------------------------------------------------------------------
(function($)
{ 
	"use strict";
	
	var animating = false,
		methods = {
		
		switchMag: function(clicked, _self)
		{
			var current 		= $(clicked)
			
			if(current.is('.active_sort') || animating) return;
			
			var filter			= current.data('filter'),
				oldContainer	= _self.container.filter(':visible'),
				newContainer	= _self.container.filter('.' + filter);
			
			//switch Class
			animating = true;
			_self.sort_buttons.removeClass('active_sort');
			current.addClass('active_sort');
			
			//apply fixed heiht for transition
			_self.magazine.height(_self.magazine.outerHeight());
			
			//switch items
			oldContainer.avia_animate({opacity:0}, 200, function()
			{
				oldContainer.css({display:'none'});
				newContainer.css({opacity:0, display:'block'}).avia_animate({opacity:1}, 150, function()
				{
					_self.magazine.avia_animate({height: (newContainer.outerHeight() + _self.sort_bar.outerHeight())}, 150, function()
					{
						_self.magazine.height('auto');
						animating = false;
					});
					
				});
			});
		}
	};
	
	
	$.fn.aviaMagazine = function( options )
	{
		if(!this.length) return; 

		return this.each(function()
		{
			var _self = {};
			 
			_self.magazine		= $(this),
			_self.sort_buttons 	= _self.magazine.find('.av-magazine-sort a');
			_self.container		= _self.magazine.find('.av-magazine-group');
			_self.sort_bar		= _self.magazine.find('.av-magazine-top-bar');
			
			_self.sort_buttons.on('click', function(e){ e.preventDefault(); methods.switchMag(this, _self);  } );
		});
	}
	
}(jQuery));