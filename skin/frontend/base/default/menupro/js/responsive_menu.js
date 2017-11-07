var mst = jQuery.noConflict();
mst(document).ready(function($) {
    //$('ul.menu-creator-pro').clone(false).removeAttr("class").addClass("menu-creator-pro-responsive").insertAfter($('ul.menu-creator-pro'));
    $('.menu-creator-pro-responsive .icon-angle-right').removeClass("icon-angle-right").addClass("icon-angle-down");
    var current_height = $('.menu-creator-pro-responsive').height();
    $('.menu-creator-pro-responsive').height(0);
    $('.menu-creator-pro-responsive > .switcher').click(function(){
        current_height = $('.menu-creator-pro-responsive').height();
        var max_height = $('.menu-creator-pro-responsive').css("height","auto").height();
        $('.menu-creator-pro-responsive').height(current_height);
        if($(this).parent().hasClass("active")){
            $(this).parent().removeClass("active").animate({height:"0px"});
        }else{
            $(this).parent().addClass("active").animate({height:max_height});
        }
        //$('.menu-creator-pro-responsive > li:not(.switcher').slideToggle(); 
        return false;
    });
    $('.menu-creator-pro-responsive > li:not(.parent) .icon-angle-down').remove();
    $('.menu-creator-pro-responsive > li .icon-angle-down').addClass("icon_toggle")
    $('.menu-creator-pro-responsive > li .icon_toggle').click(function(){
        var max_height = $('.menu-creator-pro-responsive').css("height","auto");
        if($(this).hasClass("icon-angle-down")){
            $(this).removeClass('icon-angle-down').addClass('icon-angle-up').next().slideDown(500);
        }else{
            $(this).removeClass('icon-angle-up').addClass('icon-angle-down').next().slideUp(500);
        };
    });
    $('.menu-creator-pro .icon-angle-down').click(function(){
       if(!$(this).parent().hasClass("item_cr")){
           $(this).parent().nextAll().children(".grid-container3").css('opacity',"0"); 
           $(this).next().css({'transform':'scale(1, 1)',"opacity":'1',"display":'block'});
           $(this).parent().addClass("item_cr");
       }else{
           $(this).parent().removeClass("item_cr");
           $(this).next().css({'transform':'scale(1, 0)',"opacity":'0',"display":'none'});
       }
    });
});