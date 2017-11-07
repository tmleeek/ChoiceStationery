/*
 * 	Easy Tooltip 1.0 - jQuery plugin
 *	written by Alen Grakalic	
 *	http://cssglobe.com/post/4380/easy-tooltip--jquery-plugin
 *
 *	Copyright (c) 2009 Alen Grakalic (http://cssglobe.com)
 *	Dual licensed under the MIT (MIT-LICENSE.txt)
 *	and GPL (GPL-LICENSE.txt) licenses.
 *
 *	Built for jQuery library
 *	http://jquery.com
 *
 */
 
(function($j) {

	$j.fn.easyTooltip = function(options){
	  
		// default configuration properties
		var defaults = {	
			xOffset: -50,		
			yOffset: 35,
			tooltipId: "easyTooltip",
			clickRemove: false,
			content: "",
			useElement: ""
		}; 
			
		var options = $j.extend(defaults, options);  
		var content;
				
		this.each(function() {  				
			var title = $j(this).attr("title");				
			$j(this).hover(function(e){											 							   
				content = (options.content != "") ? options.content : title;
				content = (options.useElement != "") ? $j("#" + options.useElement).html() : content;
				$j(this).attr("title","");									  				
				if (content != "" && content != undefined){			
					$j("body").append("<div id='"+ options.tooltipId +"'>"+ content +"</div>");		
					$j("#" + options.tooltipId)
						.css("position","absolute")
						.css("top",(e.pageY - options.yOffset) + "px")
						.css("left",(e.pageX + options.xOffset) + "px")						
						.css("display","none")
						.fadeIn("fast")
				}
			},
			function(){	
				$j("#" + options.tooltipId).remove();
				$j(this).attr("title",title);
			});	
			$j(this).mousemove(function(e){
				$j("#" + options.tooltipId)
					.css("top",(e.pageY - options.yOffset) + "px")
					.css("left",(e.pageX + options.xOffset) + "px")					
			});	
			if(options.clickRemove){
				$j(this).mousedown(function(e){
					$j("#" + options.tooltipId).remove();
					$j(this).attr("title",title);
				});				
			}
		});
	  
	};

})(jQuery);