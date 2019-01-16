// -------------------------------------------------------------------------------------------
// Message Box
// -------------------------------------------------------------------------------------------

(function($)
{
    "use strict";

    $.fn.avia_sc_messagebox = function (options) {

        "use strict";

        return this.each(function () {

            var container = $(this),
                close_btn = container.find('.av_message_close'),
                mbox_ID = container.attr('id'),

                aviaSetCookie = function(CookieName,CookieValue,CookieDays) {
                    if (CookieDays) {
                        var date = new Date();
                        date.setTime(date.getTime()+(CookieDays*24*60*60*1000));
                        var expires = "; expires="+date.toGMTString();
                    }
                    else var expires = "";
                    document.cookie = CookieName+"="+CookieValue+expires+"; path=/";
                },

                aviaGetCookie = function(CookieName) {
                    var docCookiesStr = CookieName + "=";
                    var docCookiesArr = document.cookie.split(';');
                    for(var i=0; i < docCookiesArr.length; i++) {
                        var thisCookie = docCookiesArr[i];
                        while (thisCookie.charAt(0)==' ') {
                            thisCookie = thisCookie.substring(1,thisCookie.length);
                        }
                        if (thisCookie.indexOf(docCookiesStr) == 0) {
                            var cookieContents = container.attr('data-contents');
                            var savedContents = thisCookie.substring(docCookiesStr.length,thisCookie.length);
                            if (savedContents == cookieContents) {
                                return savedContents;
                            }
                        }
                    }
                    return null;
                };

            // check if cookie is set and display message box
            if ( ! aviaGetCookie(mbox_ID)){
                container.removeClass('messagebox-hidden');
            }

            // set cookie when button clicked
            close_btn.on('click', function() {
                var cookieContents = container.attr('data-contents');

                // set session cookie
                if ( container.hasClass('messagebox-session_cookie') ) {
                    var cookieLifetime = "";
                }
                // set cookie with defined lifetime
                else if ( container.hasClass('messagebox-custom_cookie') ) {
                    var cookieLifetime = parseInt(container.attr('data-cookielifetime'));
                }

                aviaSetCookie(mbox_ID,cookieContents,cookieLifetime);
                container.addClass('messagebox-hidden');
            });

        });

    };


    // activate message box
    $('.avia_message_box').avia_sc_messagebox();


}(jQuery));

