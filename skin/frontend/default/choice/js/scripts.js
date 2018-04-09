jQuery(document).ready(function(){

/*********************************************************************************************************** Superfish Menu *********************************************************************/
setTimeout(function() {
jQuery("#search").css("padding-left","9px");       
},2000);
/* toggle nav */
jQuery("#menu-icon").on("click", function(){
jQuery(".sf-menu").slideToggle();
jQuery(this).toggleClass("active");
});
/* toggle nav */
jQuery(".icon_header li").on("click", function(){
if(jQuery(this).hasClass("search")) {
jQuery("#search_box_container").slideToggle();
jQuery(".nav-container").hide();
jQuery("#cart_box_container").hide();
jQuery("#accont_box_container").hide();	
}
if(jQuery(this).hasClass("cart")) {
var html = jQuery(".block-cart-header").html();
if(html!="") {
      	jQuery("#cart_box_container").html(html).slideToggle();
jQuery("#cart_box_container .cart-content").show();
}
jQuery("#accont_box_container").hide();	 		   
jQuery(".nav-container").hide();
jQuery("#search_box_container").hide();
}

if(jQuery(this).hasClass("menu")) {

jQuery(".nav-container").slideToggle();
jQuery("#search_box_container").hide();
jQuery("#cart_box_container").hide();
jQuery("#accont_box_container").hide();	
}

if(jQuery(this).hasClass("my-account")) {

jQuery("#accont_box_container").slideToggle();		   
jQuery(".nav-container").hide();
jQuery("#search_box_container").hide();
jQuery("#cart_box_container").hide();

}			

});

if (jQuery('.container').width() < 724) {
jQuery('.sf-menu').removeClass('sf-js-enabled').find('li.parent').append('<strong></strong>');
jQuery('.sf-menu li.parent strong').on("click", function(){
if (jQuery(this).attr('class') == 'opened') { jQuery(this).removeClass().parent('li.parent').find('> ul').slideToggle(); } 
else {
jQuery(this).addClass('opened').parent('li.parent').find('> ul').slideToggle();
}
});
};

/*********************************************************************************************************** Cart Truncated *********************************************************************/

if (jQuery('.container').width() < 724) {
jQuery('.truncated span').click(function(){
jQuery(this).parent().find('.truncated_full_value').stop().slideToggle();
}
)
}
else {
jQuery('.truncated span').hover(function(){
jQuery(this).parent().find('.truncated_full_value').stop().slideToggle();
}
)
};

/*********************************************************************************************************** Product View Accordion *********************************************************************/
if (jQuery('.container').width() < 724) {
jQuery.fn.slideFadeToggle = function(speed, easing, callback) {
return this.animate({opacity: 'toggle', height: 'toggle'}, speed, easing, callback);  
};
jQuery('.box-collateral').not('.box-up-sell').find('h2').append('<span class="toggle"></span>');
jQuery('.form-add').find('.box-collateral-content').css({'display':'block'}).parents('.form-add').find('> h2 > span').addClass('opened');

jQuery('.box-collateral > h2').click(function(){
OpenedClass = jQuery(this).find('> span').attr('class');
if (OpenedClass == 'toggle opened') { jQuery(this).find('> span').removeClass('opened'); }
else { jQuery(this).find('> span').addClass('opened'); }
jQuery(this).parents('.box-collateral').find(' > .box-collateral-content').slideFadeToggle()
});
};
/*********************************************************************************************************** Sidebar Accordion *********************************************************************/
if (jQuery('.container').width() < 724) {
jQuery('.sidebar .block .block-title').append('<span class="toggle"></span>');
jQuery('.sidebar .block .block-title').on("click", function(){
if (jQuery(this).find('> span').attr('class') == 'toggle opened') { jQuery(this).find('> span').removeClass('opened').parents('.block').find('.block-content').slideToggle(); }
else {
jQuery(this).find('> span').addClass('opened').parents('.block').find('.block-content').slideToggle();
}
});
};

/*********************************************************************************************************** Footer Accordion *********************************************************************/
if (jQuery('.container').width() < 769) {
jQuery('.footer .footer-col > h4').append('<span class="toggle"></span>');
jQuery('.footer h4').on("click", function(){
if (jQuery(this).find('span').attr('class') == 'toggle opened') { jQuery(this).find('span').removeClass('opened').parents('.footer-col').find('.footer-col-content').slideToggle(); }
else {
jQuery(this).find('span').addClass('opened').parents('.footer-col').find('.footer-col-content').slideToggle();
}
});
};

/*********************************************************************************************************** Header Buttons *********************************************************************/
if (jQuery('.container').width() > 800) {
jQuery('.header-button ul').css({'display':'none'});
jQuery('.header-button').not('.top-login').hover(
function(){
/*ListHeight = jQuery(this).find('ul').height();*/
jQuery(this).find('a').css({'background-color': 'transparent'}).parent().find('ul').toggle()/*css({'display':'block','height':'0'}).stop().animate({height:ListHeight, opacity: 1}, 200)*/
jQuery(this).find('a').addClass('active');
},
function(){jQuery(this).find('a').css({'background-color': 'transparent'}).parent().find('ul').toggle()/*stop().animate({height:'0', opacity: 0}, 200, 
function() {
jQuery(this).css({'display':'none', 'height':ListHeight})
});*/
jQuery(this).find('a').removeClass('active');
}
);
}
else {
jQuery('.header-button').not('.top-login').click(
function(){
Ulheight = jQuery(this).find('ul').css('display');
if (Ulheight == 'none') {
jQuery('.header-button').find('ul').hide(0);
jQuery(this).find('ul').show(0);
jQuery(this).find('a').addClass('active');
}
else {
jQuery(this).find('ul').hide(0);
jQuery(this).find('a').removeClass('active');
}
}
)
};

jQuery('.products-grid .add-to-links li > a ').tooltip('hide')
qwe = jQuery('.lang-list ul li span').text();
jQuery('.lang-list > a').text(qwe);

/*********************************************************************************************************** Header Cart *********************************************************************/
jQuery('.block-cart-header .cart-content').hide();
if (jQuery('.container').width() < 800) {
jQuery('.block-cart-header .summary, .block-cart-header .cart-content, .block-cart-header .empty').click(function(){
jQuery('.block-cart-header .cart-content').stop(true, true).slideToggle(300);
}
)
}
else {
jQuery('.block-cart-header .summary, .block-cart-header .cart-content, .block-cart-header .empty').hover(
function(){jQuery('.block-cart-header .cart-content').stop(true, true).slideDown(400);},
function(){	jQuery('.block-cart-header .cart-content').stop(true, true).delay(400).slideUp(300);}
);
};
});

/*************************************************************************************************************back-top*****************************************************************************/
jQuery(function () {
jQuery(window).scroll(function () {
if (jQuery(this).scrollTop() > 100) {
jQuery('#back-top').fadeIn();
} else {
jQuery('#back-top').fadeOut();
}
});

// scroll body to 0px on click
jQuery('#back-top a').click(function () {
jQuery('body,html').stop(false, false).animate({
scrollTop: 0
}, 800);
return false;
});
});

/***************************************************************************************************** Magento class **************************************************************************/
jQuery(document).ready(function() {
jQuery('.sidebar .block').last().addClass('last_block');
jQuery('.sidebar .block').first().addClass('first');
jQuery('.box-up-sell li').eq(2).addClass('last');
jQuery(' .form-alt li:last-child').addClass('last');
jQuery('.product-collateral #customer-reviews dl dd, #cart-sidebar .item').last().addClass('last');
jQuery('.product-view .product-img-box .more-views li:nth-child(4)').last().addClass('item-4');
jQuery('.header .row-2 .links').first().addClass('LoginLink');
jQuery('#checkout-progress-state li:odd').addClass('odd');
jQuery('.product-view .product-img-box .product-image').append('<span></span>');

if (jQuery('.container').width() < 766) {
jQuery('.my-account table td.order-id').prepend('<strong>Order #:</strong>');
jQuery('.my-account table td.order-date').prepend('<strong>Date: </strong>');
jQuery('.my-account table td.order-ship').prepend('<strong>Ship To: </strong>');
jQuery('.my-account table td.order-total').prepend('<strong>Order Total: </strong>');
jQuery('.my-account table td.order-status').prepend('<strong>Status: </strong>');
jQuery('.my-account table td.order-sku').prepend('<strong>SKU: </strong>');
jQuery('.my-account table td.order-price').prepend('<strong>Price: </strong>');
jQuery('.my-account table td.order-subtotal').prepend('<strong>Subtotal: </strong>');

jQuery('.multiple-checkout td.order-qty').prepend('<strong>Qty: </strong>');
jQuery('.multiple-checkout th.order-qty').prepend('<strong>Qty: </strong>');
jQuery('.multiple-checkout td.order-shipping').prepend('<strong>Send To: </strong>');
jQuery('.multiple-checkout td.order-subtotal').prepend('<strong>Subtotal: </strong>');
jQuery('.multiple-checkout td.order-price').prepend('<strong>Price: </strong>');
}
});

jQuery(window).bind('load resize',function(){
var maxHeight = 0;
function setHeight(column) {
column = jQuery(column);
column.each(function() {       
if(jQuery(this).height() > maxHeight) {
maxHeight = jQuery(this).height();;
}
});
column.height(maxHeight);
};	
(function(jQuery){jQuery.fn.equalHeights=function(minHeight,maxHeight){tallest=(minHeight)?minHeight:0;this.each(function(){if(jQuery(this).height()>tallest){tallest=jQuery(this).height()}});if((maxHeight)&&tallest>maxHeight)tallest=maxHeight;return this.each(function(){jQuery(this).height(tallest)})}})(jQuery)
sw = jQuery('.container').width();
if ( sw > 723 ) {
setHeight('.products-grid .product-shop');
jQuery('.product-name-s').equalHeights();
} else { 
jQuery('.products-grid .product-shop').removeAttr('style');
jQuery('.products-grid .product-name-s').removeAttr('style');
};
});

jQuery(document).ready(function() {
if (jQuery('.container').width() < 450) {
jQuery('.related-carousel').jcarousel({
vertical: false,
visible:1,
scroll: 1
});
}
else {
jQuery('.related-carousel').jcarousel({
vertical: false,
visible:3,
scroll: 1
});
}
/*jQuery(".toner-configurator select").selectbox();
jQuery(".toner-configurator select").live('change',function(){
jQuery(".toner-configurator select").selectbox();
});*/
});

jQuery(window).load(function() {

});

/*********************** JQuery for make header fixed after scroll for mini-cart pop up ***********************/
function headerScroll(){
	var numli = $j('#cart-sidebar li').length;
	if (numli > 0){
		$j('#cart-sidebar li').hide();
		$j('#cart-sidebar li').slice(0,3).show();
	}
	var s = jQuery(".header-bottom");
	if(jQuery(window).width() >= 769){
		var pos = s.position();				   
		jQuery(document).scroll(function() {
			var windowpos = jQuery(window).scrollTop();
			if (windowpos >= 210) {
				s.addClass("fixed-header");
			} else {
				s.removeClass("fixed-header");	
			}
		});
	} else {
		s.removeClass("fixed-header");
		jQuery(document).scroll(function() {
			s.removeClass("fixed-header");	
		});
	}
}
jQuery(document).ready(headerScroll);
jQuery(window).on('load resize', headerScroll);
