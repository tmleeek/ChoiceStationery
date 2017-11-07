//**************************************************************************************************************
//Display panel windows
function showPanelAskQuestion(url, productName)
{
    //retrieve content via ajax
	
    var request = new Ajax.Request(
        url,
        {
            method: 'GET',
            onSuccess: function onSuccess(transport)
            {
                var content = transport.responseText;
	        				
                //display windows
                win = new Window({
                    className: "alphacube", 
                    title: productName, 
                    width:800, 
                    height:400, 
                    destroyOnClose:true,
                    closable:true,
                    draggable:true, 
                    recenterAuto:true, 
                    okLabel: "OK"
                });
                win.setHTMLContent(content);
                win.showCenter();
            },
            onFailure: function onFailure(transport)
            {
                alert('An error occured : ' + url);
            }
        }
        );	
	
}

//**************************************************************************************************************
//Display Attachement Image gsa
function showImageAttachementPopup(url, fileName)
{
    var defaultImgW= 400;
    var defaultImgH= 400;

    //display windows
    win = new Window({
        className: "alphacube",
        title: fileName,
        showEffectOptions: {
            duration:0
        },
        hideEffectOptions: {
            duration:0
        },
        width:defaultImgW,
        height:defaultImgH,
        destroyOnClose:true,
        closable:true,
        draggable:true,
        recenterAuto:true,        
        okLabel: "OK"
    });
    win.setHTMLContent('<img src="' + url + '" />');
    win.showCenter();

    var img = new Image();
    img.onload = function() {
        var imgHMargin= 20;
        var imgWMargin= 50;
        var imgW=this.width;
        var imgH=this.height;
        //TODO compatibility with IE6 ... document.body.offsetWidth, document.body.offsetHeigh, document.documentElement.offsetHeight, document.documentElement.offsetWidth
        var winW=window.innerWidth;
        var winH=window.innerHeight;
        //alert ('win:'+winW + 'x'+winH + ' img:'+imgH + 'x'+imgW);
        if(imgH<winH && imgW<winW){
            win.setSize(imgW+imgWMargin, imgH+imgHMargin)
        }else{
            var newH = winH-(imgHMargin*2);
            var newW = winW-(imgWMargin*2);
            if(imgH>winH && imgW<winW){
                newW = imgW+imgWMargin;        
            }else if(imgH<winH && imgW>winW){
                newH = imgH+imgHMargin;        
            }
            win.setSize(newW, newH);
            win.setHTMLContent('<img src="' + url + '" width="'+newW+'" height="'+newH+'" />');
        }
        win.updateWidth();
        win.updateHeight();      
        win.refresh();
        win.showCenter(false);
    }
    img.src = url;

}

//**************************************************************************************************************
//
function showMessagesHistory(url, title)
{
    var winW=window.innerWidth;
    var winH=window.innerHeight;
    var HMargin= 150;
    var WMargin= 300;

    var request = new Ajax.Request(
        url,
        {
            method: 'GET',
            evalScripts: true,
            onSuccess: function onSuccess(transport)
            {
                var content = transport.responseText;

                win = new Window({
                    className: "alphacube",
                    title: title,
                    showEffectOptions: {
                        duration:0
                    },
                    hideEffectOptions: {
                        duration:0
                    },
                    width:(winW-HMargin),
                    height:(winH-HMargin),
                    destroyOnClose:true,
                    closable:true,
                    draggable:true,
                    recenterAuto:true,
                    okLabel: "OK"
                });
                win.setHTMLContent(content);                
                win.showCenter();
            },
            onFailure: function onFailure(transport)
            {
                alert('Impossible to retrieve ticket. Please try again');
            }
        }
        );

}

//**************************************************************************************************************
//
function submitTicketForm(url)
{
    //submit form with ajax
    //alert('we are goiung to submit form to : ' + url);
        
    var request = new Ajax.Request(
        url,
        {
            method:'POST',
            onSuccess: function onSuccess(transport)
            {
                alert('ticket as been submitted');
                // put result into the div
                document.getElementById('window_askquestion').innerHTML = transport.responseText;
            },
            onFailure: function onFailure()
            {
                alert('error');
            },
            parameters: Form.serialize(document.getElementById('product-question'))
        }
        );

}

//**************************************************************************************************************
//Pager in tickets list
function crmTicketChangePage(select)
{
    var pageId = select.value;
    var url = pageUrl;
    url = url.replace('##', pageId);
    document.location.href = url;
}

//**************************************************************************************************************
//
function showCustomerObjectPopup(url)
{
    var request = new Ajax.Request(
        url,
        {
            method: 'GET',
            evalScripts: true,
            onSuccess: function onSuccess(transport)
            {
                var content = transport.responseText;

                win = new Window({
                    className: "alphacube",
                    title: '',
                    width:1000,
                    height:800,
                    destroyOnClose:true,
                    closable:true,
                    draggable:true,
                    recenterAuto:true,
                    okLabel: "OK"
                });
                win.setHTMLContent(content);                
                win.showCenter();
            },
            onFailure: function onFailure(transport)
            {
                alert('An error occured');
            }
        }
        );
}