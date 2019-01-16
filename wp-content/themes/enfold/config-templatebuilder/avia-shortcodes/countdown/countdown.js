// -------------------------------------------------------------------------------------------
// 
// AVIA Countdown
// 
// -------------------------------------------------------------------------------------------
(function($)
{ 
	"use strict";
	
	var _units	= ['weeks','days','hours','minutes','seconds'],
		_second = 1000,
		_minute = _second * 60,
		_hour 	= _minute * 60,
		_day 	= _hour * 24,
		_week	= _day * 7,	
		ticker	= function(_self)
		{
			var _time		= {},
				_now 		= new Date(),
				_timestamp  = _self.end - _now;
			
			if(_timestamp <= 0)
			{
				clearInterval(_self.countdown);
				return;
			}	
			
			_self.time.weeks   	= Math.floor( _timestamp / _week);
			_self.time.days 	= Math.floor((_timestamp % _week) / _day);
			_self.time.hours	= Math.floor((_timestamp % _day) / _hour); 
			_self.time.minutes 	= Math.floor((_timestamp % _hour) / _minute); 
			_self.time.seconds 	= Math.floor((_timestamp % _minute) / _second); 
			
			switch(_self.data.maximum)
			{
				case 1: _self.time.seconds 	= Math.floor(_timestamp / _second); break;
				case 2: _self.time.minutes 	= Math.floor(_timestamp / _minute); break;
				case 3: _self.time.hours	= Math.floor(_timestamp / _hour); 	break;
				case 4: _self.time.days 	= Math.floor(_timestamp / _day); 	break;
			}
			
			for (var i in _self.time)
			{	
				if(typeof _self.update[i] == "object")
				{
					if(_self.firstrun || _self.oldtime[i] != _self.time[i])
					{
						var labelkey = ( _self.time[i] === 1 ) ? "single" : "multi"; 
					
						_self.update[i].time_container.text(_self.time[i]);
						_self.update[i].label_container.text(_self.update[i][labelkey]);
					}
				}
			}
			
			//show ticker
			if(_self.firstrun) _self.container.addClass('av-countdown-active');
			
			_self.oldtime 	= $.extend( {}, _self.time );
			_self.firstrun	= false;
		};
		
	
	$.fn.aviaCountdown = function( options )
	{	
		if(!this.length) return; 

		return this.each(function()
		{
			var _self 			= {};
			_self.update		= {};
			_self.time			= {};			
			_self.oldtime		= {};			
			_self.firstrun		= true;			
			
			_self.container		= $(this);
			_self.data			= _self.container.data();
			_self.end			= new Date(_self.data.year, _self.data.month, _self.data.day, _self.data.hour, _self.data.minute );
			
			for (var i in _units)
			{
				_self.update[_units[i]] = {
					time_container:  _self.container.find('.av-countdown-' + _units[i] + ' .av-countdown-time'),
					label_container: _self.container.find('.av-countdown-' + _units[i] + ' .av-countdown-time-label')
				};
				
				if(_self.update[_units[i]].label_container.length)
				{
					_self.update[_units[i]].single = _self.update[_units[i]].label_container.data('label');
					_self.update[_units[i]].multi  = _self.update[_units[i]].label_container.data('label-multi');
				}
			}
			
			ticker(_self);
			_self.countdown 	= setInterval(function(){ ticker(_self); }, 1000);

			
		});
	};
	
	
	
}(jQuery));