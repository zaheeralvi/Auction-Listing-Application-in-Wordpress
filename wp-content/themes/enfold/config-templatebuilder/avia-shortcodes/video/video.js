(function($)
{ 
	"use strict";

	
	$('body').on('click','.av-lazyload-video-embed .av-click-to-play-overlay', function(e){
		
		if(document.cookie.match(/aviaPrivacyVideoEmbedsDisabled/))
		{
			if (e.originalEvent === undefined) return; //human click only
				  
			var src_url = $(this).parents('.avia-video').data('original_url');
			if( src_url ) window.open(src_url , '_blank'); 
			
			return;
		}
		
		
		var clicked 	= $(this),
			container	= clicked.parents('.av-lazyload-video-embed'),
			video		= container.find('.av-video-tmpl').html();
			
			container.html(video);
	});
	
	$('.av-lazyload-immediate .av-click-to-play-overlay').trigger('click');
	
}(jQuery));