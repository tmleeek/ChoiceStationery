<?php

class Magestore_Megamenu_Block_Adminhtml_Megamenu_Edit_Tab_Content_Maincontent extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        if (Mage::getSingleton('adminhtml/session')->getMegamenuData()) {
            $data = Mage::getSingleton('adminhtml/session')->getMegamenuData();
            Mage::getSingleton('adminhtml/session')->setMegamenuData(null);
        } elseif (Mage::registry('megamenu_data'))
            $data = Mage::registry('megamenu_data')->getData();
        $fieldset = $form->addFieldSet('megamenu_submenu', array('legend' => Mage::helper('megamenu')->__('General Configuration')));
        if(!isset($data['submenu_width']) || !$data['submenu_width'])
            $data['submenu_width'] = 100;
         $fieldset->addField('submenu_width','note', array(
            'label' =>  Mage::helper('megamenu')->__('Width'),
            'index' =>  'submenu_width',
            'name'  => 'submenu_width',
            'text' =>$data['submenu_width'].'%',
             'after_element_html' => '</br><input type="range" onchange="$(\'submenu_width\').update(this.value+\'%\')" id="submenu_width_slide" name="submenu_width" value="'.$data['submenu_width'].'">'
        ));
         $imagemenu = Mage::getBaseUrl('media').'megamenu/menu.png';
         $fieldset->addField('submenu_align', 'select', array(
            'label'        => Mage::helper('megamenu')->__('Alignment Type'),
            'name'        => 'submenu_align',
            'values'    => Mage::getModel('megamenu/megamenu')->getSubmenualignOptions(),
             'after_element_html' => '<img style="margin-left: -200px;max-width: 900px;margin-top: 20px;" class="megamenu-image" src="'.$imagemenu.'"/>'
        ));
          $fieldset->addField('leftsubmenu_align', 'select', array(
            'label'        => Mage::helper('megamenu')->__('Alignment Type'),
            'name'        => 'leftsubmenu_align',
            'values'    => Mage::getModel('megamenu/megamenu')->getLeftSubmenualignOptions(),
        ));
        $fieldset = $form->addFieldSet('megamenu_maincontent', array('legend' => Mage::helper('megamenu')->__('Main Content')));
     
         $fieldset->addField('menu_type', 'select', array(
            'label'        => Mage::helper('megamenu')->__('Main Content Type'),
            'name'        => 'menu_type',
            'onchange' => 'toggleMenuType()',
            'values'    => Mage::getModel('megamenu/megamenu')->getMenutypeOptions(),
        ));
        
       
        if(!isset($data['colum']) || !$data['colum'])
            $data['colum'] = 4;
        $fieldset->addField('colum','text', array(
            'label' =>  Mage::helper('megamenu')->__('Number of Columns'),
            'index' =>  'colum',
            'name'  => 'colum',
            'note' => '
                <span><a href="JavaScript:void(0);" id="colum_number_tooltip">'.$this->__('What is this?').'</a> <br>'.$this->__('If the value is zero or empty, default will be 4').      
                    '<script type="text/javascript">
                        tip = new Tooltip("colum_number_tooltip","'.Mage::getBaseUrl('media').'megamenu/maincontent.png");
                    </script>
                </span>'
        )); 
       
        
        $fieldset->addField('categories_box_title','text', array(
            'label' =>  Mage::helper('megamenu')->__('Categories Box Title'),
            'index' =>  'categories_box_title',
            'name'  => 'categories_box_title'
        ));
    
        $fieldset->addField('products_box_title','text', array(
            'label' =>  Mage::helper('megamenu')->__('Products Box Title'),
            'index' =>  'products_box_title',
            'name'  => 'products_box_title',
            'value' =>  'Products'
        ));

         $fieldset->addField('category_type', 'select', array(
            'label'        => Mage::helper('megamenu')->__('Arrange Category Items by'),
            'name'        => 'category_type',
            'values'    => Mage::getModel('megamenu/megamenu')->getCategorytypeOptions(),
        ));
         $fieldset->addField('category_image', 'select', array(
            'label'        => Mage::helper('megamenu')->__('Category Image shown'),
            'name'        => 'category_image',
            'values'    => Mage::getModel('megamenu/megamenu')->getCategoryImageOptions(),
        )); 
        $wysiwygConfig = Mage::getSingleton('cms/wysiwyg_config')->getConfig(
			array(
				'hidden'=>false,
				'add_variables' => true, 
				'add_widgets' => true,
				'add_images'=>true,
				'widget_window_url'	=> Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/widget/index'),
				'directives_url'	=> Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/cms_wysiwyg/directive'),
				'directives_url_quoted'	=> preg_quote(Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/cms_wysiwyg/directive')),
				'files_browser_window_url'	=> Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/cms_wysiwyg_images/index')
			)
		);
        $fieldset->addField('main_content', 'editor', array(
            'label' => Mage::helper('megamenu')->__('Menu Content'),
            'title' => Mage::helper('megamenu')->__('Menu Content'),
            'name' => 'main_content',
            'wysiwyg' => true,
            'config'        =>$wysiwygConfig,
        ));
        
        $categoryIds = implode(", ", Mage::getResourceModel('catalog/category_collection')->addFieldToFilter('level', array('gt' => 0))->getAllIds());
        if(!isset($data['categories'])){
            $data['categories'] = $categoryIds;
        }
        $fieldset->addField('categories', 'text', array(
            'label' => Mage::helper('megamenu')->__('Categories'),
            'name' => 'categories',
            'after_element_html' => '<a id="category_link" href="javascript:void(0)" onclick="toggleMainCategories()"><img src="' . $this->getSkinUrl('images/rule_chooser_trigger.gif') . '" alt="" class="v-middle rule-chooser-trigger" title="Select Categories"></a>
                <div  id="categories_check" style="display:none">
                    <a href="javascript:toggleMainCategories(1)">Check All</a> / <a href="javascript:toggleMainCategories(2)">Uncheck All</a>
                </div>
                <div id="main_categories_select" style="display:none"></div>
                    <script type="text/javascript">
                    function toggleMainCategories(check){
                        var cate = $("main_categories_select");
                        if($("main_categories_select").style.display == "none" || (check ==1) || (check == 2)){
                            $("categories_check").style.display ="";
                            var url = "' . $this->getUrl('adminhtml/megamenu/chooserMainCategories') . '";
                            if(check == 1){
                                $("categories").value = $("category_all_ids").value;
                            }else if(check == 2){
                                $("categories").value = "";
                            }
                            var params = $("categories").value.split(", ");
                            var parameters = {"form_key": FORM_KEY,"selected[]":params };
                            var request = new Ajax.Request(url,
                                {
                                    evalScripts: true,
                                    parameters: parameters,
                                    onComplete:function(transport){
                                        $("main_categories_select").update(transport.responseText);
                                        $("main_categories_select").style.display = "block"; 
                                    }
                                });
                        if(cate.style.display == "none"){
                            cate.style.display = "";
                        }else{
                            cate.style.display = "none";
                        } 
                    }else{
                        cate.style.display = "none";
                        $("categories_check").style.display ="none";
                    }
                };
		</script>
            '
        ));
        $productIds = implode(", ", Mage::getResourceModel('catalog/product_collection')->getAllIds());
        $fieldset->addField('products', 'text', array(
            'label' => Mage::helper('megamenu')->__('Products'),
            'name' => 'products',
            'class' => 'rule-param',
            'after_element_html' => '<a id="product_link" href="javascript:void(0)" onclick="toggleMainProducts()"><img src="' . $this->getSkinUrl('images/rule_chooser_trigger.gif') . '" alt="" class="v-middle rule-chooser-trigger" title="Select Products"></a><input type="hidden" value="'.$productIds.'" id="product_all_ids"/><div id="main_products_select" style="display:none;width:640px"></div>
                <script type="text/javascript">
                    function toggleMainProducts(){
                        if($("main_products_select").style.display == "none"){
                            var url = "' . $this->getUrl('adminhtml/megamenu/chooserMainProducts') . '";
                            var params = $("products").value.split(", ");
                            var parameters = {"form_key": FORM_KEY,"selected[]":params };
                            var request = new Ajax.Request(url,
                            {
                                evalScripts: true,
                                parameters: parameters,
                                onComplete:function(transport){
                                    $("main_products_select").update(transport.responseText);
                                    $("main_products_select").style.display = "block"; 
                                }
                            });
                        }else{
                            $("main_products_select").style.display = "none";
                        }
                    };
                    
                   
                </script>'
        ));
        $fieldset->addField('text_font','text', array(
            'label' =>  Mage::helper('megamenu')->__('Product Label'),
            'index' =>  'text_font',
            'name'  => 'text_font',  
        ));
        $fieldset->addField('text_color','text', array(
            'label' =>  Mage::helper('megamenu')->__('Product Label Color'),
            'index' =>  'text_color',
            'name'  => 'text_color',  
            'class' => 'color',
            'values' => 'ff0000',
        ));
        $fieldset->addField('background_color', 'text', array(
            'label' => Mage::helper('megamenu')->__('Products Using Label'),
            'name' => 'background_color',
            'class' => 'rule-param',
            'after_element_html' => '<a id="product_link2" href="javascript:void(0)" onclick="toggleMainProducts2()"><img src="' . $this->getSkinUrl('images/rule_chooser_trigger.gif') . '" alt="" class="v-middle rule-chooser-trigger" title="Select Products"></a><input type="hidden" value="'.$productIds.'" id="product_all_ids2"/><div id="main_products_select2" style="display:none;width:640px"></div>
                <script type="text/javascript">
                    function toggleMainProducts2(){
                        if($("main_products_select2").style.display == "none"){
                            var url = "' . $this->getUrl('adminhtml/megamenu/chooserMainProducts2') . '";
                            var params = $("background_color").value.split(", ");
                            var parameters = {"form_key": FORM_KEY,"selected[]":params };
                            var request = new Ajax.Request(url,
                            {
                                evalScripts: true,
                                parameters: parameters,
                                onComplete:function(transport){
                                    $("main_products_select2").update(transport.responseText);
                                    $("main_products_select2").style.display = "block"; 
                                }
                            });
                        }else{
                            $("main_products_select2").style.display = "none";
                        }
                    };
                   
                   
                </script>'
        ));
		 $fieldset->addField('subtitle_font_size', 'select', array(
            'label'        => Mage::helper('megamenu')->__('Show View all link'),
            'name'        => 'subtitle_font_size',
            'values'    => Mage::getModel('megamenu/megamenu')->getCategoryImageOptions(),
        ));
        $form->setValues($data);
        return parent::_prepareForm();
    }

    public function getLoadUrl() {
        return $this->getUrl('*/*/chooser');
    }

}