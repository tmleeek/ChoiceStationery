//***************************************************************************
//Before save quote
function beforeSaveQuote()
{
    persistantProductSelection.storeLogInTargetInput();
    $('tab_to_display').value = quotation_edit_tabsJsTabs.activeTab.id;
    editForm.submit();
}

////**************************************************************************
//display div to add product / fake product
function showAddProductDiv(divToDisplay)
{
    document.getElementById('tab_add_fake').style.display = 'none';
    document.getElementById('tab_add_product').style.display = 'none';
    
    document.getElementById(divToDisplay).style.display = '';
}

//**************************************************************************
//Diplay or Hide weight control depending of method
function ToggleQuoteWeightFieldVisibility()
{
    if (document.getElementById('myform[auto_calculate_weight]').value == 0)
        document.getElementById('myform[weight]').className = '';
    else
        document.getElementById('myform[weight]').className = 'not-editable-zone';
}

//**************************************************************************
//Diplay or Hide price control depending of method
function ToggleQuotePriceFieldVisibility()
{
    if (document.getElementById('myform[auto_calculate_price]').value == 0){
        document.getElementById('myform[price_ht]').className = '';
        document.getElementById('myform[show_detail_price]').disabled = true;
    }
    else{
        document.getElementById('myform[price_ht]').className = 'not-editable-zone';
        document.getElementById('myform[show_detail_price]').disabled = false;
    }
}

//******************************************************************************
//Submit quote form
function SubmitForm()
{
    var spans = document.getElementsByTagName('input');
    var hasNotExcludeProduct = false;
    var hasProduct = false;
    for (i=0; i < spans.length; i++)
    {
        if (spans[i] && spans[i].id != null)
        {
            if (spans[i].id.indexOf('exclude_') != -1)
            {
                hasProduct = true;
                if (!spans[i].checked)
                    hasNotExcludeProduct = true;
            }
        }
    }
	
    if ((!hasNotExcludeProduct) && (hasProduct))
    {
        alert('All products can not be excluded');
        return;
    }
	
    //Submit form
    document.getElementById('edit_form').submit();
}

//******************************************************************************
//Display final price for each product row
function DisplayFinalPrice(rowId)
{
    var Price;
    var Discount;
    var FinalPrice;
    var Margin;
    var Cost;
	
    Cost = document.getElementById('cost_' + rowId).value;
    Price = document.getElementById('price_ht_' + rowId).value;
    Discount = document.getElementById('discount_purcent_' + rowId).value;
    if (Discount == '')
        Discount = 0;
		
    FinalPrice = Price * (1 - Discount / 100);
    Margin = (FinalPrice - Cost) / FinalPrice * 100;

    document.getElementById('final_price_' + rowId).innerHTML = FinalPrice.toFixed(2) + '<br><i>' + Margin.toFixed(2) + '%</i>';
}

//******************************************************************************
//Initialize prices 
function InitAllFinalPrice()
{
    var spans = document.getElementsByTagName('span');
    for (i=0; i < spans.length; i++)
    {
        if (spans[i] && spans[i].id != null)
        {
            if (spans[i].id.indexOf('final_price_') != -1)
            {
                var name = spans[i].id;
                var product_id = name.split('_');
                DisplayFinalPrice(product_id[2]);
            }
        }
	 
    }
}


//******************************************************************************
//display block product
function displayBlockFakeProduct(element){

    if($(element).style.display == 'none')
        $(element).style.display = 'block';
    else
        $(element).style.display = 'none';
	
}

//******************************************************************************
//
function incrementField(fieldName)
{
    var currentValue = parseInt($(fieldName).value);
    if (isNaN(currentValue))
        currentValue = 0;
    currentValue++;
    $(fieldName).value = currentValue;
}