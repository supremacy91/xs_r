<?php

namespace Biztech\Manufacturer\Block\Manufacturer;
use Biztech\Manufacturer\Helper\Data;
use Biztech\Manufacturer\Model\Manufacturer;
use Biztech\Manufacturer\Model\Manufacturertext;
use Magento\Catalog\Block\Product\AbstractProduct;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Config;

class View extends AbstractProduct {
	protected $_defaultToolbarBlock = 'Biztech\Manufacturer\Block\Manufacturer';
	protected $_scopeConfig;
	protected $_helperData;
	protected $_storeManager;
	protected $_manufacturerCollection;
	protected $_manufacturerTextCollection;
	protected $_productCollection;
	protected $_eavConfig;
	protected $_defaultColumnCount = 4;
	protected $_columnCountLayoutDepend = [];
	protected $_collection;

	public function __construct(
		Context $context,
		Data $helperData,
		Manufacturer $manufacturerCollection,
		Manufacturertext $manufacturerTextCollection,
		Product $productCollection,
		Config $eavConfig,
		array $data = []
		) {
		$this->_helperData = $helperData;
		$this->_storeManager = $context->getStoreManager();
		$this->_manufacturerCollection = $manufacturerCollection;
		$this->_manufacturerTextCollection = $manufacturerTextCollection;
		$this->_productCollection = $productCollection;
		$this->_eavConfig = $eavConfig;
		parent::__construct($context, $data);
	}

	public function getConfigValue($configPath) {
		return $this->_scopeConfig->getValue($configPath);
	}

	protected function _beforeToHtml() {
		$toolbar = $this->getToolbarBlock();

		// called prepare sortable parameters
		$collection = $this->getProductCollection();
		// $collection->addAttributeToFilter('visibility', [['neq' => 1]]);

		// use sortable parameters
		$orders = $this->getAvailableOrders();
		if ($orders) {
			$toolbar->setAvailableOrders($orders);
		}
		$sort = $this->getSortBy();
		if ($sort) {
			$toolbar->setDefaultOrder($sort);
		}
		$dir = $this->getDefaultDirection();
		if ($dir) {
			$toolbar->setDefaultDirection($dir);
		}
		$modes = $this->getModes();
		if ($modes) {
			$toolbar->setModes($modes);
		}

		// set collection to toolbar and apply sort
		$toolbar->setCollection($collection);

		$this->setChild('toolbar', $toolbar);

		$this->getProductCollection()->load();

		return parent::_beforeToHtml();
	}

	protected function _prepareLayout()
	{
	    parent::_prepareLayout();
	    $this->pageConfig->getTitle()->set(__($this->getManufacturer()->getManufacturerName()));
	    if ($this->getProductCollection()) {
	        $pager = $this->getLayout()->createBlock(
	            'Magento\Theme\Block\Html\Pager',
	            'fme.news.pager'
	        )->setAvailableLimit(array(9=>9,15=>15,30=>30))->setShowPerPage(true)->setCollection(
	            $this->getProductCollection()
	        );
	        $this->setChild('pager', $pager);
	        $this->getProductCollection()->load();
	    }
	    return $this;
	}

	public function getPagerHtml()
	{
	    return $this->getChildHtml('pager');
	}


	public function getManufacturer() {
		$store = $this->_storeManager->getStore()->getId();
		$_manufacturerId = $this->getRequest()->getParam('id');
		$this->_manufacturerCollection->load($_manufacturerId, 'manufacturer_id');

		$model_text = $this->_manufacturerTextCollection
		->getCollection()
		->addFieldToFilter('store_id', $store)
		->addFieldToFilter('manufacturer_id', $_manufacturerId)
		->getData();
		if (count($model_text) !== 0) {
			$text_data = $model_text[0];
			$this->_manufacturerCollection = $this->_manufacturerCollection->addData($text_data);

			if ($this->_manufacturerCollection->getData('status')) {
				return $this->_manufacturerCollection;
			}
		}
		return null;
	}

	public function getPreparedCollection() {
		return $this->_prepareCollection();
	}

	protected function _prepareCollection() {
		$collection = $this->_manufacturerCollection->getCollection()->addFieldToFilter("status", ["eq" => 1]);
		$collection->setPageSize(1);
		return $collection;
	}

	public function getProductCollection() {

		// $requestOb = $objectManager->get('Magento\Framework\App\Request\Http');
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$eavAttribute = $objectManager->get('\Magento\Eav\Model\ResourceModel\Entity\Attribute');
		$requestOb = $objectManager->get('Magento\Framework\App\RequestInterface');
		$filter = $requestOb->getParams();

		$manufacturer = $this->getManufacturer();
		if ($manufacturer != null) {
			$collection = $this->_productCollection->getCollection();
			
			$attribute = $this->getConfigValue('manufacturer/general/brandlist_attribute');
			$table = $this->_eavConfig->getAttribute('catalog_product', $attribute)->getBackend()->getTable();
			$attributeData = $this->_eavConfig->getAttribute('catalog_product', $attribute);
			$attributeId = $attributeData->getAttributeId();
			$options = $attributeData->getSource()->getAllOptions();
			$manufId = '';
			foreach($options as $key => $data) {
				if(trim($data['label']) == trim($manufacturer->getBrandName())) {
					$manufId = $data['value'];
					break;
				}
			}
			if($manufId == '') {
				$manufId = $manufacturer->getManufacturerId();
			}
			$collection->addAttributeToSelect('*')
			->addAttributeToFilter('visibility', [['eq' => 1], ['eq' => 4]])
			->addFieldToFilter('status', ['eq' => 1])
			->addAttributeToFilter($attribute, $manufId)
			->addMinimalPrice()
			->addFinalPrice()
			->addTaxPercents();

			$collection->joinField('qty',
				'cataloginventory_stock_item',
				'qty',
				'product_id=entity_id',
				'{{table}}.stock_id=1',
				'left'
				);
			$collection->joinField('parent_id',
				'catalog_product_super_link',
				'parent_id',
				'parent_id=entity_id',
				NULL,
				'left'
				);

			$attributes = $this->getRequest()->getParams();
			foreach ($attributes as $attributeCode => $attributeValue) {
				$attributesFilter[$attributeCode] = $attributeValue;
			}
			foreach ($attributesFilter as $code => $value) {
				if ($code == 'id' || $code == 'p' || $code == 'product_list_order' || $code == 'product_list_dir' || $code == 'product_list_mode' || $code == 'product_list_limit') {
					continue;
				} elseif ($code == 'cat') {
					/*$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
						$productCollection = $objectManager->create('Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');
						$categoryCode = $productCollection->create()
							->setStoreId($this->_storeManager->getStore()->getId())
					*/
					/*var_dump($categoryCode->getData());
					die();*/
					/*var_dump(get_class_methods($collection));
					die();*/
					$collection->addCategoryIds(4);
				} elseif ($code == 'price') {
					$priceData = explode('-', $value);
					$fromPrice = $priceData[0];
					$toPrice = $priceData[1];
					if (!$fromPrice) {
						$collection->addAttributeToFilter(
							[['attribute' => $code, 'lteq' => $toPrice], ['attribute' => 'special_price', 'lteq' => $toPrice]]);
					}
					if (!$toPrice) {
						$collection->addAttributeToFilter(
							[['attribute' => $code, 'lteq' => $fromPrice], ['attribute' => 'special_price', 'lteq' => $fromPrice]]);
					}
					if ($toPrice && $fromPrice) {
						$collection->addAttributeToFilter(
							[['attribute' => $code, ['from' => $fromPrice, 'to' => $toPrice]], ['attribute' => 'special_price', ['from' => $fromPrice, 'to' => $toPrice]]]);
					}
				} else {
					if($code == 'color' || $code == 'size')
					{
						$attributeId = $eavAttribute->getIdByCode('catalog_product',$code);
						$joinConditions = 'at_parent_id.product_id = '.$code.'.entity_id AND '.$code.".attribute_id=".$attributeId.' AND '.$code.'.store_id=0';
						$collection->getSelect()
								    ->join(
								        array($code => 'catalog_product_entity_int'),
								        $joinConditions,
								        []
								    )->columns('')
								    ->where($code.".value=".$value);
					} else {
						$collection->addAttributeToFilter($code, $value);
					}
				}
			}
			$collection->distinct(true);
		}
			//get values of current page. if not the param value then it will set to 1
		$page=($this->getRequest()->getParam('p'))? $this->getRequest()->getParam('p') : 1;
		$product_list_dir =($this->getRequest()->getParam('product_list_dir') == 'desc')? 'ASC' : 'DESC';
		$product_list_order =($this->getRequest()->getParam('product_list_order'))? $this->getRequest()->getParam('product_list_order') : '';
		//get values of current limit. if not the param value then it will set to 1
		$collection->addAttributeToFilter('visibility', [['neq' => 1]]);
		$collection->setOrder('created_at', $product_list_dir);
		if ($this->getRequest()->getParam('product_list_limit') == 'all') {
			$pageSizeCount = count($collection);
			$pageSize=$pageSizeCount;		
		} else if ($this->getRequest()->getParam('product_list_limit')) {
			$pageSize=$this->getRequest()->getParam('product_list_limit');		
		} else{
			$pageSize=30;		
		}
		// $pageSize=($this->getRequest()->getParam('product_list_limit'))? $this->getRequest()->getParam('product_list_limit') : 9;		
		$collection->setPageSize($pageSize);
		$collection->setCurPage($page);
		return $collection; 
	}

	/**
	 * Retrieve Toolbar block
	 *
	 * @return \Magento\Catalog\Block\Product\ProductList\Toolbar
	 */
	public function getToolbarBlock() {
		$blockName = $this->getToolbarBlockName();
		if ($blockName) {
			$block = $this->getLayout()->getBlock($blockName);
			if ($block) {
				return $block;
			}
		}
		$block = $this->getLayout()->createBlock($this->_defaultToolbarBlock, uniqid(microtime()));
		return $block;
	}

	public function getToolbarHtml() {
		return $this->getChildHtml('toolbar');
	}

	/**
	 * Retrieve current view mode
	 *
	 * @return string
	 */
	public function getMode() {
		return $this->getChildBlock('toolbar')->getCurrentMode();
	}

	public function getAvailableOrders() {
		return ['position' => 'Position', 'brand_name' => 'Name'];
	}

	public function getDefaultDirection() {
		return 'asc';
	}

	public function getSortBy() {
		return 'manufacturer_id';
	}

	public function getManufacturerUrl($manufacturer) {
		$url = $this->getUrl('merken/' . $manufacturer->getUrlKey(), []);
		return $url;
	}

	public function getAdditionalHtml() {
		return $this->getChildHtml('additional');
	}

}
