 require([
    "jquery"
    ], function($){
    $(document).ready(function(){

        /* Footer Mobile Accordian Js Start */
        $(".mobile-footer-title.information-title").click(function(){
            $(this).toggleClass('active');
            $(".footer_wrapper .footer-col-left").stop(true, true).slideToggle('400');
        });

        $(".mobile-footer-title.signup-title").click(function(){
            $(this).toggleClass('active');
            $(".footer_wrapper .content.signup-newsletter-wrap").stop(true, true).slideToggle('400');
        });

        $(".mobile-footer-title.contact-title").click(function(){
            $(this).toggleClass('active');
            $(".footer_wrapper .footer_nav .footer_contact .left").stop(true, true).slideToggle('400');
        });
        /* Footer Mobile Accordian Js End */

        /* ------------------------------------------------------------------------------------------ */

        var counter = 0;
        var height = 0;
        var newheight;

        $(".additional-attributes-wrapper tr").each(function(){
            if(counter <= 7){
                // $(this).height();
                height = height + $(this).height();
                counter++;
                // counter++;
            }

            if(counter == 7){
              $(".additional-attributes-wrapper table").height(height+ "px");
            }

        })

        /* Mobile Menu Js Start */
        $(".menu-mobile-custom").click(function(){
            $(".navigation").toggleClass('active');
            $("#om").toggleClass('active');
            $('body').toggleClass('hide_overflow');
        });
        $(".menu_close").click(function(){
            $(".navigation").removeClass('active');
            $("#om").removeClass('active');
            $('body').removeClass('hide_overflow');
        });
        /* Mobile Menu Js End */

        /*My account page title up start*/

        $("body.account .page-title-wrapper").prependTo(".columns .main");

        /*My account page title up end*/

        /* ------------------------------------------------------------------------------------------ */

        /******************************* filters ********************************/
        if ($(window).width() > 1024) {
            $('.filter_select').click(function(event){
                $('.amasty-catalog-topnav').slideToggle(400);
                event.stopImmediatePropagation();
                equalheight('.filter-options .filter-options-item');
             });

            $("body").click(function(){
                $('.amasty-catalog-topnav').slideUp(400);
            });

            $('.amasty-catalog-topnav').click(function(event){
                event.stopImmediatePropagation();
            });
        }
        else{
            $(".filter_select").click(function(event){
                $('.amasty-catalog-topnav').toggleClass('active');
                $('.filter_overlay').toggleClass('active');
                $('body').toggleClass('hide_overflow');
            });
            $(".filter_close").click(function(){
                $('.amasty-catalog-topnav').removeClass('active');
                $('.filter_overlay').removeClass('active');
                $('body').removeClass('hide_overflow');
                $('.toolbar_wrap').removeClass('active');
            });
            $('.mob-sort-title').click(function(){
                $('.sort-by-wrap').toggleClass('active');
                $('body').toggleClass('hide_overflow');
            });
        }

        /**************************** faq *********************************/

        if ($(window).width() >= 768) {
            $('.tab_link li:first a').addClass('active');
            $(document).on('click','.tab_link li a', function(event) {
            event.preventDefault();
            $(this).addClass('active').parents('.tab_link li').siblings().find('.active').removeClass('active');
            var target = "#" + this.getAttribute('data-target');
            $('html, body').animate({
                scrollTop: $(target).offset().top
            }, 2000);
            });
        }
        else{
            var first_link = $('.tab_link_wrap li:first a').html();
            $('.mob_tab_active').html(first_link);
            $('.tab_link li:first a').addClass('active');
            $('.tab_content_wrap .tab_content:first').addClass('active');

            $('.mob_tab_active').click(function(){
                $(this).toggleClass('active');
                $('.tab_link_wrap').slideToggle();
            });

            $(document).on('click','.tab_link li a', function(event) {
            event.preventDefault();
            $(this).addClass('active').parents('.tab_link li').siblings().find('.active').removeClass('active');
            var get_active_link = $(this).html();
            var target = "#" + this.getAttribute('data-target');
            $(".mob_tab_active").html(get_active_link);
            $(target).addClass('active').siblings().removeClass('active');
            });

        }
        /**************************************************************/
        var header_height = jQuery('.page-header').height() + jQuery('.top-container').height();
        jQuery(".common_overlay").css("top",header_height+"px");
        //jQuery(".menu-nav-overlay").css("top",header_height+"px");
        $('.minicart-wrapper .showcart').click(function(){
            $('.minicart_overlay').toggle();
            $('.customer-menu').hide();
        });
        $('.minicart_overlay').on('click',function(){
            $(this).hide();
        });
        $("body").click(function(){
            $('.minicart_overlay').hide();
        });


        $('.menu > ul > li').on('blur',function(){
            $(".menu-nav-overlay").hide();
        });

        /******************* checkout steps *************************/
        $(document).on('click','.login-title',function(){
            $(".opc-progress-bar li:nth-child(1) span").trigger("click");
        });
        $(document).on('click','.shipping-title',function(){
            $(".opc-progress-bar li:nth-child(2) span").trigger("click");
        });
        $(document).on('click','.payment_title',function(){
            $(".opc-progress-bar li:nth-child(3) span").trigger("click");
        });
        /*************************** category page ************************************/
        equalheight('.product-items .item .product-item-info');
        /******************************minicart************************************/
        $(".customer-welcome span.customer-name, .customer-welcome").click(function(){
            $('.customer-menu').slideToggle();
        });
        $(".customer-welcome span.customer-name, .customer-welcome, .customer-menu").click(function(event){
                event.stopImmediatePropagation();
        });
        $("body").click(function(){
            $('.customer-menu').slideUp();
        });
        /************************************************************************/
        var get_active_acc = $('.block-collapsible-nav .block-collapsible-nav-content li.current').html();
        $(document).on('click','.block-collapsible-nav .block-collapsible-nav-title', function() {
            $(".block-collapsible-nav-title").html(get_active_acc);
        });
        $(".block-collapsible-nav-title").html(get_active_acc);



        $(".button_overlay").on('click',function(){
            // console.log("hi");
            if (!($(".field.search").hasClass("focused"))) {
                // console.log("ok");
                $(".field.search").addClass("focused");
                /*$("body").addClass("overflow_none");*/
                $("input#search").focus();
            }else {
                // console.log("bae");
                $(".field.search").removeClass("focused");
                /*$("body").removeClass("overflow_none");*/
                $("input#search").blur();
            }
        })

        $("input#search").change(function(){
            // console.log("change");
            if(($('input#search').val())){
                $(this).addClass("hasvalue");

            }
            if(!($('input#search').val())){
                $(this).removeClass("hasvalue");
                 $(".search .close, .search_btn").removeClass("keyShow");
            }
        })

        $("input#search").keyup(function(){
            // console.log("keyup");
            var words = $('input#search').val();

            if(($('input#search').val())){
                if((words.length >= 3)){
                    $(".search_overlay").show();
                    $("body").addClass("overflow_none");
                }else {
                    $(".search_overlay").hide();
                    $("body").removeClass("overflow_none");
                }
                $(".search .close").addClass("keyShow");
                $(".search_btn").addClass("keyShow");


            }
            if(!($('input#search').val())){

                 $(".search_overlay").hide();
                $("body").removeClass("overflow_none");
                $(".search .close").removeClass("keyShow");


                    $(".search_btn").removeClass("keyShow");

            }
        });

        $(".block-search span.close").on('click',function(){
            $('input#search').val('');
            $("#search_autocomplete").hide();
            $('input#search').focus()
            $('input#search').removeClass("hasvalue");
            $(this).removeClass("keyShow");
            $(".search_overlay").hide();
            $("body").removeClass("overflow_none");
        })

        $(".search_btn").on('click',function(){
            $(".action.search").trigger('click');
        });


        $("input#search").focus(function(){
            // console.log("focus");
            // $(".search_overlay").show();
            if($(window).width() >= 521) {
                $(".button_overlay").hide();
            }

        });
        $("input#search").blur(function(){
            // console.log("blur");
            if(!($('input#search').val())){
                if($(window).width() >= 521) {
                // $(this).attr("value","");
                    //$("input#search").blur();
                    $(".field.search").removeClass("focused");
                    $("body").removeClass("overflow_none");
                }
                if($(window).width() >= 521 || !($(".field.search").hasClass("focused"))) {
                    $(".search_overlay").hide();
                }

                $(".button_overlay").show();
            }


        });

         $(window).blur(function(){
            $("input#search").blur();
        });

        $(document).on("click", "#search-view-all" , function() {
            // $('#search_mini_form').submit();
            $(".action.search").trigger('click');
        });

        $(".mob-filter-title, .filter_select, .mob-sort-title").on('click',function(){
            $(".header .block-search").addClass("indexz");
        });

        $(".filter_close").on('click',function(){
            $(".header .block-search").removeClass("indexz");
        });

        jQuery('.track_package').click(function(){
            var has_class_active = jQuery(this).hasClass("active");
            if(has_class_active == true){
                jQuery('.tracking_table_wrap').stop(true, true).slideUp('400');
                jQuery('.track_package').removeClass("active");
            }
            else{
                jQuery('.tracking_table_wrap').stop(true, true).slideUp('400');
                jQuery('.track_package').removeClass("active");
                jQuery(this).next('.tracking_table_wrap').stop(true, true).slideDown('400');
                jQuery(this).addClass("active");
            }

        });
        jQuery('.track_pro_link').click(function(){
                jQuery('.track_pro_details').stop(true, true).slideToggle('400');
                jQuery('.track_pro_link').removeClass("active");
        });

        jQuery(".track_head").click(function() {
             jQuery('html, body').animate({
                 scrollTop: jQuery(".tracking_wrap").offset().top
             }, 2000);
         });

    });
});

 /******************************* slick **********************************/
 require(['jquery','slick'],function($){
    $(document).ready(function(){
            $('.related_products').slick({
                dots: false,
                infinite: false,
                speed: 300,
                slidesToShow:3,
                slidesToScroll: 1,
                responsive: [
                    {
                        breakpoint: 768,
                        settings: {
                        slidesToShow:2,
                        }
                    },
                    {
                        breakpoint: 480,
                        settings: {
                        slidesToShow:1,
                        }
                    }
                ]
            });

    });
});
/******************************** window.resize ******************************/
require([
   "jquery"
   ], function($){
   $(window).resize(function(){
       if ($(window).width() > 1024) {
           equalheight('.filter-options .filter-options-item');
       }
       /*************************** category page ************************************/
       equalheight('.product-items .item .product-item-info');
   });

});
/**************************************************/
require([
   "jquery"
   ], function($){
       jQuery(document).ready(function(){
       /* Show the HTML page only after the js and css are completely loaded */
         delayShow();
       });

       function delayShow() {
         var secs = 1000;
         setTimeout('jQuery(".flex-direction-nav").css("opacity","1");', secs);
       }

});
/*************************** equal height function ***********************************/
equalheight = function(container){
   var currentTallest = 0,
   currentRowStart = 0,
   rowDivs = new Array(),
   $el,
   topPosition = 0;
   jQuery(container).each(function() {

       $el = jQuery(this);
       jQuery($el).height('auto')
       topPostion = $el.position().top;

       if (currentRowStart != topPostion) {
       for (currentDiv = 0 ; currentDiv < rowDivs.length ; currentDiv++) {
       rowDivs[currentDiv].height(currentTallest);
       }
       rowDivs.length = 0; // empty the array
       currentRowStart = topPostion;
       currentTallest = $el.height();
       rowDivs.push($el);
       } else {
       rowDivs.push($el);
       currentTallest = (currentTallest < $el.height()) ? ($el.height()) : (currentTallest);
       }
       for (currentDiv = 0 ; currentDiv < rowDivs.length ; currentDiv++) {
       rowDivs[currentDiv].height(currentTallest);
       }
   });
}
