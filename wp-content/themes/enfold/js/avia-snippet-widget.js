(function($) {

    "use strict";

    $(document).ready(function() {

        $('.avia_auto_toc').each(function(){

            var $toc_section = $(this).attr('id');
            var $levels = 'h1';
            var $levelslist = new Array();
            var $excludeclass = '';

            var $toc_container = $(this).find('.avia-toc-container');

            if ($toc_container.length){
                var $levels_attr = $toc_container.attr('data-level');
                var $excludeclass_attr = $toc_container.attr('data-exclude');

                if(typeof $levels_attr !== undefined) {
                    $levels = $levels_attr;
                }
                if(typeof $excludeclass_attr !== undefined) {
                    $excludeclass = $excludeclass_attr;
                }
            }

            $levelslist = $levels.split(',');

            $('.entry-content-wrapper').find($levels).each( function() {

                var $h_id = $(this).attr('id');
                var $tagname = $(this).prop('tagName').toLowerCase();
                var $txt = $(this).text();
                var $pos = $levelslist.indexOf($tagname);
                var $extraclass = '';

                if ($h_id == undefined){
                    var $new_id = av_pretty_url($txt);
                    $(this).attr('id',$new_id);
                    $h_id = $new_id;
                }

                if ( ! $(this).hasClass('av-no-toc') && ! $(this).hasClass($excludeclass) && ! $(this).parent().hasClass($excludeclass)){
                    var $list_tag = '<a href="#' + $h_id + '" class="avia-toc-link avia-toc-level-' + $pos + '"><span>' + $txt + '</span></a>';
                }

                $toc_container.append($list_tag);

            });


            // Smooth Scrolling

            $(".avia-toc-smoothscroll .avia-toc-link").on('click',function(e){
                e.preventDefault();
                var $target = $(this).attr('href');

                var $offset = 50;

                // calculate offset if there is a sticky header
                var $sticky_header = $('.html_header_top.html_header_sticky #header');

                if ( $sticky_header.length ) {
                    $offset =  $sticky_header.outerHeight() + 50;
                }

                $('html,body').animate({scrollTop:$($target).offset().top - $offset})
            });


        });

    });


    function av_pretty_url(text) {

        return text.toLowerCase()
            .replace(/[^a-z0-9]+/g, "-")
            .replace(/^-+|-+$/g, "-")
            .replace(/^-+|-+$/g, '');

    }

})( jQuery );
