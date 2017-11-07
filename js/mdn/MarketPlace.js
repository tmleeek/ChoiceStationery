function clearArea(elt){

    while(elt.firstChild)
        elt.removeChild(elt.firstChild);

}

/*function showMpMessage(id){

    var div = document.getElementById(id);
    var layer = document.getElementById('mp_message_layer');
    var container = document.getElementById('mp_message_container');
    var content = document.getElementById('mp_message_content');

    div.style.top = '50px;';
    div.style.left = '200px';
    
    //content.appendChild(div);

    layer.style.display = 'block';
    //container.style.display = 'block';
    div.style.display = 'block';

    return false;

}*/

/*function closeMpMessage(){

    var container = document.getElementById('mp_message_container');
    var content = document.getElementById('mp_message_content');
    var layer = document.getElementById('mp_message_layer');

    clearArea(content);
    container.style.display = 'none';
    layer.style.display = 'none';

    return false;

}*/

/********************************************************************************/
/*                         PIXMANIA                                             */
/********************************************************************************/

function verifFormPixmania(){

    var error = 0;
    var requis = new Array('sku','langue','ean','id_segment', 'id_marque', 'id_marque', 'libelle_principal', 'description' );

    for(var i=0; i<requis.length;i++){

        var elt = document.getElementById('product_'+requis[i]);

        if(elt.value == "" || elt.value == "empty"){
            elt.className = 'addProductPixError';
            error++;
        } else {
            elt.className = '';
        }
    }

    if (error != 0)
    {
        alert('Form is not properly filled');
    }
	
    return (error == 0) ? true : false;

}

var ajaxResults = new Array();
ajaxResults['Univers'] = new Array();
ajaxResults['Category'] = new Array();

function loadPixmania(id, xmlElt, domElt){

    // if ajax request has already be done, load saved result
    if(typeof(ajaxResults[xmlElt][id]) != "undefined")
        document.getElementById(domElt).innerHTML = ajaxResults[xmlElt][id];
    else{
        var url = MDN_MARKETPLACE_PIXMANIA_LOAD +'id/'+id+'/elt/'+xmlElt;
        
        new Ajax.Request(
            url,
            {
                methode : 'GET',
                onSuccess: function(transport, json){
                    ajaxResults[xmlElt][id] = transport.responseText;
                    document.getElementById(domElt).innerHTML = transport.responseText;
                },
                onFailure: function(response){
                    alert('error');
                }
            }
            );
    }

}

function addMarqueForm(){

    var label_name = document.createElement('label');
    label_name.setAttribute('for', 'marque_name');

    var label_name_txt = document.createTextNode('Name');
    label_name.appendChild(label_name_txt);

    var label_id = document.createElement('label');
    label_id.setAttribute('for', 'marque_id');

    var label_id_txt = document.createTextNode('Id');
    label_id.appendChild(label_id_txt);

    var marque_name = document.createElement('input');
    marque_name.setAttribute('id','marque_name');
    marque_name.setAttribute('name', 'marque_name');

    var marque_id = document.createElement('input');
    marque_id.setAttribute('id', 'marque_id');
    marque_id.setAttribute('name', 'marque_id');

    var a = document.createElement('a');
    a.setAttribute('id','add_marque');
    a.setAttribute('onClick', 'addMarque();return false;');
    a.setAttribute('href', '');
    var a_txt = document.createTextNode('Add');
    a.appendChild(a_txt);

    var affichage = document.getElementById('addMarqueArea');
    affichage.innerHTML = "";
    affichage.appendChild(label_id);
    affichage.appendChild(marque_id)
    affichage.appendChild(label_name);
    affichage.appendChild(marque_name);
    affichage.appendChild(a);

}

function addMarque(){

    var marque_name = document.getElementById('marque_name').value;
    var marque_id = document.getElementById('marque_id').value;

    var url = MDN_MARKETPLACE_PIXMANIA_ADDMARQUE + 'id/'+marque_id+'/name/'+marque_name;

    var area = document.getElementById('select_marque');

    new Ajax.Request(
        url,
        {
            methode : 'GET',
            onSuccess: function(transport, json){
                clearArea(area);
                area.innerHTML = transport.responseText;
            },
            onFailure: function(response){
                alert('error');
            }
        }
        );

    var add_marque = document.getElementById('addMarqueArea');

    var a = document.createElement('a');
    a.setAttribute('href','');
    a.setAttribute('onClick', 'addMarqueForm(); return false;');

    var a_txt = document.createTextNode('Add');

    a.appendChild(a_txt);

    clearArea(add_marque);
    add_marque.appendChild(a);

}

function editMarqueForm(){

    var table = document.getElementById('brandEditTable');
    table.style.display = "block";
    var button = document.getElementById('brandEdit');
    button.innerHTML = "Hide";
    button.setAttribute('onClick','hideMarqueForm();');

    return false;

}

function hideMarqueForm(){

    var table = document.getElementById('brandEditTable');
    table.style.display = "none";
    var button = document.getElementById('brandEdit');
    button.innerHTML = "Edit";
    button.setAttribute('onClick','editMarqueForm();');

    return false;
}

var i=0;

function addMarqueField(){

    var tr = document.createElement('tr');

    var td_name = document.createElement('td');
    var input_name = document.createElement('input');
    input_name.setAttribute('id', 'marque_'+i);
    input_name.setAttribute('name', 'marque['+i+'][name]');
    td_name.appendChild(input_name);

    var td_id = document.createElement('td');
    var input_id = document.createElement('input');
    input_id.setAttribute('id', 'id_'+i);
    input_id.setAttribute('name', 'marque['+i+'][id]');
    td_id.appendChild(input_id);

    var td_action = document.createElement('td');
    td_action.innerHTML = "";

    tr.appendChild(td_name);
    tr.appendChild(td_id);
    tr.appendChild(td_action);

    document.getElementById('brandTable').appendChild(tr);

    i++;


}

function deleteBrand(id){

    var elt = document.getElementById('marque_'+id);
    while(elt.firstChild){
        elt.removeChild(elt.firstChild);
    }

}

/********************************************************************************/
/*                         AMAZON                                               */
/********************************************************************************/

var nbrBulletPoint = 0;
var nbrSearchTerms = 0;
var nbrPlatinumKeywords = 0;
var nbrRebate = 0;
var nbrUsedFor = 0;
var nbrSubjectContent = 0;
var nbrTargetAudience = 0;
var nbrOtherItemAttributes = 0;

var max = new Array();

max['bullet_point'] = 5;
max['search_terms'] = 5;
max['platinum_keywords'] = 20;
max['rebate'] = 2;
max['used_for'] = 5;
max['other_item_attributes'] = 5;
max['target_audience'] = 3;
max['subject_content'] = 5;

function addInput(name, ref){

    var indice;
    var maxOccurs = 0;

    switch(name){
        case "bullet_point":
            maxOccurs = max['bullet_point'];
            if(nbrBulletPoint < maxOccurs){
                nbrBulletPoint++;
                indice = nbrBulletPoint;
                document.getElementById('product_nbr_bullet_point').value = indice;
            }
            break;
        case "search_terms":
            maxOccurs = max['search_terms'];
            if(nbrSearchTerms < maxOccurs){
                nbrSearchTerms++;
                indice = nbrSearchTerms;
                document.getElementById('product_nbr_search_terms').value = indice;
            }
            break;
        case "platinum_keywords":
            maxOccurs = max['platinum_keywords'];
            if(nbrPlatinumKeywords < maxOccurs){
                nbrPlatinumKeywords++;
                indice = nbrPlatinumKeywords;
                document.getElementById('product_nbr_platinum_keywords').value = indice;
            }
            break;
        case "rebate":
            maxOccurs = max['rebate'];
            if(nbrRebate < maxOccurs){
                nbrRebate++;
                indice = nbrRebate;
                document.getElementById('product_nbr_rebate').value = indice;
            }
            break;
        case "used_for":
            maxOccurs = max['used_for'];
            if(nbrUsedFor < maxOccurs){
                nbrUsedFor++;
                indice = nbrUsedFor;
                document.getElementById('product_nbr_used_for').value = indice;
            }
            break;
        case "other_item_attributes":
            maxOccurs = max['other_item_attributes'];
            if(nbrOtherItemAttributes < maxOccurs){
                nbrOtherItemAttributes++;
                indice = nbrOtherItemAttributes;
                document.getElementById('product_nbr_other_item_attributes').value = indice;
            }
            break;
        case "target_audience":
            maxOccurs = max['target_audience'];
            if(nbrTargetAudience < maxOccurs){
                nbrTargetAudience++;
                indice = nbrTargetAudience;
                document.getElementById('product_nbr_target_audience').value = indice;
            }
            break;
        case "subject_content":
            maxOccurs = max['subject_content'];
            if(nbrSubjectContent < maxOccurs){
                nbrSubjectContent++;
                indice = nbrSubjectContent;
                document.getElementById('product_nbr_subject_content').value = indice;
            }
            break;
    }

    if(indice < maxOccurs){
        var id = 'product_'+name+'_'+indice++;

        var contener = document.getElementById(ref);
        var tr = document.createElement('tr');

        var td_1 = document.createElement('td');
        td_1.setAttribute('style','min-width:200px;');
        var label = document.createElement('label');
        label.setAttribute('for',id);
        var txt = document.createTextNode(name);
        label.appendChild(txt);
        td_1.appendChild(label);
        tr.appendChild(td_1);

        var td_2 = document.createElement('td');
        td_2.setAttribute('style', 'min-width:200px;');
        var input = document.createElement('input');
        input.setAttribute('id', id);
        input.setAttribute('name',id);
        td_2.appendChild(input);
        tr.appendChild(td_2);

        contener.appendChild(tr);
    }
    else{
        alert('Max occurs : '+maxOccurs);
    }

}

function showProductTypeSelect(value, defaultProductType){

    // if ajax request has already be done, load saved result
    /*if(typeof(ajaxResults[xmlElt][id]) != "undefined")
    document.getElementById(domElt).innerHTML = ajaxResults[xmlElt][id];
else{*/
    
    var div = document.getElementById('ProductTypeSelect');
    var divForm = document.getElementById('productDataForm');
    clearArea(div);
    clearArea(divForm);

    if(value != ""){

        var url = MDN_MARKETPLACE_AMAZON_SHOWPRODUCTDATASELECT+'value/'+value;

        new Ajax.Request(
            url,
            {
                methode : 'GET',
                onSuccess: function(transport, json){
                    //ajaxResults[xmlElt][id] = transport.responseText;
                    div.innerHTML = transport.responseText;
					
                    //apply default product type if set
                    if ((defaultProductType != '') && (document.getElementById('ProductType')))
                        document.getElementById('ProductType').value = defaultProductType;
                },
                onFailure: function(response){
                    alert('error');
                }
            }
            );
    }
    
//}

}

function showProductDataForm(category, subcategory){

    // if ajax request has already be done, load saved result
    /*if(typeof(ajaxResults[xmlElt][id]) != "undefined")
    document.getElementById(domElt).innerHTML = ajaxResults[xmlElt][id];
else{*/

    var div = document.getElementById('productDataForm');

    clearArea(div);

    if(subcategory != "" && subcategory != 0){

        var url = MDN_MARKETPLACE_AMAZON_SHOWPRODUCTDATAFORM+'category/'+category+'/subcategory/'+subcategory;

        new Ajax.Request(
            url,
            {
                methode : 'GET',
                onSuccess: function(transport, json){
                    //ajaxResults[xmlElt][id] = transport.responseText;
                    div.innerHTML = transport.responseText;
                },
                onFailure: function(response){
                    alert('error');
                }
            }
            );
    }
    
//}

}


/********************************************************************************/
/*                               GLOBAL                                            */
/********************************************************************************/

function showCategoriesForUnivers(univers){

    var url = MDN_MARKETPLACE_SHOWCATEGORIES+'univers/'+univers;
    var div = document.getElementById('select_categories');

    clearArea(div);
    clearArea(document.getElementById('select_sub_cat'));

    if(univers != ""){
        new Ajax.Request(
            url,
            {
                method : 'GET',
                onSuccess: function(transport){
                    div.innerHTML = transport.responseText;
                },
                onFailure: function(response){
                    div.innerHTML = '<span color="red">'+response.status+' : '+response.statusText+'</span>';
                }
            }

        );
    }

}

function showSubCategoriesForCategory(cat){

    var url = MDN_MARKETPLACE_SHOWSUBCATEGORIES+'category/'+cat;
    var div = document.getElementById('select_sub_cat');

    clearArea(div);

    if(cat != ""){

        new Ajax.Request(
            url,
            {
                method : 'GET',
                onSuccess: function(transport){
                    div.innerHTML = transport.responseText;
                },
                onFailure: function(response){
                    div.innerHTML = '<span color="red">'+response.status+' : '+response.statusText+'</span>';
                }
            }

        );
    }

}

function showSubCategorySubCategories(cat){
    
    var url = MDN_MARKETPLACE_SHOWSUBSUBCATEGORIES+'category/'+cat;
    var div = document.getElementById('select_sub_sub_cat'); // .....
    
    clearArea(div);
    
    if(cat != ""){
        
        new Ajax.Request(
            url,
            {
                method: 'GET',
                onSuccess: function(transport){
                    div.innerHTML = transport.responseText;
                },
                onFailure: function(response){
                    div.innerHTML = '<span color="red">'+response.status+' : '+response.statusText+'</span>';
                }
            }
        );
        
    }
    
}

function updateReference(){

    var univers = document.getElementById('univers').value;
    var cat = document.getElementById('categories').value;
    var sub_cat = document.getElementById('sub_categories').value;
    var div = document.getElementById('cat_ref');

    clearArea(div);

    if(univers != "" && cat != "" && sub_cat != ""){

        var catTab = cat.split('-');

        var selected = univers+'-'+catTab[0]+'-'+sub_cat;

        var url = MDN_MARKETPLACE_GETCATEGORIEREFERENCE+'selected/'+selected;

        new Ajax.Request(
            url,
            {
                method: 'GET',
                onComplete: function(transport){
                    //alert(response.responseText);
                    div.value = transport.responseText;
                },
                onFailure: function(response){
                    //alert(response.statusText);
                    div.value = response.status+' : '+response.statusText;
                }
            }

        );
    }
}

/******************************************************************************/
/*                            CDISCOUNT                                       */
/******************************************************************************/
function updateReferenceCdiscount(){
    
    var id = document.getElementById('sub_sub_categories').value;
    var div = document.getElementById('cat_ref');
    
    clearArea(div);
    
    if(id != ""){
        
        var selected = id;
        
        var url = MDN_MARKETPLACE_GETCATEGORIEREFERENCE+'selected/'+selected;
        
        new Ajax.Request(
            url,
            {
                method: 'GET',
                onSuccess: function(transport){
                    div.value = transport.responseText;
                },
                onFailure: function(response){
                    div.value = response.status+' '+response.statusText;
                }
            }
        );
        
    }
    
}