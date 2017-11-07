<?php

class Magestore_Megamenu_Model_Observer {

    // give event when save product
    public function saveProduct($observer) {
        $product = $observer->getEvent()->getProduct();
        if ($product->getId()) {
             Mage::getModel('core/config')->saveConfig('megamenu/general/reindex',1);
        }
        Mage::app()->getCacheInstance()->cleanType('config');
        return;
    }

    // give event when delete product
    public function deleteProduct() {
        $products = Mage::app()->getRequest()->getParams('product');
        if ($products) {
            Mage::getModel('core/config')->saveConfig('megamenu/general/reindex',1);
        } else {
            $product_id = Mage::app()->getRequest()->getParams('id');
            if ($product_id) {
                 Mage::getModel('core/config')->saveConfig('megamenu/general/reindex',1);
            }
        }
        Mage::app()->getCacheInstance()->cleanType('config');
        return;
    }

    //give event when save category

    public function saveCategory($observer) {
        $category = $observer->getEvent()->getCategory();
        if ($category->getId()) {
             Mage::getModel('core/config')->saveConfig('megamenu/general/reindex',1);
             Mage::app()->getCacheInstance()->cleanType('config');
        }
        return;
    }

    //give event when delete category
    public function deleteCategory($observer) {
        $category = $observer->getEvent()->getCategory();
        if ($category->getId()) {
            Mage::getModel('core/config')->saveConfig('megamenu/general/reindex',1);
            Mage::app()->getCacheInstance()->cleanType('config');
        }
        return;
    }
    
    public function megamenu_item_save_after($observer){
        Mage::helper('megamenu')->saveCacheHtml();
    }
    
    /**
     * event after save config in admin
     */
    public function admin_system_config_changed_section_megamenu($observer){
        Mage::helper('megamenu')->saveCacheHtml();
    }
    public function prepareLayoutBefore($observer){
        /* Add jQuery */
        if (Mage::getStoreConfig('megamenu/general/jquery')){
            $block = $observer->getEvent()->getBlock();
            if ("head" == $block->getNameInLayout()) {
                $file  = 'megamenu/jquery-1.11.2.min.js';       
                $block->addJs($file);
            }
        }
        // Billy --- create sample data
        $data = Mage::getModel('megamenu/megamenu')->getCollection();
        if(!$data->getSize()){
            $data = array();
            $model = Mage::getModel('megamenu/megamenu');
            $rootcat = Mage::getModel('catalog/category')->getCollection()->getFirstItem();
            $catids = $rootcat->getAllChildren();
            $catids = explode(',',$catids);
            if(count($catids) > 0){
                unset($catids[0]);unset($catids[1]);
                $parentIds = array();
                $categories = Mage::getResourceModel('catalog/category_collection')
                    ->addAttributeToSelect('*')
                    ->addFieldToFilter('entity_id', array('in' => $catids))
                    ->addFieldToFilter('is_active', 1)
                                    ->setOrder('position','ASC');
                $categoryIds = $categories->getAllIds();
                foreach($categories as $category){
                    $parents = $category->getParentIds();
                    if(count(array_intersect($parents, $categoryIds))== 0)
                            $parentIds[] = $category->getId();
                }
                $i= 0;
                foreach($parentIds as $parentId){
                    $parrent = Mage::getModel('catalog/category')->load($parentId);
                    $childIds = $parrent ->getAllChildren();
                    $childIds = explode(',',$childIds);
                    unset($childIds[0]);
                    if(count($childIds)> 0) 
                        $type = 8;
                    else  
                        $type = 6;
                    $childIds = implode(', ',$childIds);
                    $data[]= array(
                        'name_menu' =>$parrent->getName(),
                        'stores'=> 0,
                        'link' =>$parrent->getUrl(),
                        'sort_order'=>$i,
                        'megamenu_type'=>0,
                        'status'=>1,
                        'menu_type'=>$type,
                        'categories'=>$childIds,
                        'submenu_align'=>2,
                        'submenu_width'=>20,
                        'featured_type'=> 0,
                    );
                    $i++;
                }
                for($i=0;$i< count($data);$i++){
                    $model->setData($data[$i])->save();
                }
                Mage::helper('megamenu')->saveCacheHtml();
            }
        }
        return $this;
    }
    public function cms_wysiwyg_config_prepare($observer){
		if(Mage::app()->getRequest()->getModuleName() !='megamenu')
			return $this;
        $config = $observer->getEvent()->getConfig();

        if ($config->getData('add_variables')) {
            $settings = $this->getWysiwygPluginSettings($config);
            $config->addData($settings);
        }
		if ($config->getData('add_widgets')) {
            $settings = $this->getPluginSettings($config);
            $config->addData($settings);
        }
        return $this;
    }
	public function getWysiwygPluginSettings($config)
    {
        $variableConfig = array();
        $onclickParts = array(
            'search' => array('html_id'),
            'subject' => 'MagentovariablePlugin.loadChooser(\''.$this->getVariablesWysiwygActionUrl().'\', \'{{html_id}}\');'
        );
        $variableWysiwygPlugin = array(array('name' => 'magentovariable',
            'src' => Mage::getModel('core/variable_config')->getWysiwygJsPluginSrc(),
            'options' => array(
                'title' => Mage::helper('adminhtml')->__('Insert Variable...'),
                'url' => $this->getVariablesWysiwygActionUrl(),
                'onclick' => $onclickParts,
                'class'   => 'add-variable plugin'
        )));
        $configPlugins = $config->getData('plugins');
        $variableConfig['plugins'] = $variableWysiwygPlugin;
        return $variableConfig;
    }
	public function getVariablesWysiwygActionUrl()
    {
        return Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/system_variable/wysiwygPlugin');
    }
	public function getPluginSettings($config)
    {
        $settings = array(
            'widget_plugin_src'   => Mage::getBaseUrl('js').'mage/adminhtml/wysiwyg/tiny_mce/plugins/magentowidget/editor_plugin.js',
            'widget_images_url'   => Mage::getModel('widget/widget_config')->getPlaceholderImagesBaseUrl(),
            'widget_placeholders' => Mage::getModel('widget/widget_config')->getAvailablePlaceholderFilenames(),
            'widget_window_url'   => Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/widget/index')
        );

        return $settings;
    }
}

