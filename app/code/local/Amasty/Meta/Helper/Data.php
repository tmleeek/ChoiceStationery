<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2008-2012 Amasty (http://www.amasty.com)
* @package Amasty_Meta
*/
class Amasty_Meta_Helper_Data extends Mage_Core_Helper_Abstract
{
	const ROBOTS_INDEX_FOLLOW     = 1;
	const ROBOTS_NOINDEX_FOLLOW   = 2;
	const ROBOTS_INDEX_NOFOLLOW   = 3;
	const ROBOTS_NOINDEX_NOFOLLOW = 4;

	const CONFIG_MAX_META_DESCRIPTION = 'ammeta/general/max_meta_description';
	const CONFIG_MAX_META_TITLE       = 'ammeta/general/max_meta_title';

	const DEFAULT_CHARSET = 'utf8';

	/** @var Amasty_Meta_Model_Config */
	protected $_configByUrl = null;
	protected $_configs = null;

	protected $_entityCollection = array();

	/**
	 * Get config by url
	 *
	 * @return Amasty_Meta_Model_Config
	 */
	public function getMetaConfigByUrl($path = '')
	{
		if (is_null($this->_configByUrl)) {
            $storeId = Mage::app()->getStore()->getId();
            if ($path) {
                $urls = array($path);
            } else {
                $request = new Zend_Controller_Request_Http();
                $rawPath = $request->setRequestUri()->getRequestUri();

                $pathInfo = Mage::app()->getRequest()->getOriginalPathInfo();

                $urls = array($pathInfo, $rawPath);
            }

			$data = Mage::getModel('ammeta/config')->getConfigByUrl($urls, $storeId);
			if (! empty($data)) {
				$data               = $data->getData();
				$this->_configByUrl = array();

				$customUrlMapping = $this->getUrlColumnsMapping();

				foreach ($data as $item) {
					foreach ($customUrlMapping as $attrCode => $column) {
						if (! isset($this->_configByUrl[$attrCode])
							&& ! empty($item[$column])
							&& trim($item[$column]) != ''
						) {
							if ($column == 'custom_robots') {
								foreach ($this->getRobotOptions() as $itemRobot) {
									if ($itemRobot['value'] == $item[$column]) {
										$item[$column] = $itemRobot['label'];
										break;
									}
								}
							}

							if ($column == 'custom_meta_description') {
								$item[$column] = mb_substr(
									$item[$column],
									0,
									$this->getMaxMetaDescriptionLength(),
									self::DEFAULT_CHARSET
								);
							}

							if ($column == 'custom_meta_title') {
								$item[$column] = mb_substr(
									$item[$column],
									0,
									$this->getMaxMetaTitleLength(),
									self::DEFAULT_CHARSET
								);
							}

							$this->_configByUrl[$attrCode] = $item[$column];
						}
					}
				}
			} else {
				$this->_configByUrl = false;
			}
		}

		return $this->_configByUrl;
	}

	/**
	 *  Parses template wth optional parts, uses _parseAttributes
	 *
	 * @param $tpl
	 *
	 * @return mixed|string
	 */
	public function parse($tpl, $isUrl = false)
	{
		// replase attribute values if possible
		$tpl = $this->_parseAttributes($tpl, $isUrl);

		// handle optional parts
		$tpl = preg_replace_callback(
			'/\[.*?\]/',
			create_function('$m', 'if(strpos($m[0], "}")) return ""; return substr($m[0],1,-1);'),
			$tpl
		);

		// remove non-processed variables
		$tpl = preg_replace('/{([a-z\_\|0-9]+)}/', '', $tpl);

		return $tpl;
	}


	/**
	 *  Parses template and insert attribute values
	 *
	 * @param $tpl
	 *
	 * @return mixed
	 */
	protected function _parseAttributes($tpl, $isUrl)
	{
		$vars = array();
		preg_match_all('/{([a-z\_\|0-9]+)}/', $tpl, $vars);
		if (! $vars[1]) {
			return $tpl;
		}
		$vars = $vars[1];

		foreach ($vars as $codes) {
			$value = '';
			foreach (explode('|', $codes) as $code) {
				foreach ($this->_entityCollection as $object) {
					$value = $this->_getValue($object, $code, $isUrl);
					if ($value) {
						break 2; // we have found the first non-empty occurense.
					}
				}
			}
			if ($value)
				$tpl = str_replace('{' . $codes . '}', $value, $tpl);
		}

		return $tpl;
	}

	/**
	 * Gets attribute value by its code. Support custom params, see manual for details
	 *
	 * @param $p
	 * @param $code
	 *
	 * @return mixed|null|string
	 */
	protected function _getValue($p, $code, $isUrl)
	{
		$value = $code;
		$store = null;

		if ($p instanceof Mage_Catalog_Model_Product || $p instanceof Mage_Catalog_Model_Category) {
			$value = $this->_getValueByProduct($p, $code);
			$store = Mage::app()->getStore($p->getStoreId());
		} else {
			$value = $p->getData($code);
		}

		if (!is_string($value)) {
			return '';
		}

		// remove tags
		$value = strip_tags($value);
		// remove spases
		$value = preg_replace('/\r?\n/', ' ', $value);
		$value = preg_replace('/\s{2,}/', ' ', $value);

        if ($isUrl)
        {
            $value = $this->transliterate($value);
            $value = str_replace('+', 'plus', $value);
            $value = preg_replace('#[^\w\s\d-_.~/]#', '', $value);
        }
        else
        {
            // convert possible special codes like '<' to safe html codes
            $value = html_entity_decode($value, ENT_QUOTES, 'UTF-8');
            $value = htmlspecialchars($value);
        }
		// check if price = 0.00
		if ($store && $value === $store->convertPrice(0, true, false)) {
			$value = '';
		}

		return $value;
	}

    protected function _getProductCategory($p)
    {
        if ($p instanceof Mage_Catalog_Model_Category)
            return $p;

        $collection = $p->getCategoryCollection();
        $collection->getSelect()->order('LENGTH(path) DESC');

        if (0 < $collection->getSize()) {
            foreach ($collection as $item) {
                $category = Mage::getModel('catalog/category')->load($item->getId());
                if ($category->getIsActive()) {
                    return $category;
                }
            }
        }
        
        return false;
    }

	protected function _getValueByProduct($p, $code)
	{
        if ($p instanceof Mage_Catalog_Model_Category)
            return $p->getData($code);

		$store = Mage::app()->getStore($p->getStoreId());

		switch ($code) {
			case 'category':
				$value    = '';
				$category = $this->_getProductCategory($p);
				if ($category) {
					$value = $category->getName();
				}
				break;
            case 'parent_category':
                $value    = '';
                /** @var Mage_Catalog_Model_Category $category */

                $category = $this->_getProductCategory($p);

                if ($category) {
                    $value = $category->getParentCategory()->getName();
                }
                break;
			case 'categories':
				$separator = (string) Mage::getStoreConfig('catalog/seo/title_separator');
				$separator = ' ' . $separator . ' ';
				$title     = array();
                if (Mage::getStoreConfig('ammeta/product/no_breadcrumbs', $store)) {
                    $categoryIds = $p->getCategoryIds();
                    if ($categoryIds) {
                        $categoryCollection = Mage::getModel('catalog/category')->getCollection();
                        $categoryCollection->addIdFilter($categoryIds);
                        $categoryCollection->addNameToResult();
                        if ($categoryCollection->getSize() > 0) {
                            foreach ($categoryCollection as $cat) {
                                $title[] = $cat->getName();
                            }
                        }
                    }
                    $value = join($separator, $title);
                } else {
                    $path = Mage::helper('catalog')->getBreadcrumbPath();
                    foreach ($path as $breadcrumb) {
                        $title[] = $breadcrumb['label'];
                    }
                    array_pop($title);
                    $value = join($separator, array_reverse($title));
                }
				break;
			case 'store_view':
				$value = $store->getName();
				break;
			case 'store':
				$value = $store->getGroup()->getName();
				break;
			case 'website':
				$value = $store->getWebsite()->getName();
				break;
			case 'domain':
				$value = $_SERVER['HTTP_HOST'];
				break;
			case 'price':
				$value = $store->convertPrice($p->getPrice(), true, false);
				break;
			case 'special_price':
				$value = $store->convertPrice($p->getData($code), true, false);
				break;
			case 'final_price':
				$value = $store->convertPrice(Mage::helper('tax')->getPrice($p, $p->getFinalPrice()), true, false);
				break;
			case 'final_price_incl_tax':
				$value = $store->convertPrice(Mage::helper('tax')->getPrice($p, $p->getFinalPrice(), true), true, false
				);
				break;
			case 'startingfrom_price':
				$minimalPrice = $this->_getMinimalPrice($p);
				$value        = $store->convertPrice($minimalPrice, true, false);
				break;
			case 'startingto_price':
				$maximalPrice = $this->_getMaximalPrice($p);
				$value        = $store->convertPrice($maximalPrice, true, false);
				break;

			case 'current_page':
				$page  = Mage::app()->getRequest()->getParam('p');
				$value = $page < 1 ? NULL : intVal($page);
				break;
            case 'product_tags':
                $tags = Mage::getResourceModel('tag/tag_collection')
                    ->addPopularity()
                    ->addStatusFilter(Mage_Tag_Model_Tag::STATUS_APPROVED)
                    ->addProductFilter($p->getId())
                    ->setFlag('relation', true)
                    ->addStoreFilter(Mage::app()->getStore()->getId())
                    ->setActiveFilter()
                    ->load()
                ;

                $tagNames = $tags->getColumnValues('name');

                if (!empty($tagNames)) {
                    $value = implode(', ', $tagNames);
                }
                else {
                    $value = '';
                }

                break;

			default:
                if (!$code)
                    return '';

				$value = $p->getData($code);
				if (is_numeric($value)) {
					// flat enabled
					if ($p->getData($code . '_value')) {
						$value = $p->getData($code . '_value');
					} else {
						$attr = $p->getResource()->getAttribute($code);
						if ($attr) { // type dropdown
                            $attr->setStoreId($p->getStoreId());
							$optionText = $attr->getSource()->getOptionText($value);
							$value      = $optionText ? $optionText : $value;
						}
					}
				} // multiple select
				elseif (preg_match('/^[0-9,]+$/', $value)) {
					$attr = $p->getResource()->getAttribute($code);
					if ($attr) {
						$ids   = explode(',', $value);
						$value = '';
						foreach ($ids as $id) {
							$value .= $attr->getSource()->getOptionText($id) . ', ';
						}
						$value = substr($value, 0, - 2);
					}
				}

		} // end switch

		return $value;
	}

	/**
	 * Genarates tree of all categories
	 *
	 * @return array sorted list category_id=>title
	 */
	public function getTree($asHash = false)
	{
		$tree   = array();

		$collection = Mage::getModel('catalog/category')
			->getCollection()->addNameToResult();

		$pos = array();
		foreach ($collection as $cat) {
			$path = explode('/', $cat->getPath());
			if ($cat->getLevel()) {
				$tree[$cat->getId()] = array(
					'label' => str_repeat('--', $cat->getLevel()) . $cat->getName(),
					'value' => $cat->getId(),
					'path'  => $path,
				);
			}
			$pos[$cat->getId()] = $cat->getPosition();
		}

		foreach ($tree as $catId => $cat) {
			$order = array();
			foreach ($cat['path'] as $id) {
				if (isset($pos[$id])) {
					$order[] = $pos[$id];
				}
			}
			$tree[$catId]['order'] = $order;
		}

		usort($tree, array($this, 'compare'));
		if ($asHash) {
			$hash = array();
			foreach ($tree as $v) {
				$hash[$v['value']] = $v['label'];
			}
			$tree = $hash;
		}

		if (! empty($tree)) {
			reset($tree);
			$firstKey = key($tree);
			if ($asHash) {
				$firstElement = current($tree);
				$tree         = array(0 => $firstElement) + $tree;
				unset($tree[$firstKey]);
			} else {
				$tree[$firstKey]['value'] = 1;
			}
		}

		return $tree;
	}

	/**
	 * Compares category data. Must be public as used as a callback value
	 *
	 * @param array $a
	 * @param array $b
	 *
	 * @return int 0, 1 , or -1
	 */
	public function compare($a, $b)
	{
		foreach ($a['path'] as $i => $id) {
			if (! isset($b['path'][$i])) {
				// B path is shorther then A, and values before were equal
				return 1;
			}
			if ($id != $b['path'][$i]) {
				// compare category positions at the same level
				$p  = isset($a['order'][$i]) ? $a['order'][$i] : 0;
				$p2 = isset($b['order'][$i]) ? $b['order'][$i] : 0;

				return ($p < $p2) ? - 1 : 1;
			}
		}

		// B path is longer or equal then A, and values before were equal
		return ($a['value'] == $b['value']) ? 0 : - 1;
	}

	protected function _getMinimalPrice($product)
	{
		$minimalPrice = Mage::helper('tax')->getPrice($product, $product->getMinimalPrice(), true);
		if ($product->isGrouped()) {
			$associatedProducts = $product->getTypeInstance(true)->getAssociatedProducts($product);
			foreach ($associatedProducts as $item) {
				$temp = Mage::helper('tax')->getPrice($item, $item->getFinalPrice(), true);
				if (is_null($minimalPrice) || $temp < $minimalPrice) {
					$minimalPrice = $temp;
				}
			}
		}
        else if ($product->getTypeId() == 'bundle'){
            list($minimalPrice, $maximalPrice) = $product->getPriceModel()->getTotalPrices($product, null, null, false);
        }

		return $minimalPrice;
	}

	protected function _getMaximalPrice($product)
	{
		$maximalPrice = 0;
		if ($product->isGrouped()) {
			$associatedProducts = $product->getTypeInstance(true)->getAssociatedProducts($product);
			foreach ($associatedProducts as $item) {
				if ($qty = $item->getQty() * 1) {
					$maximalPrice += $qty * Mage::helper('tax')->getPrice($item, $item->getFinalPrice(), true);
				} else {
					$maximalPrice += Mage::helper('tax')->getPrice($item, $item->getFinalPrice(), true);
				}
			}
		}
        else if ($product->getTypeId() == 'bundle'){
            list($minimalPrice, $maximalPrice) = $product->getPriceModel()->getTotalPrices($product, null, null, false);
        }
		if (! $maximalPrice) {
			$maximalPrice = Mage::helper('tax')->getPrice($product, $product->getFinalPrice(), true);
		}

		return $maximalPrice;
	}

	public function getRobotOptions()
	{
		return array(
			array('label' => 'INDEX, FOLLOW', 'value' => self::ROBOTS_INDEX_FOLLOW),
			array('label' => 'NOINDEX, FOLLOW', 'value' => self::ROBOTS_NOINDEX_FOLLOW),
			array('label' => 'INDEX, NOFOLLOW', 'value' => self::ROBOTS_INDEX_NOFOLLOW),
			array('label' => 'NOINDEX, NOFOLLOW', 'value' => self::ROBOTS_NOINDEX_NOFOLLOW)
		);
	}

	public function getUrlColumnsMapping()
	{
		return array(
			'meta_title'           => 'custom_meta_title',
			'meta_description'     => 'custom_meta_description',
			'meta_keyword'         => 'custom_meta_keywords',
			'meta_keywords'        => 'custom_meta_keywords',
			'meta_robots'          => 'custom_robots',
			'custom_canonical_url' => 'custom_canonical_url',
			'custom_h1_tag'        => 'custom_h1_tag'
		);
	}

	/**
	 * @param $html
	 * @param $newText
	 *
	 * @return $this
	 */
	public function replaceH1Tag(&$html, $newText)
	{
		$html = preg_replace('/(\<h1.*?\>).+?(\<\/h1\>)/is', "\${1}" . $newText . '$2', $html);

		return $this;
	}

	/**
	 * @param $html
	 * @param array $attributes
	 *
	 * @return bool
	 */
	public function replaceImageData(&$html, $attributes = array())
	{
		$domQuery = new Zend_Dom_Query($html);
		$results  = $domQuery->query('.category-image img');

		if (! count($results)) {
			return false;
		}

		foreach ($results as $result) {
			foreach ($attributes as $tagName => $tagValue) {
				$result->setAttribute($tagName, $tagValue);
			}
			break;
		}

		$html = $results->getDocument()->saveHTML();
	}

	public function getMaxMetaDescriptionLength()
	{
		$value = (int) Mage::getStoreConfig(self::CONFIG_MAX_META_DESCRIPTION);

		return $value ? $value : 500;
	}

	public function getMaxMetaTitleLength()
	{
		$value = (int) Mage::getStoreConfig(self::CONFIG_MAX_META_TITLE);

		return $value ? $value : 250;
	}

	/**
	 * @param Varien_Object $object
	 *
	 * @return $this
	 */
	public function addEntityToCollection(Varien_Object $object)
	{
		$this->_entityCollection[] = $object;

		return $this;
	}

	/**
	 * @return $this
	 */
	public function cleanEntityToCollection()
	{
		$this->_entityCollection = array();

		return $this;
	}

    public function transliterate($string)
    {
        $replace = array(
            "а"=>"a","А"=>"a",
            "б"=>"b","Б"=>"b",
            "в"=>"v","В"=>"v",
            "г"=>"g","Г"=>"g",
            "д"=>"d","Д"=>"d",
            "е"=>"e","Е"=>"e",
            "ж"=>"zh","Ж"=>"zh",
            "з"=>"z","З"=>"z",
            "и"=>"i","И"=>"i",
            "й"=>"y","Й"=>"y",
            "к"=>"k","К"=>"k",
            "л"=>"l","Л"=>"l",
            "м"=>"m","М"=>"m",
            "н"=>"n","Н"=>"n",
            "о"=>"o","О"=>"o",
            "п"=>"p","П"=>"p",
            "р"=>"r","Р"=>"r",
            "с"=>"s","С"=>"s",
            "т"=>"t","Т"=>"t",
            "у"=>"u","У"=>"u",
            "ф"=>"f","Ф"=>"f",
            "х"=>"h","Х"=>"h",
            "ц"=>"c","Ц"=>"c",
            "ч"=>"ch","Ч"=>"ch",
            "ш"=>"sh","Ш"=>"sh",
            "щ"=>"sch","Щ"=>"sch",
            "ъ"=>"","Ъ"=>"",
            "ы"=>"y","Ы"=>"y",
            "ь"=>"","Ь"=>"",
            "э"=>"e","Э"=>"e",
            "ю"=>"yu","Ю"=>"yu",
            "я"=>"ya","Я"=>"ya",
            "і"=>"i","І"=>"i",
            "ї"=>"yi","Ї"=>"yi",
            "є"=>"e","Є"=>"e",
            "Ä"=>"ae","ä"=>"ae",
            "Ü"=>"ue","ü"=>"ue",
            "Ö"=>"oe","ö"=>"oe",
            "ß"=>"ss"
        );

        return iconv("UTF-8", "UTF-8//IGNORE", strtr($string, $replace));
    }
}
