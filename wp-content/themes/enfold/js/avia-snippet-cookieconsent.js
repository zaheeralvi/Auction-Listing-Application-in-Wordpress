(function($) {

    "use strict";

    $(document).ready(function() {
	
	
        if (! aviaGetCookie('aviaCookieConsent')){
            $('.avia-cookie-consent').removeClass('cookiebar-hidden');
        }

		//close btn
        $('.avia-cookie-close-bar').on('click', function(e) {

            var cookieContents = $(this).attr('data-contents');
            aviaSetCookie('aviaCookieConsent',cookieContents,60);

            $('.avia-cookie-consent').addClass('cookiebar-hidden');
            
            e.preventDefault();
        });
        
        //info btn
        if($.avia_utilities.av_popup)
        {
	        var new_options = {
				type:'inline',
				midClick: true, // Allow opening popup on middle mouse click. Always set it to true if you don't provide alternative source in href.
				items:{
					src: '#av-consent-extra-info',
					type:'inline',	
				}
			};
			
			new_options = $.extend({}, $.avia_utilities.av_popup, new_options);
	        $('.avia-cookie-info-btn').magnificPopup(new_options);
		}
		else
		{
			$('.avia-cookie-info-btn').on('click', function(e) {
            alert('Default Lightbox must be activated for this feature to work');
            e.preventDefault();
        });
		}

        function aviaSetCookie(CookieName,CookieValue,CookieDays) {
            if (CookieDays) {
                var date = new Date();
                date.setTime(date.getTime()+(CookieDays*24*60*60*1000));
                var expires = "; expires="+date.toGMTString();
            }
            else var expires = "";
            document.cookie = CookieName+"="+CookieValue+expires+"; path=/";
        }


        function aviaGetCookie(CookieName) {
            var docCookiesStr = CookieName + "=";
            var docCookiesArr = document.cookie.split(';');

            for(var i=0; i < docCookiesArr.length; i++) {
                var thisCookie = docCookiesArr[i];

                while (thisCookie.charAt(0)==' ') {
                    thisCookie = thisCookie.substring(1,thisCookie.length);
                }
                if (thisCookie.indexOf(docCookiesStr) == 0) {
                    var cookieContents = $('.avia-cookie-close-bar').attr('data-contents');
                    var savedContents = thisCookie.substring(docCookiesStr.length,thisCookie.length);
                    if (savedContents == cookieContents) {
                        return savedContents;
                    }
                }
            }
            return null;
        }

    });

})( jQuery );
