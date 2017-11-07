jQuery(document).ready(function() {
	
	jQuery('#pn-tabbed-nav li').hover(function() {
		var hoverID = jQuery(this).attr('id');
		jQuery('#pn-tabbed-nav li').removeClass('hovered-item');
		jQuery(this).addClass('hovered-item');
		jQuery('#pn-tabbed-content > div').removeClass('active-item');
		jQuery('#pn-tabbed-content > div.'+hoverID).addClass('active-item');
	});
	
});