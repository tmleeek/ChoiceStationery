var amUrlUpdate = new Class.create();

amUrlUpdate.prototype = {
    initialize: function (urls, template) {
        this.options = {
            init_url: urls['init_url'],
            process_url: urls['process_url'],
            conclude_url: urls['conclude_url'],
            template: template
        };

        this.indicator = $$('.am_processer_container')[0];
    },
    start: function () {
        if (this.options['template'] == '') {
            alert('Please specify url template');
            return;
        }
        this.initIndicator();
        new Ajax.Request(
            this.options['init_url'],
            {
                method: 'get',
                parameters: {template: this.options['template']},
                onSuccess: function (transport) {
                    var response = eval('(' + transport.responseText + ')');

                    this.page_size = response['page_size'];
                    this.total = response['total'];

                    this.pages = Math.ceil(this.total / this.page_size);

                    this.process(1);
                }.bind(this)
            }
        );
    },
    process: function (page) {
        new Ajax.Request(
            this.options['process_url'],
            {
                method: 'post',
                parameters: {
                    template: this.options['template'],
                    page: page
                },
                onSuccess: function (transport) {
                    if (page < this.pages) {
                        this.updateIndicator(page)
                        this.process(page + 1)
                    }
                    else {
                        this.conclude();
                    }
                }.bind(this)
            }
        );
    },
    conclude: function () {
        this.concludeIndicator();

        new Ajax.Request(
            this.options['conclude_url'],
            {
                method: 'post',
                onSuccess: function(){
                    this.indicator.hide();
                }.bind(this)
            }
        );
    },
    initIndicator: function(){
        this.indicator.show();
        this.indicator.down('.am_processer').setStyle({width: 0});
        this.indicator.down('.end')
            .removeClassName('end_imported')
            .addClassName('end_not_imported')
        ;
        $$('.am_meta_success_msg')[0].hide();
    },
    concludeIndicator: function(){
        this.indicator.down('.end')
            .addClassName('end_imported')
            .removeClassName('end_not_imported')
        ;
        this.indicator.down('.am_processer').setStyle({width: '100%'});
        $$('.am_meta_success_msg')[0].appear();
    },
    updateIndicator: function(page){
        var percent = (page * this.page_size / this.total) * 100;
        this.indicator.down('.am_processer').setStyle({width: percent + '%'});
    }
};
