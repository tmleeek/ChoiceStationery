function getSelectionId(text, li)
{
	var url = window.location.origin+'/printer/?model_id='+li.id;
	window.open(url,"_self");
}

function getbrands()
{
	var brandsData = '<option value="" disabled="disabled">Select Manufacturer</option>';
	var url = window.location.origin+'/inktonerfinder/custom/getBrand';
	
	$j('#inktonerfinder-brands-select').closest('div').append('<i class="loader fa fa-refresh fa-spin fa-lg"></i>');
	$j.getJSON(url, function(d){
		if(typeof d.data != 'undefined')
		{
			select_text = $j("#inktonerfinder-brands-select option:first-child").clone();
			html_s = '<select onchange="getModelsAndModelSeriesAjax();getModelsListAjax();" name="inktonerfinder-brands-select" id="inktonerfinder-brands-select">';
			$j.each(d.data, function() {
				html_s+= '<option value="'+this.id+'">'+this.val+'</option>';
			});
			html_s+= '</select>';
			$j('#inktonerfinder-brands-select').closest('div').html(html_s);
			$j(select_text).prependTo('#inktonerfinder-brands-select').prop('selected',true);
			$j('#inktonerfinder-brands-select').chosen({ search_contains: true });
		}
		$j('#inktonerfinder-brands-select').closest('div').find('.loader').remove();
	});
}
    
function getModelsAndModelSeriesAjax()
{
	console.log($j('#inktonerfinder-modelseries-select'));
	if($j('#inktonerfinder-modelseries-select').length > 0)
	{
		var manufacturersId = $j("#inktonerfinder-brands-select option:selected").val();
		if (manufacturersId)
		{
			var url = window.location.origin+'/inktonerfinder/custom/getModelsAndModelSeriesAjax/manufacture/'+manufacturersId;
			
			$j('#inktonerfinder-modelseries-select').prop('disabled',true).closest('div').append('<i class="loader fa fa-refresh fa-spin fa-lg"></i>');
			$j.getJSON(url, function(d){
				if(typeof d.data != 'undefined')
				{
					select_text = $j("#inktonerfinder-modelseries-select option:first-child").clone();
					html_s = '<select onchange="getModelsListAjax()" name="inktonerfinder-modelseries-select" id="inktonerfinder-modelseries-select">';
					$j.each(d.data, function(){
						html_s+= '<option value="'+this.id+'">'+this.val+'</option>';
					});
					html_s+= '</select>';
					$j('#inktonerfinder-modelseries-select').closest('div').html('').html(html_s);
					$j(select_text).prependTo('#inktonerfinder-modelseries-select').prop('selected',true);
					$j('#inktonerfinder-modelseries-select').chosen({ search_contains: true });
				}
				$j('#inktonerfinder-modelseries-select').closest('div').find('.loader').remove();
			});
		}
	}
}

function getModelsListAjax()
{
	var manufacturersId = $j("#inktonerfinder-brands-select option:selected").val();
	var modelseriesId = $j("#inktonerfinder-modelseries-select option:selected").val();
	if (manufacturersId) 
	{
		var url = window.location.origin+'/inktonerfinder/custom/getModelsListAjax/modelseries/'+modelseriesId+'/manufacture/'+manufacturersId;
		
		$j('#inktonerfinder-models-select').prop('disabled',true).closest('div').append('<i class="loader fa fa-refresh fa-spin fa-lg"></i>');
		$j.getJSON(url, function(d){
			if(typeof d.data != 'undefined')
			{
				select_text = $j("#inktonerfinder-models-select option:first-child").clone();
				html_s = '<select onchange="getProductsListAjax()"  name="inktonerfinder-models-select" id="inktonerfinder-models-select">';
				$j.each(d.data, function(){
					html_s+= '<option value="'+this.id+'">'+this.val+'</option>';
				});
				html_s+= '</select>';
				$j('#inktonerfinder-models-select').closest('div').html('').html(html_s);
				$j(select_text).prependTo('#inktonerfinder-models-select').prop('selected',true);
				$j('#inktonerfinder-models-select').chosen({ search_contains: true });
			}
			$j('#inktonerfinder-models-select').closest('div').find('.loader').remove();
		});
	}
}

function getProductsListAjax()
{
	var modelId = $j("#inktonerfinder-models-select").val();
	var url = window.location.origin+'/printer/?model_id='+modelId;
	window.open(url,"_self");
}
    
//Event.observe(window, "load", getbrands);
$j(document).ready(function(){
	getbrands();
	$j('.dropdowns select').chosen({ search_contains: true });
	
	$j("#inktonerfinder-search" ).autocomplete({
    source: function( request, response ) {
    	$j('#inktonerfinder-search').closest('div').find('.loader').removeClass('fa-search').addClass('fa-refresh fa-spin');
      $j.ajax({
        url: window.location.origin+'/inktonerfinder/custom/productsAutoCompletePrototype/',
        dataType: "json",
        data: {term: request.term},
        success: function(d){
        	backdata = new Array();
        	$j(d.data).each(function(k,v){
        		backdata.push({value: v.val, id: v.id }); 
        	});
          response( backdata );
          $j('#inktonerfinder-search').closest('div').find('.loader').removeClass('fa-refresh fa-spin').addClass('fa-search');
        }
      });
    },
    minLength: 3,
    autoFocus: true,
    select: function( event, ui ) {
    	//console.log( ui.item);
    	//console.log( this);
    	var url = window.location.origin+'/printer/?model_id='+ui.item.id;
			window.open(url,"_self");
    },
    open: function() {
      $j( this ).removeClass( "ui-corner-all" ).addClass( "ui-corner-top" );
    },
    close: function() {
      $j( this ).removeClass( "ui-corner-top" ).addClass( "ui-corner-all" );
    }
  });
});

