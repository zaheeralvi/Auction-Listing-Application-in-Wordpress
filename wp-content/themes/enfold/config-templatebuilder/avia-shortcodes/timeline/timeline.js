// -------------------------------------------------------------------------------------------
// Avia Timeline
// -------------------------------------------------------------------------------------------


(function($) {
    "use strict";

    $(window).on('load', function (e) {
        if ($.AviaSlider) {
            $('.avia-timeline-container').avia_sc_timeline();
        }
    });


    $.fn.avia_sc_timeline = function (options) {

        return this.each(function () {

            var container = this,
                timeline_id = '#' + $(this).attr('id'),
                timeline = $(timeline_id),
                methods;

            methods =
                {
                    // make sure all milestones have the same height in horizontal timelines
                    matchHeights: function(){

                        //date
						this.setMinHeight( $(timeline_id + ' .av-milestone-placement-top .av-milestone-date'), true );

						//content
						this.setMinHeight( $(timeline_id + ' .av-milestone-placement-bottom .av-milestone-content-wrap'), true );

                        //contentbox
                        this.setMinHeight( $(timeline_id + ' .av-milestone-placement-bottom.avia-timeline-boxshadow .av-milestone-contentbox'), false );
                        this.setMinHeight( $(timeline_id + ' .av-milestone-placement-top.avia-timeline-boxshadow .av-milestone-contentbox'), false );

						//alternate
						this.setMinHeight( $(timeline_id + ' .avia-timeline-horizontal.av-milestone-placement-alternate li >:first-child'), true );

                    },

                    setMinHeight: function( els, setNav )
                    {

	                    if(els.length < 2) return;

	                    var elsHeights = new Array();
	                    els.css('min-height','0').each(function(i)
	                    {
                            var current = $(this);
                            var currentHeight = current.outerHeight(true);
                            elsHeights.push(currentHeight);
	                    });

	                    var largest = Math.max.apply(null, elsHeights);
                        els.css('min-height', largest);

	                    //set nav position
                        if (setNav) {
                            var $firstElement = els.first(),
                                $parent = $firstElement.closest('.avia-timeline-container'),
                                $pos = $firstElement.height();

                            $parent.find('.av-timeline-nav').css('top',$pos);
                        }

                    },
                    createCarousel : function(e){

                        var self = this,
                            slider = $(timeline_id + '.avia-slideshow-carousel'),
                            slides_num = 3,
                            slides_num_small = 1;

                        if (timeline.attr('avia-data-slides')) {
                            slides_num = parseInt(timeline.attr('avia-data-slides'));
                        }

                        if (slides_num >= 2) {
                            slides_num_small = 2;
                        }

                        var sliderOptions = {
                            carousel : 'yes',
                            keep_pading : true,
                            carouselSlidesToShow : slides_num,
                            carouselSlidesToScroll : 3,
                            carouselResponsive : [
                                {
                                    breakpoint: 989,
                                    settings: {
                                        carouselSlidesToShow: slides_num_small,
                                        carouselSlidesToScroll: slides_num_small,
                                    }
                                },
                                {
                                    breakpoint: 767,
                                    settings: {
                                        carouselSlidesToShow: 1,
                                        carouselSlidesToScroll: 1,
                                    }
                                }
                            ],
                        }

                        slider.aviaSlider(sliderOptions);

                        slider.on('_kickOff',function(){
                            self.matchHeights();
                        });

                        $(window).on( 'resize', function() {
                            self.matchHeights();
                        });



                    },
                    layoutHelpers : function(e){

                        $(timeline_id + ' .avia-timeline-vertical li').each(function(index, element){

                            var $length = $(this).parents('ul').find('li').length;

                            var $icon_wrap = $(this).find('.av-milestone-icon-wrap');
                            var $icon_wrap_height = $icon_wrap.outerHeight(true);
                            var $icon_wrap_height_half = parseInt($icon_wrap_height/2);

                            if (index === ($length - 1)) {
                                $icon_wrap.css({
                                    'height' : $icon_wrap_height_half,
                                });
                            }
                            else {
                                $icon_wrap.css({
                                    'height' : $icon_wrap_height,
                                });
                            }

                        });

                    },
                    fireAnimations : function(e) {

                        if ( $(timeline_id + ' > ul').hasClass('avia-timeline-vertical') ) {
                            var milestone = timeline.find('.av-milestone');
                            timeline.on('avia_start_animation', function() {
                                milestone.each(function(i)
                                {
                                    var element = $(this);
                                    setTimeout(function(){ element.addClass('avia_start_animation') }, (i * 350));
                                });
                            });
                        }
                    }
                };
            methods.createCarousel();
            methods.layoutHelpers();
            methods.fireAnimations();
            methods.matchHeights();
        });
    }

})(jQuery);