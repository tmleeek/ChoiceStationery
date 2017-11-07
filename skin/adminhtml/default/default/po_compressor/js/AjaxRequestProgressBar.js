var AjaxRequestProgressBar = Class.create();
AjaxRequestProgressBar.prototype = {
    initialize: function(url, form, callbackAfter) {
        this.url = url;
        this.form = form;
        this.callbackAfter = callbackAfter;
    },
    getRequest: function()
    {
        if (!$('compressor_progress')) {
            $("loading_mask_loader").innerHTML = $("loading_mask_loader").innerHTML + "<span id=\"compressor_progress\"></span>";
        }
        this.showMask();
        new Ajax.Request(this.url, {
            parameters: this.form ? this.form.serialize(true) : '',
            onSuccess: function(response) {
                var json = JSON.parse(response.responseText);
                if (json.reload) {
                    location.reload();
                    return;
                }
                if (json.progress !== undefined) {
                    $("compressor_progress").innerHTML = json.progress + "%";
                }
                if (json.complete || json.progress === undefined) {
                    this._requestComplete();
                    return;
                }
                this.getRequest();
                return;
            }.bind(this)
        });
    },
    _requestComplete: function()
    {
        if (this.callbackAfter) {
            this.callbackAfter();
        }
        return;
    },
    showMask: function()
    {
        if (!$('loading-mask').visible()) {
            $('loading-mask').show();
        }
        return;
    },
    hideMask: function()
    {
        if ($('loading-mask').visible()) {
            $('loading-mask').hide();
            $('compressor_progress').remove();
        }
        return;
    }
};