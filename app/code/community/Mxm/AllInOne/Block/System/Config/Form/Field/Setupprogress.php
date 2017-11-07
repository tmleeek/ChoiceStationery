<?php

class Mxm_AllInOne_Block_System_Config_Form_Field_Setupprogress
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $element->setType('hidden');
        $html = $element->getElementHtml();

        $html .= $this->getProgressBar($element);

        $html .= $this->getRetryButton($element);

        $html .= $this->getJs($element);

        return $html;
    }

    protected function getProgressBar($element)
    {
        return <<<HTML
<div style="background-color:#AAA7A7;width:280px;height:20px;text-align:center;">
    <div id="bar_{$element->getHtmlId()}" style="background-color:#54A254;width:0%;height:20px;"></div>
    <div id="val_{$element->getHtmlId()}" style="margin-top:-19px;"></div>
</div>
HTML;
    }

    protected function getRetryButton($element)
    {
        $retryUrl = Mage::helper('adminhtml')->getUrl('mxmallinone/setup/retry');
        $buttonHtml = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setType('button')
            ->setLabel(Mage::helper('mxmallinone')->__('Retry'))
            ->setOnClick("setLocation('$retryUrl')")
            ->toHtml();
        return <<<HTML
<div id="ret_{$element->getHtmlId()}" style="display:none;width:280px;text-align:center;margin-top:5px;">$buttonHtml</div>
HTML;
    }

    protected function getJs($element)
    {
        $websiteId = null;
        if ($element->getScope() === 'websites') {
            $websiteId = $element->getScopeId();
        }
        $url         = Mage::helper('adminhtml')->getUrl('mxmallinone/setup/progress');
        $setupData   = Mage::helper('mxmallinone')->getSetupData();
        $setupData['setup_required'] = Mage::helper('mxmallinone')->isSetupRequired($websiteId);
        $o           = Mage::helper('core')->jsonEncode($setupData);
        $waitingMsg  = Mage::helper('mxmallinone')->__('Waiting for setup') . '...';
        $failedMsg   = Mage::helper('mxmallinone')->__('Setup failed');
        $completeMsg = Mage::helper('mxmallinone')->__('Setup complete');
        $websiteId   = (is_null($websiteId)) ? 'null' : "'$websiteId'";
        return <<<JS
<script type="text/javascript">
Mxm = (typeof(Mxm) === 'undefined' ? {} : Mxm);
Mxm.SetupProgress = Class.create({
    hasRun: false,
    progress: '$o'.evalJSON(),
    url: '$url',
    websiteId: $websiteId,

    initialize: function() {
        Event.observe(window, 'load', this.onLoad.bind(this));
    },

    onLoad: function() {
        this.updateProgressField();
        this.checkProgress();
    },

    updateProgressField: function() {
        var o = this.progress,
            rowId = 'row_{$element->getHtmlId()}',
            barId = 'bar_{$element->getHtmlId()}',
            valId = 'val_{$element->getHtmlId()}',
            retId = 'ret_{$element->getHtmlId()}',
            apiCls = 'mxm-api-field',
            apiMsgCls = 'mxm-api-field-msg',
            showRetry = false,
            disableFields = false;
        if (o.setup_required) {
            this.hasRun = true;
            $(rowId).show();
            if (o.is_running) {
                $(valId).update(o.progress + '%');
                $(barId).setStyle({
                    width: o.progress + '%'
                });
                disableFields = true;
            } else {
                if (!o.failed) {
                    $(valId).update('$waitingMsg');
                    $(barId).setStyle({
                        width: '0%'
                    });
                } else {
                    $(valId).update('$failedMsg');
                    showRetry = true;
                }
            }
        } else if (this.hasRun) {
            $(rowId).show();
            $(valId).update('$completeMsg');
            $(barId).setStyle({width: '100%'});
        } else {
            $(rowId).hide();
        }
        if (showRetry) {
            $(barId).setStyle({
                width: '100%',
                backgroundColor: '#CFA4A4',
            });
            $(retId).show();
        } else {
            $(barId).setStyle({
                backgroundColor: '#54A254',
            });
            $(retId).hide();
        }
        if (disableFields) {
            $$('.'+apiCls).each(function(el){
                el.disable();
            });
            $$('.'+apiMsgCls).each(function(el){
                el.show();
            });
        } else {
            $$('.'+apiCls).each(function(el){
                el.enable();
            });
            $$('.'+apiMsgCls).each(function(el){
                el.hide();
            });
        }
    },

    checkProgress: function() {

        new Ajax.Request(this.url, {
            loaderArea: false,
            parameters: {
                website: this.websiteId
            },
            onSuccess: function(response) {
                this.progress = response.responseJSON;
                this.updateProgressField();
                this.checkProgress.bind(this).delay(2);
            }.bind(this)
        });
    }
});

new Mxm.SetupProgress();

</script>
JS;
    }

    protected function _decorateRowHtml($element, $html)
    {
        if (!Mage::helper('mxmallinone')->isSetupRequired()) {
            return '<tr id="row_' . $element->getHtmlId() . '" style="display:none;">' . $html . '</tr>';
        }
        return parent::_decorateRowHtml($element, $html);
    }
}
