(function($)
{
	"use strict";
// -------------------------------------------------------------------------------------------
// Aviaccordion Slideshow 
// 
// accordion slider script
// -------------------------------------------------------------------------------------------

	$.AviaccordionSlider  =  function(options, slider)
	{
	    this.$slider  	= $( slider );
	    this.$inner	  	= this.$slider.find('.aviaccordion-inner');
	    this.$slides	= this.$inner.find('.aviaccordion-slide');
	    this.$images	= this.$inner.find('.aviaccordion-image');
	    this.$last		= this.$slides.filter(':last');
	    this.$titles  	= this.$slider.find('.aviaccordion-preview');
	    this.$titlePos  = this.$slider.find('.aviaccordion-preview-title-pos');
	    this.$titleWrap = this.$slider.find('.aviaccordion-preview-title-wrap');
	    this.$win	  	= $( window );
	    
	    if($.avia_utilities.supported.transition === undefined)
		{
			$.avia_utilities.supported.transition = $.avia_utilities.supports('transition');
		}
		
		this.browserPrefix 	= $.avia_utilities.supported.transition;
	    this.cssActive 		= this.browserPrefix !== false ? true : false;
	    this.transform3d	= document.documentElement.className.indexOf('avia_transform3d') !== -1 ? true : false;
		this.isMobile 		= $.avia_utilities.isMobile;
		this.property		= this.browserPrefix + 'transform',
		this.count			= this.$slides.length;
		this.open			= false;
		this.autoplay		= false;
		this.increaseTitle  = this.$slider.is(".aviaccordion-title-on-hover");
		// this.cssActive    = false; //testing no css3 browser
		
	    this._init( options );
	}

  	$.AviaccordionSlider.prototype =
    {
    	_init: function( options )
    	{
    		var _self = this;
    		_self.options = $.extend({}, options, this.$slider.data());
			 $.avia_utilities.preload({container: this.$slider , single_callback:  function(){ _self._kickOff(); }});
    	},
    	
    	_kickOff: function()
    	{
    		var _self = this;
    		
    		_self._calcMovement();
    		_self._bindEvents();
    		_self._showImages();
    		_self._autoplay();
    	},
    	
    	_autoplay: function()
    	{
    		var _self = this;
    		
    		if(_self.options.autoplay)
    		{
    			_self.autoplay = setInterval(function()
    			{
    				_self.open = _self.open === false ? 0 : _self.open + 1;
    				if(_self.open >= _self.count) _self.open = 0;
    				_self._move({}, _self.open);
    				
    			}, _self.options.interval * 1000)
    		}
    	},
    	
    	_showImages: function()
    	{
    		var _self = this, counter = 0, delay = 300, title_delay = this.count * delay;
    		
    		if(this.cssActive)
    		{
    			setTimeout(function(){ _self.$slider.addClass('av-animation-active'); } , 10);
    		}
    		
    		this.$images.each(function(i)
    		{
    			var current = $(this), timer = delay * (i + 1);
    				
    			setTimeout(function()
    			{ 
    				current.avia_animate({opacity:1}, 400, function()
    				{
    					current.css($.avia_utilities.supported.transition + "transform", "none");
    				}); 
    			},timer);
    		});
    		
    		if(_self.increaseTitle) title_delay = 0;
    		
    		this.$titlePos.each(function(i)
    		{
    			var current = $(this), new_timer = title_delay + 100 * (i + 1);
    					
    			setTimeout(function()
    			{ 
    				current.avia_animate({opacity:1}, 200, function()
    				{
    					current.css($.avia_utilities.supported.transition + "transform", "none");
    				}); 
    			},new_timer);
    		});
    	},
    	
    	_bindEvents: function()
    	{
    		var trigger = this.isMobile ? "click" : "mouseenter";
    	
    		this.$slider.on(trigger,'.aviaccordion-slide', $.proxy( this._move, this));
    		this.$slider.on('mouseleave','.aviaccordion-inner', $.proxy( this._move, this));
    		this.$win.on('debouncedresize', $.proxy( this._calcMovement, this));
    		this.$slider.on('av-prev av-next', $.proxy( this._moveTo, this));
    		
    		if(this.isMobile)
    		{
    			this.$slider.avia_swipe_trigger({next: this.$slider, prev: this.$slider, event:{prev: 'av-prev', next: 'av-next'}});
    		}
    		
    	},
    	
    	_titleHeight: function()
    	{
    		var th = 0;
    		
    		this.$titleWrap.css({'height':'auto'}).each(function()
    		{
    			var new_h = $(this).outerHeight();
    			if( new_h > th) th = new_h;
    		
    		}).css({'height':th + 2});
    		
    	},
    	
    	_calcMovement: function(event, allow_repeat)
    	{ 
    		var _self			= this,
    			containerWidth	= this.$slider.width(),
    			defaultPos		= this.$last.data('av-left'),
    			imgWidth		= this.$images.filter(':last').width() || containerWidth,
    			imgWidthPercent = Math.floor((100 / containerWidth) * imgWidth),
    			allImageWidth	= imgWidthPercent * _self.count,
    			modifier		= 3, // 10 - _self.count,
    			tempMinLeft		= 100 - imgWidthPercent,
    			minLeft 		= tempMinLeft > defaultPos / modifier ? tempMinLeft : 0,
    			oneLeft			= minLeft / (_self.count -1 ),
    			titleWidth		= imgWidth;
    		
    		
    		
    		if(allImageWidth < 110 && allow_repeat !== false)
    		{
    			//set height if necessary	
    			var slideHeight = this.$slider.height(), 
    				maxHeight 	= (slideHeight / allImageWidth) * 110 ;
    			
    			this.$slider.css({'max-height': maxHeight});
    			_self._calcMovement(event, false);
    			return;
    		}
    		
    		//backup so the minimized slides dont get too small
    		if(oneLeft < 2) minLeft = 0;
    		
			this.$slides.each(function(i)
			{
				var current = $(this), newLeft = 0, newRight = 0, defaultLeft = current.data('av-left');
					
				if( minLeft !== 0)
				{
					newLeft  = oneLeft * i;
					newRight = imgWidthPercent + newLeft - oneLeft;
				}
				else
				{
					newLeft  = defaultLeft / Math.abs(modifier);
					newRight = 100 - ((newLeft / i) * (_self.count - i));
				}
				
				if(i == 1 && _self.increaseTitle) { titleWidth = newRight + 1; } 
				
				if(_self.cssActive)
				{	
					//if we are not animating based on the css left value but on css transform we need to subtract the left value
					newLeft = newLeft - defaultLeft;
					newRight = newRight - defaultLeft;
					defaultLeft = 0;
				}
				
				current.data('av-calc-default', defaultLeft);
				current.data('av-calc-left', newLeft);
				current.data('av-calc-right', newRight);
				
			});
			
			if(_self.increaseTitle) { _self.$titles.css({width: titleWidth + "%"});} 
    	},
    	
    	_moveTo: function(event)
    	{
    		var direction 	= event.type == "av-next" ? 1 : -1,
    			nextSlide 	= this.open === false ? 0 : this.open + direction;
    			
    		if(nextSlide >= 0 && nextSlide < this.$slides.length) this._move(event, nextSlide);
    	},
    	
    	_move: function(event, direct_open)
    	{
    		var _self  = this,
    			slide  = event.currentTarget,
    			itemNo = typeof direct_open != "undefined" ? direct_open : this.$slides.index(slide);
    			
    		this.open = itemNo;
    		
    		if(_self.autoplay && typeof slide != "undefined") { clearInterval(_self.autoplay); _self.autoplay = false; }
    		
    		this.$slides.removeClass('aviaccordion-active-slide').each(function(i)
    		{
    			var current 	= $(this),
    				dataSet 	= current.data(),
    				trans_val	= i <= itemNo ? dataSet.avCalcLeft : dataSet.avCalcRight,
					transition 	= {},
					reset		= event.type == 'mouseleave' ? 1 : 0,
					active 		= itemNo === i ? _self.$titleWrap.eq(i) : false;
    			
    			if(active) current.addClass('aviaccordion-active-slide');
    				
    			if(reset)
    			{
    				trans_val = dataSet.avCalcDefault; 
    				this.open = false;
    			}
    				
				if(_self.cssActive) //do a css3 animation
				{
					//move the slides
					transition[_self.property]  = _self.transform3d ? "translate3d(" + trans_val  + "%, 0, 0)" : "translate(" + trans_val + "%,0)"; //3d or 2d transform?
					current.css(transition);
				}
				else
				{
					transition.left =  trans_val + "%";
					current.stop().animate(transition, 700, 'easeOutQuint');
				}	
    		});
    	}
    };


$.fn.aviaccordion = function( options )
{
	return this.each(function()
	{
		var active = $.data( this, 'AviaccordionSlider' );

		if(!active)
		{
			//make sure that the function doesnt get aplied a second time
			$.data( this, 'AviaccordionSlider', 1 );
			
			//create the preparations for fullscreen slider
			new $.AviaccordionSlider( options, this );
		}
	});
}

})(jQuery);
