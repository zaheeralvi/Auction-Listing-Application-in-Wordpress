// -------------------------------------------------------------------------------------------
// Masonry
// -------------------------------------------------------------------------------------------

(function($)
{ 
	"use strict";

	$.fn.avia_masonry = function(options)
	{
		//return if we didnt find anything
		if(!this.length) return this;
		
		var the_body = $('body'),
			the_win	 = $(window),
			isMobile = $.avia_utilities.isMobile,
			loading = false,
			methods = {
		
			
			masonry_filter: function()
			{
				var current		= $(this),
					linktext	= current.html(),
			  		selector	= current.data('filter'),
			  		masonry 	= current.parents('.av-masonry:eq(0)'),
			  		container 	= masonry.find('.av-masonry-container:eq(0)'),
			  		links		= masonry.find('.av-masonry-sort a'),
			  		activeCat	= masonry.find('.av-current-sort-title');
					
					links.removeClass('active_sort');
					current.addClass('active_sort');
					container.attr('id', 'masonry_id_'+selector);
					
					if(activeCat.length) activeCat.html(linktext);
					
					methods.applyMasonry(container, selector, function()
					{
						container.css({overflow:'visible'});
					});
				
					setTimeout(function() { the_win.trigger('debouncedresize'); }, 500);
					
					return false;
			},
			
			applyMasonry: function(container, selector, callback)
			{
				var filters = selector ? {filter: '.'+selector} : {};
				
				filters['layoutMode'] = 'packery';
				filters['packery'] = {gutter:0};
				filters['percentPosition'] = true;
				filters['itemSelector'] = "a.isotope-item, div.isotope-item";
				filters['originLeft'] = $('body').hasClass('rtl') ? false : true;
						
				container.isotope(filters, function()
				{
					the_win.trigger('av-height-change');
				});
				
				if(typeof callback === 'function')
				{
					setTimeout(callback, 0);
				}
			},
			
			show_bricks: function(bricks, callback)
			{
				bricks.each(function(i)
				{
					var currentLink 	= $(this),
						browserPrefix 	= $.avia_utilities.supports('transition'),
						multiplier		= isMobile ? 0 : 100;
					
					setTimeout(function()
					{
						if(browserPrefix === false)
						{
							currentLink.css({visibility:"visible", opacity:0}).animate({opacity:1},1500);
						}
						else
						{
							currentLink.addClass('av-masonry-item-loaded');
						}
						
						if(i == bricks.length - 1 && typeof callback == 'function')
						{
							callback.call();
							the_win.trigger('av-height-change');
						}
						
					}, (multiplier * i));
				});
			},
			
			loadMore: function(e)
			{
				e.preventDefault();
				
				if(loading) return false;
				
				loading = true;
			
				var current		= $(this),
			  		data		= current.data(),
			  		masonry 	= current.parents('.av-masonry:eq(0)'),
			  		container	= masonry.find('.av-masonry-container'),
			  		items		= masonry.find('.av-masonry-entry'),
			  		loader		= $.avia_utilities.loading(),
			  		finished	= function(){ loading = false; loader.hide(); the_body.trigger('av_resize_finished'); };
			  			  	
			  	//calculate a new offset	
			  	if(!data.offset){ data.offset = 0; }	
			  	data.offset += data.items;
			  	data.action = 'avia_ajax_masonry_more';
			  	data.loaded = []; //prevents duplicate entries from beeing loaded when randomized is active
			  	
			  	items.each(function(){
				  	var item_id = $(this).data('av-masonry-item');
				  	if(item_id) data.loaded.push( item_id );
			  	});
			  	
			  	 $.ajax({
					url: avia_framework_globals.ajaxurl,
					type: "POST",
					data:data,
					beforeSend: function()
					{
						loader.show();
					},
					success: function(response)
					{
						if(response.indexOf("{av-masonry-loaded}") !== -1)
						{
							//fetch the response. if any php warnings were displayed before rendering of the items the are removed by the string split
							var response  = response.split('{av-masonry-loaded}'),
								new_items = $(response.pop()).filter('.isotope-item');
								
								//check if we got more items than we need. if not we have reached the end of items
								if(new_items.length > data.items)
								{
									new_items = new_items.not(':last');
								}
								else
								{
									current.addClass('av-masonry-no-more-items');
								}
								
								var load_container = $('<div class="loadcontainer"></div>').append(new_items);
								
								
								
								$.avia_utilities.preload({container: load_container, single_callback:  function()
								{
									var links = masonry.find('.av-masonry-sort a'),
										filter_container = masonry.find('.av-sort-by-term'),
										allowed_filters = filter_container.data("av-allowed-sort");
									
									filter_container.hide();
									
									loader.hide();
									container.isotope( 'insert', new_items); 
									$.avia_utilities.avia_ajax_call(masonry);
									setTimeout( function(){ methods.show_bricks( new_items , finished); },150);
									setTimeout(function(){ the_win.trigger('av-height-change'); }, 550);
									if(links)
									{
										$(links).each(function(filterlinkindex)
										{
											var filterlink = $(this),
											sort = filterlink.data('filter');
	
											if(new_items)
											{
											    $(new_items).each(function(itemindex){
											        var item = $(this);
													
											        if(item.hasClass(sort) && allowed_filters.indexOf(sort) !== -1)
											        {
											            var term_count = filterlink.find('.avia-term-count').text();
											            filterlink.find('.avia-term-count').text(' ' + (parseInt(term_count) + 1) + ' ');
											
											            if(filterlink.hasClass('avia_hide_sort'))
											            {
											                filterlink.removeClass('avia_hide_sort').addClass('avia_show_sort');
											                masonry.find('.av-masonry-sort .'+sort+'_sep').removeClass('avia_hide_sort').addClass('avia_show_sort');
											                masonry.find('.av-masonry-sort .av-sort-by-term').removeClass('hidden');
											            }
											        }
											    });
											}
										});
	
									}
	
	                                				filter_container.fadeIn();
								}
							});
						}
						else
						{
							finished();
						}
					},
					error: finished,
					complete: function()
					{
					    setTimeout(function() { the_win.trigger('debouncedresize'); }, 500);
					}
				});
			}
	
		};
	
		return this.each(function()
		{	
			var masonry			= $(this),
				container 		= masonry.find('.av-masonry-container'),
				bricks			= masonry.find('.isotope-item'), 
				filter			= masonry.find('.av-masonry-sort').css({visibility:"visible", opacity:0}).on('click', 'a',  methods.masonry_filter),
				load_more		= masonry.find('.av-masonry-load-more').css({visibility:"visible", opacity:0});
				
			$.avia_utilities.preload({container: container, single_callback:  function()
			{
				var start_animation = function()
				{ 
					filter.animate({opacity:1}, 400);
					
					//fix for non aligned elements because of scrollbar
					if(container.outerHeight() + container.offset().top + $('#footer').outerHeight() > $(window).height())
					{
						$('html').css({'overflow-y':'scroll'});
					}
					
					methods.applyMasonry(container, false, function()
					{
						masonry.addClass('avia_sortable_active');
						container.removeClass('av-js-disabled '); 
					});
					
					methods.show_bricks(bricks, function()
					{
						load_more.css({opacity:1}).on('click',  methods.loadMore);
					});
					
					//container.isotope( 'reLayout' );
	
				};
				
				if(isMobile)
				{
					start_animation();
				}
				else
				{
					masonry.waypoint(start_animation , { offset: '80%'} );
				}
						
				// update columnWidth on window resize
				$(window).on( 'debouncedresize', function()
				{
				  	methods.applyMasonry(container, false, function()
					{
						masonry.addClass('avia_sortable_active');
					});
				});
			}
		});
			
			
		});
	};

	
}(jQuery));
