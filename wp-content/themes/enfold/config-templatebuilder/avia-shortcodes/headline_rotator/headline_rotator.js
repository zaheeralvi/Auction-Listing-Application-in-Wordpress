(function($)
{ 
	"use strict";
	
	// -------------------------------------------------------------------------------------------
	// Avia Text Rotator
	// 
	// accordion slider script
	// -------------------------------------------------------------------------------------------

	$.AviaTextRotator  =  function(options, slider)
	{
	    this.$win	  	= $( window );
	    this.$slider  	= $( slider );
	    this.$inner	  	= this.$slider.find('.av-rotator-text');
	    this.$slides	= this.$inner.find('.av-rotator-text-single');
	    this.$current   = this.$slides.eq(0);
	    this.open		= 0;
	    this.count		= this.$slides.length;
	    
	    if($.avia_utilities.supported.transition === undefined)
		{
			$.avia_utilities.supported.transition = $.avia_utilities.supports('transition');
		}
		
		this.browserPrefix 	= $.avia_utilities.supported.transition;
	    this.cssActive 		= this.browserPrefix !== false ? true : false;
		this.property		= this.browserPrefix + 'transform',
		
		//this.cssActive    = false; //testing no css3 browser
		
	    this._init( options );
	}

  	$.AviaTextRotator.prototype =
    {
    	_init: function( options )
    	{
    		var _self = this;
    		
    		if(this.count <= 1) return;
    		
    		_self.options = $.extend({}, options, this.$slider.data());
			_self.$inner.addClass('av-rotation-active');
			//if(_self.options.fixwidth == 1) this.$inner.width(this.$current.width());
			_self._autoplay();
			
			if(_self.options.animation == "typewriter")
			{
				_self.$slider.addClass('av-caret av-blinking-caret');
			}
			
			
    	},
    	
    	_autoplay: function()
    	{
    		var _self = this;
    		
			_self.autoplay = setTimeout(function()
			{
				_self.open = _self.open === false ? 0 : _self.open + 1;
				if(_self.open >= _self.count) _self.open = 0;
				
				if(_self.options.animation != "typewriter")
				{
					_self._move({}, _self.open);
					_self._autoplay();
				}
				else
				{
					_self._typewriter();
				}
				
				
			}, _self.options.interval * 1000)
    	},
    	
    	_typewriter: function(event)
    	{
	    	var _self = this;
	    	
	    	//mark text
	    	_self.$current.css('background-color', _self.$current.css('color') );
	    	_self.$slider.removeClass('av-caret av-blinking-caret').addClass('av-marked-text');  
		    
		    
		    //store and hide text
		    setTimeout(function()
	    	{ 
		    	_self.$slider.addClass('av-caret av-blinking-caret').removeClass('av-marked-text');  
		    	_self.$current.data('av_typewriter_text', _self.$current.html());
		    	_self.$current.css('background-color', 'transparent');
		    	_self.$current.html("");
		    
		    }, 800 );
	    	
	    	
	    	//start typing new text
	    	setTimeout(function()
	    	{ 
		    	_self.$slider.removeClass('av-blinking-caret');  
		    	_self.$next = _self.$slides.eq(_self.open);
		    	var content = _self.$next.data('av_typewriter_text') || _self.$next.html();
		    	_self.$current.css({display:'none'});
		    	_self.$next.css({display:'inline'});
		    	_self.$next.html("");
		    	
		    	var i = 0;
				var speed = 50; /* The speed/duration of the effect in milliseconds */
				
				function typeWriter() {
					
				  if (i < content.length) {
				    _self.$next[0].innerHTML += content.charAt(i);
				    i++;
				    setTimeout(typeWriter, speed + Math.floor(Math.random() * 100 ) );
				  }
				  else
				  {
					  _self.$slider.addClass('av-caret av-blinking-caret'); 
					  _self.$current = _self.$slides.eq(_self.open);
					  _self._autoplay();
				  }
				  
				}
				
				typeWriter();
		    	
	    	}, 1500 );
	    },
    	 	
    	_move: function(event)
    	{
	    	var _self 		= this, 
	    		modifier 	= 30 * _self.options.animation, 
	    		fade_out 	= {opacity:0}, 
	    		fade_start  = {display:'inline-block', opacity:0},
	    		fade_in		= {opacity:1};
	    		
    		this.$next = _self.$slides.eq(this.open);
    		
    		if(this.cssActive)
    		{
	    		fade_out[_self.property] 	= "translate(0px," + modifier +"px)";
	    		fade_start[_self.property] 	= "translate(0px," + (modifier * -1) +"px)";
	    		fade_in[_self.property] 	= "translate(0px,0px)";
    		}
    		else
    		{
	    		fade_out['top'] 	= modifier;
	    		fade_start['top'] 	= (modifier * -1);
	    		fade_in['top'] 		= 0;
    		}
    		
    		
    		_self.$current.avia_animate(fade_out, function()
    		{
	    		_self.$current.css({display:'none'});
	    		_self.$next.css(fade_start).avia_animate(fade_in, function()
	    		{
		    		_self.$current = _self.$slides.eq(_self.open);
	    		});
    		});
    	}
    };


	$.fn.avia_textrotator = function( options )
	{
		return this.each(function()
		{
			var active = $.data( this, 'AviaTextRotator' );
	
			if(!active)
			{
				//make sure that the function doesnt get aplied a second time
				$.data( this, 'AviaTextRotator', 1 );
				
				//create the preparations for fullscreen slider
				new $.AviaTextRotator( options, this );
			}
		});
	}
	
}(jQuery));