<?php

class HS_Defer_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_MODULE_ENABLED                 = 'defer/general/enabled';

    const XML_PATH_MODULE_JS_DEFER_ENABLED        = 'defer/js_settings/enabled';
    const XML_PATH_MODULE_JS_EXCLUDE_PATH         = 'defer/js_settings/exclude_path';
    const XML_PATH_MODULE_JS_EXCLUDE_HOMEPAGE     = 'defer/js_settings/exclude_homepage';
    const XML_PATH_MODULE_JS_EXCLUDE_CONTROLLER   = 'defer/js_settings/exclude_controller';

    const XML_PATH_MODULE_CSS_DEFER_ENABLED       = 'defer/css_settings/enabled';
    const XML_PATH_MODULE_CSS_EXCLUDE_PATH        = 'defer/css_settings/exclude_path';
    const XML_PATH_MODULE_CSS_EXCLUDE_HOMEPAGE    = 'defer/css_settings/exclude_homepage';
    const XML_PATH_MODULE_CSS_EXCLUDE_CONTROLLER  = 'defer/css_settings/exclude_controller';

    const DEFER_TYPE_JS                           = 'JS';
    
    /**
     * Return true to exclude homepage, false otherwise.
     *
     * @return boolean
     */
    public function isExcludeHomepage($type = self::DEFER_TYPE_JS) 
    {
        $path = constant("self::XML_PATH_MODULE_{$type}_EXCLUDE_HOMEPAGE");
        if ( ! Mage::getStoreConfig($path)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Return true if current page is homepage.
     *
     * @return boolean
     */
    public function isHomepage() 
    {
        if( ! Mage::getBlockSingleton('page/html_header')->getIsHomePage()) {
            return false;
        }    
        
        return true;
    }
    
    /**
     * Return true if current module_controller_action matches the excluded controllers.
     *
     * @return boolean
     */
    public function isExlcudeController($type = self::DEFER_TYPE_JS) 
    {
        $controllers = $this->getExcludeController($type);
        if( ! $controllers) {
            return false;
        }

        $currentController = $this->getModule() . '_' . $this->getController() .'_' . $this->getAction();
        foreach ($controllers as $controller) {
            $expr = trim($controller['expr']);
            $count = count(explode('_', $expr));
            if($count == 1) {
                $expr = $expr . '_index_index';
            } elseif ($count == 2) {
                $expr = $expr . '_index';
            }
            
            if ($expr === $currentController) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Return true if current path matches the excluded path.
     *
     * @return boolean
     */
    public function isExcludePath($type = self::DEFER_TYPE_JS) 
    {
        $excludePath = $this->getExcludePath($type);
        if( ! $excludePath) {
            return false;
        }

        $currentPath = Mage::app()->getRequest()->getRequestUri();
        foreach ($excludePath as $path) {
            $expr = trim($path['expr']);   
            if ($expr === $currentPath) {
                return true;
            }
        }
        
        return false; 
    }
    
    /**
     * Return current module.
     *
     * @return string
     */
    protected function getModule() 
    {
        return Mage::app()->getFrontController()->getRequest()->getModuleName();    
    }
    
    /**
     * Return current controller.
     *
     * @return string
     */
    protected function getController() 
    {
        return Mage::app()->getFrontController()->getRequest()->getControllerName();    
    }
    
    /**
     * Return current action.
     *
     * @return string
     */
    protected function getAction() 
    {
        return Mage::app()->getFrontController()->getRequest()->getActionName();    
    }
    
    
    /**
     * Return excluded controllers.
     *
     * @return string
     */
    public function getExcludeController($type = self::DEFER_TYPE_JS) 
    {
        $path = constant("self::XML_PATH_MODULE_{$type}_EXCLUDE_CONTROLLER");
        $controllers = Mage::getStoreConfig($path);
        $unserializeControllers = unserialize($controllers);
        if( ! $controllers || empty($unserializeControllers)) {
            return false;
        }
        
        return $unserializeControllers;
    }
    
    /**
     * Return excluded paths.
     *
     * @return string
     */
    public function getExcludePath($type = self::DEFER_TYPE_JS) 
    {
        $path = constant("self::XML_PATH_MODULE_{$type}_EXCLUDE_PATH");
        $excludePath = Mage::getStoreConfig($path);
        $unserializePath = unserialize($excludePath);
        if( ! $path || empty($unserializePath)) {
            return false;
        }
        
        return $unserializePath;
    }
    
    /**
     * Checks whether or not to defer.
     *
     * @return boolean
     */
    public function canDefer($type = self::DEFER_TYPE_JS) 
    {
        $path = constant("self::XML_PATH_MODULE_{$type}_DEFER_ENABLED");
        if( Mage::app()->getStore()->isAdmin()
            || ! Mage::getStoreConfigFlag($path)) {
            return false;
        }

        if($this->isExcludeHomepage($type) && $this->isHomepage($type)) {
            return false;
        }

        if($this->isExlcudeController($type) || $this->isExcludePath($type)) {
            return false;
        } 

        return true;
    }    
}
	 