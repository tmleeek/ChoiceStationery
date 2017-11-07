<?php

class Magestore_Megamenu_Helper_Data extends Mage_Core_Helper_Abstract {
    const CONTENT_ONLY = 1;
    const PRODUCT_LISTING = 2;
    const CATEGORY_LISTING = 3;
    const CONTACT_FORM = 4;
    const GROUP_CATEGORY_LISTING = 5;
    const ANCHOR_TEXT = 6;
    const PRODUCT_GRID =7 ;
    const CATEGORY_LEVEL = 8;
    const CATEGORY_DYNAMIC = 9;
    public function getSelectedStateSegment($currentUrl, $baseUrl) {
        $currentUrl = str_replace($baseUrl, '', $currentUrl);
        $currentUrl = str_replace('index.php/', '', $currentUrl);
        $currentUrl = $this->_hasStartingSlash($currentUrl);
        return $this->_removeDotHtml($this->_getSelectedStateSegment($currentUrl));
    }

    private function _getSelectedStateSegment($currentUrl) {
        $explodedCurrentUrl = explode('/', $currentUrl);
        return array_key_exists(0, $explodedCurrentUrl) ? $explodedCurrentUrl[0] : false;
    }

    public function getUrlImageCache() {

        return Mage::getBaseUrl('media') . 'megamenu/image/cache/';
    }

    public function getUrlImage() {
        return Mage::getBaseUrl('media') . 'megamenu/image/';
    }

    public function getUrlImageAdmin($id) {
        $collection = Mage::getModel('megamenu/template')->load($id);
        $image = $collection->getData('image');
        return Mage::getBaseUrl('media') . 'megamenu/image/' . $id . '.' . $image;
    }

    public function createImage($image, $id) {
        if (isset($image) && $image != '') {
            try {
                /* Starting upload */
                $uploader = new Varien_File_Uploader('image');

                // Any extention would work
                $uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png'));
                $uploader->setAllowRenameFiles(true);
                // Set the file upload mode 
                // false -> get the file directly in the specified folder
                // true -> get the file in the product like folders 
                //	(file.jpg will go in something like /media/f/i/file.jpg)
                $uploader->setFilesDispersion(false);

                // We set media as the upload dir
                $path = Mage::getBaseDir('media') . DS . 'megamenu' . DS . 'image' . DS . $id;
                $uploader->save($path, $image);
                $path_resze = Mage::getBaseDir('media') . DS . 'megamenu' . DS . 'image' . DS . 'cache' . DS . $id . DS . $image;
                $imageObj = new Varien_Image($path . DS . $image);
                $imageObj->constrainOnly(TRUE);
                $imageObj->keepAspectRatio(TRUE);
                $imageObj->keepFrame(FALSE);
                $imageObj->resize(350, 150);
                $imageObj->save($path_resze);
            } catch (Exception $e) {
                
            }
        }
    }

    public function ImportImage($image, $id, $pathimage) {
        $path = Mage::getBaseDir('media') . DS . 'megamenu' . DS . 'image' . DS . $id . DS . $image;
        $imageObjfull = new Varien_Image($pathimage);
        $imageObjfull->constrainOnly(TRUE);
        $imageObjfull->keepAspectRatio(TRUE);
        $imageObjfull->keepFrame(FALSE);
        $imageObjfull->save($path);

        $path_resze = Mage::getBaseDir('media') . DS . 'megamenu' . DS . 'image' . DS . 'cache' . DS . $id . DS . $image;
        $imageObj = new Varien_Image($pathimage);
        $imageObj->constrainOnly(TRUE);
        $imageObj->keepAspectRatio(TRUE);
        $imageObj->keepFrame(FALSE);
        $imageObj->resize(350, 150);
        $imageObj->save($path_resze);
    }

    public function getPathImageImport($name) {
        return Mage::getBaseDir('media') . DS . 'import' . DS . $name;
    }

    public function AdminImage($id) {
        if ($id != NULL) {
            $collection = Mage::getModel('megamenu/template')->load($id);

            $image = $collection->getData('image');

            $url = $this->getUrlImage() . $id . "/" . $image;
            $re = '<a><img id="image_small" src = "' . $url . '" width="30px" height="30px"/></a>
            <script type="text/javascript">                                           
                    tip = new Tooltip("image_small", "' . $url . '");                                                
            </script>
            ';
            return $re;
        } else {
            return "Up image";
        }
    }

    public function returnlayout() {
        return '&nbsp;&nbsp;&lt;default&gt;<br/>              
                &nbsp;&nbsp;&nbsp;&nbsp;&lt;reference name="left&gt;<br/>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;block name="leftmenu" type="megamenu/megamenu" template="megamenu/megamenu-left.phtml" before="-"/&gt<br/>         
                 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/reference&gt<br/>
          &nbsp;&nbsp;&lt;/default&gt';
    }

    public function returnblock() {
        return '&nbsp;&nbsp{{block type="megamenu/megamenu" template="megamenu/megamenu-left.phtml"}}<br>';
    }

    public function getFullProductUrl($pro) {
        return $pro->getProductUrl();
    }

    public function returntext() {
        return 'If you enable this module, it will rewrite your current top menu by a new mega menu. Also, you can show left mega menu in other places by choosing one of the options below (recommended for developers)';
    }

    public function returntemplate() {
        return "&nbsp;&nbsp;\$this->getLayout()->createBlock('megamenu/megamenu')->setTemplate('megamenu/megamenu-left.phtml')<br/>&nbsp;&nbsp;->tohtml();";
    }

    /**
     * get menu type
     * @return menu type array
     */
    public function getMenutypeOptions() {
        return array(
            array(
                'label' => 'Anchor Text',
                'value' => self::ANCHOR_TEXT
            ),
            array(
                'label' => 'Default Category Listing',
                'value' => self::CATEGORY_LEVEL
            ),
            array(
                'label' => 'Static Category Listing',
                'value' => self::CATEGORY_LISTING
            ),
            array(
                'label' => 'Dynamic Category Listing',
                'value' => self::CATEGORY_DYNAMIC
            ),
            array(
                'label' => 'Product Listing',
                'value' => self::PRODUCT_LISTING
            ),
            array(
                'label' => 'Product Grid',
                'value' => self::PRODUCT_GRID
            ),
            array(
                'label' => 'Content',
                'value' => self::CONTENT_ONLY
            ),
        );
    }
    public function getMegamenutypeOptions() {
        return array(
            array(
                'label' => 'Top Menu',
                'value' => 0
            ),
            array(
                'label' => 'Left Menu',
                'value' => 1
            ),
           
        );
    }
    /**
     * get menu type options for grid menu item
     * @return menu type options
     */
    public function menuTypeToOptionArray() {
        $result = array();
        $array = $this->getMenutypeOptions();
        foreach ($array as $item) {
            $result[$item['value']] = $item['label'];
        }
        return $result;
    }
     public function megamenuTypeToOptionArray() {
        $result = array();
        $array = $this->getMegamenutypeOptions();
        foreach ($array as $item) {
            $result[$item['value']] = $item['label'];
        }
        return $result;
    }

    /**
     * get featured type: none, product, category
     * @return array
     */
    public function getFeaturedTypes() {
        return array(
            array(
                'label' => 'None',
                'value' => '0'
            ),
            array(
                'label' => 'Product',
                'value' => '1'
            ),
            array(
                'label' => 'Category',
                'value' => '2'
            ),
            array(
                'label' => 'Content',
                'value' => '3'
            )
        );
    }

    /**
     * get font style
     * @return font array
     */
    public function getFontStyle() {
        return array(
            array(
                'label' => 'Arial',
                'value' => 'Arial,Helmet,Freesans,sans-serif'
            ),
            array(
                'label' => 'Times New Roman',
                'value' => 'Times New Roman'
            ),
            array(
                'label' => 'Tahoma',
                'value' => 'Tahoma, Geneva, sans-serif'
            ),
            array(
                'label' => 'Verdana',
                'value' => 'Verdana, Geneva, sans-serif'
            ),
            array(
                'label' => 'Georgia',
                'value' => 'Georgia, serif'
            ),
            array(
                'label' => 'Bookman Old Style',
                'value' => 'Bookman Old Style, serif'
            ),
            array(
                'label' => 'Comic Sans MS',
                'value' => 'Comic Sans MS, cursive'
            ),
            array(
                'label' => 'Courier New',
                'value' => 'Courier New, Courier, monospace'
            ),
            array(
                'label' => 'Garamond',
                'value' => 'Garamond, serif'
            ),
            array(
                'label' => 'Georgia',
                'value' => 'Georgia, serif'
            ),
            array(
                'label' => 'Impact, Charcoal',
                'value' => 'Impact, Charcoal, sans-serif'
            ),
            array(
                'label' => 'Lucida Console, Monaco',
                'value' => 'Lucida Console, Monaco, monospace'
            ),
            array(
                'label' => 'Tahoma',
                'value' => 'Tahoma, Geneva, sans-serif'
            ),
            array(
                'label' => 'Webdings',
                'value' => 'Webdings, sans-serif'
            )
        );
    }

    /**
     * Save html into system config
     */
    public function saveCacheHtml($store = null) {
        $currentStore = Mage::app()->getStore()->getStoreId();
        $stores = Mage::app()->getStores(false);
        foreach ($stores as $id => $store) {
            Mage::app()->setCurrentStore($store->getId());
            /* ----- Top Menu ------ */
            $topmenu = Mage::getModel('megamenu/megamenu')
                    ->getCollection()
                    ->addFieldToFilter('status', 1)
                    ->addFieldToFilter('megamenu_type', 0);
            if ($topmenu->getSize()) {
                $block = Mage::app()->getLayout()->createBlock('megamenu/navigationtop')
                        ->setArea('frontend')
                        ->setStore($id)
                        ->setTemplate('megamenu/topmenu.phtml');

                $html = $block->toHtml();
                $data = array();
                $staticBlock = Mage::getModel('cms/block')->load('megamenu_' . $id, 'identifier');
                if(!$staticBlock->getId()){
                    $data['title'] = 'Mega Menu ' . $store->getName();
                    $data['identifier'] = 'megamenu_' . $id;
                    $data['stores'] = array($id);
                    $data['block_id'] = $staticBlock->getId();
                }
                $data['content'] = $html;
                $model = Mage::getModel('cms/block')->load($staticBlock->getId());
                $model->addData($data)->save();
            }
            /* ---- End ------ */

            /* --- Left menu ----- */
            $left_menu = Mage::getModel('megamenu/megamenu')
                    ->getCollection()
                    ->addFieldToFilter('status', 1)
                    ->addFieldToFilter('megamenu_type', 1);
            if ($left_menu->getSize()) {
                $blockleft = Mage::app()->getLayout()->createBlock('megamenu/navigationleft')
                        ->setArea('frontend')
                        ->setStore($id)
                        ->setTemplate('megamenu/navigationleft.phtml');

                $htmlleft = $blockleft->toHtml();
                $data2 = array();
                $staticBlockleft = Mage::getModel('cms/block')->load('megamenuleft_' . $id, 'identifier');
                if(!$staticBlockleft->getId()){
                    $data2['title'] = 'Left Mega Menu ' . $store->getName();
                    $data2['identifier'] = 'megamenuleft_' . $id;
                    $data2['stores'] = array($id);
                    $data2['block_id'] = $staticBlockleft->getId();
                }
                $data2['content'] = $htmlleft;
                $model2 = Mage::getModel('cms/block')->load($staticBlockleft->getId());
                $model2->addData($data2)->save();
            }
            /* ---- End ------ */
        }
        Mage::app()->setCurrentStore($currentStore);
        Mage::getModel('core/config')->saveConfig('megamenu/general/reindex', 0);
        Mage::app()->getCacheInstance()->cleanType('config');
        Mage::app()->getCacheInstance()->cleanType('block_html');
    }

    public function positionIsAuto() {
        $store = Mage::app()->getStore()->getId();
        $positionType = Mage::getStoreConfig('megamenu/general/menu_position_type', $store);
        if ($positionType == 1) {
            return true;
        }
        return false;
    }

    public function positionSubAuto($align) {
        $sub_position = '';
        switch ($align) {
            case 0:
                $sub_position = 'sub_left';
                break;
            case 1:
                $sub_position = 'sub_right';
                break;
            case 2:
                $sub_position = 'sub_left position_auto';
                break;
            case 3:
                $sub_position = 'sub_right position_auto';
                break;
            default:
                break;
        }
        return $sub_position;
    }
    public function positionLeftSubAuto($align) {
        $sub_position = '';
            switch ($align) {
                case 0:
                    $sub_position = 'position_menu';
                    break;
                case 1:
                    $sub_position = 'position_item';
                    break;
                default:
                    break;
            }
            return $sub_position;
        }
    public function setLevel($level){
        switch ($level) {
                case 1:
                    $class = 'level1';
                    break;
                case 2:
                    $class = 'level2';
                    break;
                case 3:
                    $class = 'level3';
                    break;
                default:
                    break;
            }
            return $class;
    }

}
