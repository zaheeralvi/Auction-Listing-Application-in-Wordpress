(function($)
{ 
	"use strict";
	
	// -------------------------------------------------------------------------------------------
	// Gallery shortcode javascript
	// -------------------------------------------------------------------------------------------
	
	$.fn.avia_sc_gallery = function(options)
	{
		return this.each(function()
		{
			var gallery = $(this), images = gallery.find('img'), big_prev = gallery.find('.avia-gallery-big');
	
	
			//trigger displaying of thumbnails
			gallery.on('avia_start_animation', function()
			{
				images.each(function(i)
				{
					var image = $(this);
					setTimeout(function(){ image.addClass('avia_start_animation') }, (i * 110));
				});
			});
			
			if(gallery.hasClass('deactivate_avia_lazyload')) gallery.trigger('avia_start_animation');
	
			//trigger thumbnail hover and big prev image change
			if(big_prev.length)
			{
				gallery.on('mouseenter','.avia-gallery-thumb a', function()
				{
					var _self = this;
	
					big_prev.attr('data-onclick', _self.getAttribute("data-onclick"));
					big_prev.height(big_prev.height());
					big_prev.attr('href', _self.href)
	
					var newImg 		= _self.getAttribute("data-prev-img"),
						oldImg 		= big_prev.find('img'),
						oldImgSrc 	= oldImg.attr('src');
	
					if(newImg != oldImgSrc)
					{
						var next_img = new Image();
						next_img.src = newImg;
						
						var $next = $(next_img);
						
						if(big_prev.hasClass('avia-gallery-big-no-crop-thumb'))
						{
							$next.css({'height':big_prev.height(),'width':'auto'});
						}
						
						big_prev.stop().animate({opacity:0}, function()
						{
							$next.insertAfter(oldImg);
							oldImg.remove();
							big_prev.animate({opacity:1});
							
							big_prev.attr('title',$(_self).attr('title'));
							
						});
					}
				});
	
				big_prev.on('click', function()
				{
					var imagelink = gallery.find('.avia-gallery-thumb a').eq(this.getAttribute("data-onclick") - 1);
	
					if(imagelink && !imagelink.hasClass('aviaopeninbrowser'))
					{
						imagelink.trigger('click');
					}
					else if(imagelink)
					{
						var imgurl = imagelink.attr("href");
	
						if(imagelink.hasClass('aviablank') && imgurl != '' )
						{
							window.open(imgurl, '_blank');
						}
						else if( imgurl != '' )
						{
							window.open(imgurl, '_self');
						}
					}
					return false;
				});
	
	
				$(window).on("debouncedresize", function()
				{
				  	big_prev.height('auto');
				});
	
			}
		});
	}

}(jQuery));