<?php
/**
* @copyright Amasty.
*/ 
class Amasty_List_ListController extends Mage_Core_Controller_Front_Action
{
    private $_customerId = 0;
    
    public function preDispatch()
    {
        parent::preDispatch();
        if (!Mage::getStoreConfigFlag('amlist/general/active')) {
            $this->norouteAction();
            return;
        }
        
        $session = Mage::getSingleton('customer/session');
        if (!$session->authenticate($this)) {
            $this->setFlag('', 'no-dispatch', true);
            if(!$session->getBeforeAmlistUrl()) {
                $session->setBeforeAmlistUrl($this->_getRefererUrl());
            }
            if ($this->getRequest()->isPost()){
                //store custom options
                $productId = $this->getRequest()->getParam('product');
                if ($productId){
                    $params[$productId] = $this->getRequest()->getParams();
                    $session->setAmlistParams($params);
                }
            }
        }
        $this->_customerId = $session->getCustomer()->getId();
    }
    
    /**
     * Highlight menu and render layout
     */
    private function _renderLayoutWithMenu()
    {
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $navigationBlock = $this->getLayout()->getBlock('customer_account_navigation');
        if ($navigationBlock) {
            $navigationBlock->setActive('amlist/list');
        }
        $this->renderLayout();
    }
    
    /**
     * Show list of all customer's lists
     */
    public function indexAction()
    {
        $this->_renderLayoutWithMenu();
    }

    /**
     * Show list's title and items
     */
    public function editAction()
    {
        $list = Mage::getModel('amlist/list');
        $id = $this->getRequest()->getParam('id');
        if ($id){
            $list->load($id);
            if ($list->getCustomerId() != $this->_customerId){
                $this->_redirect('*/*/');
                return;
            }    
        } 
        Mage::register('current_list', $list);
        
        $this->_renderLayoutWithMenu();
    }
    
    /**
     * Save list details
     */
 	public function saveAction() {
 	    if (!$this->_validateFormKey()) {
            $this->_redirect('*/*/');
            return ;
        }
	    $id     = $this->getRequest()->getParam('id');
	    $list   = Mage::getModel('amlist/list');
	    if ($id){
	       $list->load($id);
	       if ($list->getCustomerId() != $this->_customerId){
	           $this->_redirect('*/*/');
	           return;    
	       }
	    }
	    
	    $data = $this->getRequest()->getPost();
		if ($data) {
			$list->setData($data)->setId($id);
			try {
			    $list->setCustomerId($this->_customerId);
			    $list->setCreatedAt(date('Y-m-d H:i:s'));
				$list->save();
                Mage::getSingleton('customer/session')->addSuccess(Mage::helper('amlist')->__('Folder has been successfully saved'));    
				Mage::getSingleton('customer/session')->setListFormData(false);
				
			    $productId = Mage::getSingleton('amlist/session')->getAddProductId(); 
			    Mage::getSingleton('amlist/session')->setAddProductId(null);  
				if ($productId){
				    $this->_redirect('*/*/addItem', array('product' => $productId, 'list'=>$list->getId()));
				    return;
				}
				$this->_redirect('*/*/edit', array('id' => $list->getId()));
				return;
				
            } catch (Exception $e) {
                Mage::getSingleton('customer/session')->addError($e->getMessage());
                Mage::getSingleton('customer/session')->setListFormData($data);
                $this->_redirect('*/*/', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('customer/session')->addError(Mage::helper('amlist')->__('Unable to find folder for saving'));
        $this->_redirect('*/*/');
	}
	
    /**
     * Delete list 
     */	
	public function removeAction() 
	{
	    $id     = (int)$this->getRequest()->getParam('id');
	    $list   = Mage::getModel('amlist/list')->load($id);
	    
	    if ($list->getCustomerId() == $this->_customerId){
    		try {
    		    // test !!!
        		$list->delete();
        		Mage::getSingleton('customer/session')->addSuccess($this->__('Folder has been successfully removed'));
            } catch (Exception $e) {
                Mage::getSingleton('customer/session')->addError($e->getMessage());
            }
		}
        $this->_redirect('*/*/index');
	}
	
	public function defaultAction() 
	{
	    $id     = (int)$this->getRequest()->getParam('id');
	    $list   = Mage::getModel('amlist/list')->load($id);
	    
		try {
    		$list->saveDefault();
    		Mage::getSingleton('customer/session')->addSuccess($this->__('Folder `%s` has been set as default.', $list->getTitle()));
        } 
        catch (Exception $e) {
            Mage::getSingleton('customer/session')->addError($e->getMessage());
        }
        
        $this->_redirect('*/*/index');
	}	
	
	/**
     * Delete a product from a list 
     */
	public function removeItemAction() {
	    $id    = (int) $this->getRequest()->getParam('id');
	    
	    $item  = Mage::getModel('amlist/item');
	    $item->load($id);
	    if (!$item->getId()){
	        $this->_redirect('*/*/');
	    }
	    
	    $list = Mage::getModel('amlist/list');
	    $list->load($item->getListId());
	    if ($list->getCustomerId() != $this->_customerId){
	        $this->_redirect('*/*/');
	        return;
	    }
	    
        try {
            $item->delete();
    		Mage::getSingleton('customer/session')->addSuccess($this->__('Product has been successfully removed from the folder'));
        }
        catch (Exception $e) {
            Mage::getSingleton('customer/session')->addError($this->__('There was an error while removing item from the folder: %s', $e->getMessage()));
        }
        $this->_redirect('*/*/edit', array('id' => $list->getId()));
        
	}
	

    /**
     * Get request for "add to cart" action
     *
     * @return  Varien_Object
     */
    protected function _getProductRequest()
    {
        $requestInfo = $this->getRequest()->getParams();
        
        $params = Mage::getSingleton('customer/session')->getAmlistParams();
        if ($params && key($params) == $this->getRequest()->getParam('product')){
            $requestInfo = current($params);
            Mage::getSingleton('customer/session')->setAmlistParams(null);
        }
        
        if ($requestInfo instanceof Varien_Object) {
            $request = $requestInfo;
        }
        elseif (is_numeric($requestInfo)) {
            $request = new Varien_Object();
            $request->setQty($requestInfo);
        }
        else {
            $request = new Varien_Object($requestInfo);
        }

        if (!$request->hasQty()) {
            $request->setQty(1);
        }
        
        return $request;
    }	
	
    /**
     * Add product(s) to the list 
     */	
 	public function addItemAction() 
 	{
 	    $session    = Mage::getSingleton('customer/session');
 	    
	    $productId  = $this->getRequest()->getParam('product');
	    
	    $list      = Mage::getModel('amlist/list');
	    $listId    = $this->getRequest()->getParam('list');
	    
	    if (!$listId){ //get default - last
	       $listId = $list->getLastListId($this->_customerId); 
	    }
	    
	    if (!$listId) { //create new
	       Mage::getSingleton('amlist/session')->setAddProductId($productId);
	       $this->_redirect('*/*/edit/');
	       return;
	    }
	    
	    $list->load($listId);
	    if ($list->getCustomerId() == $this->_customerId){
            try {
                $product = Mage::getModel('catalog/product')
                    ->setStoreId(Mage::app()->getStore()->getId())
                    ->load($productId);
                $request = $this->_getProductRequest(); 
                    
                if ($product->getTypeId() == 'grouped'){
                    $cnt = 0; //subproduct count
                    if ($request && !empty($request['super_group'])) {
                        foreach ($request['super_group'] as $subProductId => $qty){
                            if (!$qty)
                                continue;
                                
                            $request = new Varien_Object(); 
                            $request->setProduct($subProductId);
                            $request->setQty($qty);            
                            
                            $subProduct = Mage::getModel('catalog/product')
                                ->setStoreId(Mage::app()->getStore()->getId())
                                ->load($subProductId);
                            
                            // check if params are valid
                            $customOptions = $subProduct->getTypeInstance()->prepareForCart($request, $subProduct);
                            
                            // string == error during prepare cycle
                            if (is_string($customOptions)) {
                                $session->setRedirectUrl($product->getProductUrl());
                                Mage::throwException($customOptions);
                            }                
                            
                            $list->addItem($subProductId, $customOptions); 
                            
                            $cnt++;
                        }
                    }
                    
                    if (!$cnt) {
                        $session->setRedirectUrl($product->getProductUrl());
                        Mage::throwException($this->__('Please specify the product(s) quantity'));
                    }                

                } 
                else { //if product is not grouped
                    // check if params are valid
                    $customOptions = $product->getTypeInstance()->prepareForCart($request, $product);
                    
                    // string == error during prepare cycle
                    if (is_string($customOptions)) {
                        $session->setRedirectUrl($product->getProductUrl());
                        Mage::throwException($customOptions);
                    }                
                    
                    $list->addItem($productId, $customOptions); 
                }
                
                $referer = $session->getBeforeAmlistUrl();
                if ($referer){
                    $session->setBeforeAmlistUrl(null);
                }
                else {
                    $referer = $this->_getRefererUrl();
                }
 
	           $message = $this->__('Product has been successfully added to the folder. Click <a href="%s">here</a> to continue shopping', $referer);
               
	           $session->setRedirectUrl($product->getProductUrl());
               $session->addSuccess($message); 
               $this->_redirect('*/*/edit', array('id'=>$listId));
               
            } 
            catch (Exception $e) {
                $url =  $session->getRedirectUrl(true);
                if ($url) {
                    Mage::getSingleton('checkout/session')->addNotice($e->getMessage());
                    $this->getResponse()->setRedirect($url);
                }
                else {
                    $session->addError($this->__('There was an error while adding item to the list: %s', $e->getMessage()));  
                }
            }
            
	    }
        //$this->_redirect('*/*/');
	}
	
    /**
     * Save list's items
     */	
	public function updateAction() 
	{
        if (!$this->_validateFormKey()) {
            $this->_redirect('*/*/');
            return;
        }
        
        $listId = $this->getRequest()->getParam('list_id');

        $list  = Mage::getModel('amlist/list');
        $list->load($listId);
        if ($list->getCustomerId() != $this->_customerId){
            $this->_redirect('*/*/');
            return;
        }
        
        $post = $this->getRequest()->getPost();
        if ($post && isset($post['qty']) && is_array($post['qty'])) {
            foreach ($post['qty'] as $itemId => $qty) {
                $item = Mage::getModel('amlist/item')->load($itemId);
                if ($item->getListId() != $listId) {
                     continue;
                }
                try {
/*                    if (!$qty)
                        $item->delete();
                    else 
                    { */
                        $item->setQty(max(0.01, intVal($qty)));
                        
                        $newListId = isset($post['moveto'][$itemId]) ? $post['moveto'][$itemId] : 0;
                        if ($newListId ){
                            $item->setListId($newListId);
                        }

                        $item->save();
//                    }
                }
                catch (Exception $e) {
                    Mage::getSingleton('customer/session')->addError(
                        $this->__('Can not save item: %s.', $e->getMessage())
                    );
                }
            }
            Mage::getSingleton('customer/session')->addSuccess($this->__('Quantities have been successfully updated'));
        }
        $this->_redirect('*/*/edit', array('id'=>$listId));
	}
        
    public function cartAction() 
    {
        $messages           = array();
        $urls               = array();

        $listId = $this->getRequest()->getParam('list_id');
        $list = Mage::getModel('amlist/list')->load($listId);
        if (!$list->getId()) {
            $this->_redirect('*/*');
            return;
        }
        
        $isPost = $this->getRequest()->isPost();
        $selectedIds = $this->getRequest()->getParam('cb');
        if ($isPost && (!$selectedIds || !is_array($selectedIds))){
            Mage::getSingleton('customer/session')->addNotice(Mage::helper('amlist')->__('Please select products.'));
            $this->_redirect('*/*/edit', array('id'=>$list->getId()));
            return;            
        }        
        
        $quote = Mage::getSingleton('checkout/cart');
        foreach ($list->getItems() as $item) {
            if ($isPost && !in_array($item->getId(), $selectedIds))
                continue;
            try {
                $qty = $item->getQty();
                $product = Mage::getModel('catalog/product')
                    ->load($item->getProductId())
                    ->setQty(max(0.01, $qty));
                    
                $req = unserialize($item->getBuyRequest());
                $req['qty'] = $product->getQty();			

                $quote->addProduct($product, $req);
                
            } 
            catch (Exception $e) {
                $url = Mage::getSingleton('checkout/session')
                    ->getRedirectUrl(true);
                
                if ($url) {
                    $url = Mage::getModel('core/url')
                        ->getUrl('catalog/product/view', array(
                            'id' => $item->getProductId(),
                            'list_next' => 1
                        ));

                    $urls[]         = $url;
                    $messages[]     = $e->getMessage();
                } 
                else {
                    Mage::getSingleton('customer/session')->addNotice($e->getMessage());
                    $this->_redirect('*/*/edit', array('id'=>$list->getId()));
                    return;
                }
            }
        }
        $quote->save();
        
        if ($urls) {
            Mage::getSingleton('checkout/session')->addNotice(array_shift($messages));
            $this->getResponse()->setRedirect(array_shift($urls));

            Mage::getSingleton('checkout/session')->setAmlistPendingUrls($urls);
            Mage::getSingleton('checkout/session')->setAmlistPendingMessages($messages);
        }
        else {
             //$this->_redirectToCart();
             $this->_redirect('checkout/cart');
        }
    }
    
    // add all products from the cart to the list
    public function laterAction()
    {
 	    try {
    	    $list = Mage::getModel('amlist/list')			    
    	       ->setTitle('saved cart - ' . date('Y-m-d'))
    	       ->setCustomerId($this->_customerId)
    	       ->save();
    	    
    	    $quote = Mage::getSingleton('checkout/cart');
    	    foreach ($quote->getItems() as $item){
    	        
                $option = new Varien_Object();
                $option
                    ->setValue(serialize(array('qty'=>$item->getQty())))
                    ->setProductId($item->getProductId())     
                    ->setCode('info_buyRequest');
                      
                $request = new Varien_Object();  
                $request->setCustomOptions(array($option));     	        
    	        
    	        $list->addItem($item->getProductId(), array($request));
    	    }
    	    
    	    Mage::getSingleton('customer/session')->addSuccess(
    	       $this->__('The cart has been successfully saved')
    	    );    
			$this->_redirect('*/*/edit', array('id' => $list->getId()));
 	    } 
 	    catch (Exception $e) {
            Mage::getSingleton('customer/session')->addError(
    	       $this->__('There was an error while saving the cart: %s', $e->getMessage())
    	    ); 
            $this->_redirect('*/*/index');
 	    }
    }

	/**
	 *  Import favorite products from CSV file
	 */
	public function importCsvAction()
	{
		$request = $this->getRequest();

		if ($request->isPost()) {
			try {
				/**
				 * @var $helper Amasty_List_Helper_Data
				 */
				$helper = Mage::helper('amlist/data');
				/**
				 * @var $listModel Amasty_List_Model_List
				 */
				$listModel = Mage::getModel('amlist/list');

				//Init Uploader
				try {
					$uploader = new Varien_File_Uploader('file');
					$uploader->setAllowedExtensions(array('csv'));
					$uploader->setAllowRenameFiles(true);
					$uploader->setFilesDispersion(false);

					$folderPath = Mage::getBaseDir('media') . $helper::CSV_FOLDER_PATH;
					$fileName   = 'import_' . date('Y-m-d') . '.csv';

					// Upload the file
					$uploader->save($folderPath, $fileName);
				} catch (\Exception $e) {
					throw new Exception($this->__('check uploaded file'));
				}

				$fileName = $uploader->getUploadedFileName();

				$productData = $helper->parseProductCsv($fileName);
				$report = $listModel->createListFromCsv($productData, $this->_customerId);

				/*Add messages to session*/
				if (empty($report['notices'])) {
					$successMessages = array();
					$successMessages[] = $this->__('Import from CSV was successfully finished');
					$successMessages = array_merge($successMessages, $report['data']);

					Mage::getSingleton('customer/session')->addSuccess(implode('<br />', $successMessages));
				} else {
					Mage::getSingleton('customer/session')->addError(implode('<br />', $report['notices']));
				}

				if (! empty($report['errors'])) {
					Mage::getSingleton('customer/session')->addError(implode('<br />', $report['errors']));
				}
			} catch (Exception $e) {
				Mage::getSingleton('customer/session')->addError(
					$this->__('There was an error while uploading CSV: %s', $e->getMessage())
				);
			}
		}

		$this->_redirect('*/*/index');
	}
}