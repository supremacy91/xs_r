<?php

namespace Biztech\Manufacturer\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper {

	const XML_PATH_ENABLED 	= 'manufacturer/general/enabled';
	const XML_PATH_INSTALLED = 'manufacturer/activation/installed';
	const XML_PATH_DATA      = 'manufacturer/activation/data';
	const XML_PATH_WEBSITES  = 'manufacturer/activation/websites';
	const XML_PATH_EN        = 'manufacturer/activation/en';
	const XML_PATH_KEY       = 'manufacturer/activation/key';

	protected $_logger;
	protected $_moduleList;
	protected $_zend;
	protected $_resourceConfig;
	protected $_encryptor;
	protected $_web;
	protected $_objectManager;
	protected $_coreConfig;
	protected $_dir;
	protected $_storeManager;
	protected $_manufacturer;
	protected $_scopeConfig;
	protected $_productCollection;
	protected $_eavConfig;
	protected $_storeConfig;

	public function __construct(
		\Biztech\Manufacturer\Model\Config $storeConfig,
		\Magento\Catalog\Model\Product $productCollection,
		\Magento\Eav\Model\Config $eavConfig,
		\Biztech\Manufacturer\Model\Manufacturer $manufacturer,
		\Magento\Framework\App\Helper\Context $context,
		\Magento\Framework\Encryption\EncryptorInterface $encryptor,
		\Magento\Framework\Module\ModuleListInterface $moduleList,
		\Zend\Json\Json $zend,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Config\Model\ResourceModel\Config $resourceConfig,
		\Magento\Framework\ObjectManagerInterface $objectmanager,
		\Magento\Framework\App\Config\ReinitableConfigInterface $coreConfig,
		\Magento\Store\Model\Website $web,
		\Magento\Framework\Filesystem\DirectoryList $dir,
		\Magento\Backend\Helper\Data $backendHelper,
		\Magento\Framework\App\ResourceConnection $resource
		){
		$this->_zend              = $zend;
		$this->_logger            = $context->getLogger();
		$this->_moduleList        = $moduleList;
		$this->_storeManager      = $storeManager;
		$this->_resourceConfig    = $resourceConfig;
		$this->_encryptor         = $encryptor;
		$this->_web               = $web;
		$this->_objectManager     = $objectmanager;
		$this->_coreConfig        = $coreConfig;
		$this->_dir               = $dir;
		$this->_manufacturer      = $manufacturer;
		$this->scopeConfig = $context->getScopeConfig();
		$this->_productCollection = $productCollection;
		$this->_eavConfig         = $eavConfig;
		$this->_storeConfig       = $storeConfig;
		parent::__construct($context);
	}

	public function buildHttpQuery($query) {
		$query_array = [];
		foreach ($query as $key => $key_value)
			$query_array[] = $key.'='.urlencode($key_value);
		return implode('&', $query_array);
	}

	public function parseXml($xmlString) {
		libxml_use_internal_errors(true);
		$xmlObject = simplexml_load_string($xmlString);
		$result    = [];
		if (!empty($xmlObject)) {
			$this->convertXmlObjToArr($xmlObject, $result);
		}

		return $result;
	}

	public function convertXmlObjToArr($obj, &$arr) {
		$children = $obj->children();
		$executed = false;
		foreach ($children as $elementName => $node) {
			if (is_array($arr) && array_key_exists($elementName, $arr)) {
				if (is_array($arr[$elementName]) && array_key_exists(0, $arr[$elementName])) {
					$i = count($arr[$elementName]);
					$this->convertXmlObjToArr($node, $arr[$elementName][$i]);
				} else {
					$tmp                  = $arr[$elementName];
					$arr[$elementName]    = [];
					$arr[$elementName][0] = $tmp;
					$i                    = count($arr[$elementName]);
					$this->convertXmlObjToArr($node, $arr[$elementName][$i]);
				}
			} else {
				$arr[$elementName] = [];
				$this->convertXmlObjToArr($node, $arr[$elementName]);
			}
			$executed = true;
		}
		if (!$executed && $children->getName() == "") {
			$arr = (String) $obj;
		}
		return;
	}

	public function getAllStoreDomains() {
		$domains = [];
		foreach ($this->_storeManager->getWebsites() as $website) {
			$url = $website->getConfig('web/unsecure/base_url');
			if ($domain = trim(preg_replace('/^.*?\/\/(.*)?\//', '$1', $url))) {
				$domains[] = $domain;
			}
			$url = $website->getConfig('web/secure/base_url');
			if ($domain = trim(preg_replace('/^.*?\/\/(.*)?\//', '$1', $url))) {
				$domains[] = $domain;
			}
		}
		return array_unique($domains);
	}

	public function getDataInfo() {
		$data = $this->scopeConfig->getValue(self::XML_PATH_DATA, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		return json_decode(base64_decode($this->_encryptor->decrypt($data)));
	}

	public function getAllWebsites() {
		$value = $this->scopeConfig->getValue(self::XML_PATH_INSTALLED, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		if (!$value) {
			return [];
		}
		$data = $this->scopeConfig->getValue(self::XML_PATH_DATA, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		$web  = $this->scopeConfig->getValue(self::XML_PATH_WEBSITES, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		//$websites = explode(',', str_replace($data, '', $this->_encryptor->decrypt($web)));
		$websites = explode(',', str_replace($data, '', $this->_encryptor->decrypt($web)));
		$websites = array_diff($websites, [""]);
		return $websites;
	}

	public function getFormatUrl($url) {
		$input = trim($url, '/');
		if (!preg_match('#^http(s)?://#', $input)) {
			$input = 'http://'.$input;
		}
		$urlParts = parse_url($input);
		if (isset($urlParts['path'])) {
			$domain = preg_replace('/^www\./', '', $urlParts['host'].$urlParts['path']);
		} else {
			$domain = preg_replace('/^www\./', '', $urlParts['host']);
		}
		return $domain;
	}

	public function isEnabled() {
		$websiteId = $this->_storeManager->getStore()->getWebsite()->getId();
		$isenabled = $this->scopeConfig->getValue(self::XML_PATH_ENABLED, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		if ($isenabled) {
			if ($websiteId) {
				$websites = $this->getAllWebsites();
				$key = $this->scopeConfig->getValue(self::XML_PATH_KEY, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
				if ($key == null || $key == '') {
					return false;
				} else {
					$en = $data = $this->scopeConfig->getValue(self::XML_PATH_EN, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
					if ($isenabled && $en && in_array($websiteId, $websites)) {
						return true;
					} else {
						return false;
					}
				}
			} else {
				$en = $en = $data = $this->scopeConfig->getValue(self::XML_PATH_EN, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
				if ($isenabled && $en) {
					return true;
				}
			}
		}
	}

	//Manufacturer Helper Functions

	public function getConfigValue($path) {
		$store = $this->_storeManager->getStore()->getId();
		return $this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store);
	}

	public function getConfig() {
		return $this->_storeConfig;
	}

	/*public function getImageUrl($manufacturer_name, $fileName, $resize_type = null) {
		$replace = ["'", " ", "!", "%", "@", "$", '#'];
		$new_manufacturer_name = str_replace($replace, "_", $manufacturer_name);

		$imageUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'Manufacturer/'.$new_manufacturer_name.'/'.$fileName;
		if( isset($resize_type) ){
			$imageUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'Manufacturer/'.$new_manufacturer_name.'/resized/'. $resize_type .'/'.$fileName;
		}
		return $imageUrl;
	}*/

	public function getImageUrl($fileName, $resize_type = null){

		$imageUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'Manufacturer'.$fileName;

		if( !is_null($resize_type) || isset($resize_type) ){
			$imageUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'Manufacturer'.DIRECTORY_SEPARATOR.'resized'.DIRECTORY_SEPARATOR.$resize_type .$fileName;
		}

		if( !file_exists($imageUrl) ){
			$imageUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'Manufacturer'.$fileName;
		}

		return $imageUrl;
	}

	/*public function getManufacturerImageUploadPath($manufacturer_name, $fileName = '', $resize_type = '') {
		$replace = ["'", " ", "!", "%", "@", "$", '#'];
		$new_manufacturer_name = str_replace($replace, "_", $manufacturer_name);
		$path                  = $this->_dir->getPath('media').DIRECTORY_SEPARATOR.'Manufacturer'.DIRECTORY_SEPARATOR.$new_manufacturer_name.DIRECTORY_SEPARATOR;
		if ($fileName) {
			$path .= 'resized'.DIRECTORY_SEPARATOR.$resize_type.DIRECTORY_SEPARATOR.$fileName;
		}
		return $path;
	}*/

	public function getManufacturerImageUploadPath($fileName = '', $resize_type = '', $dispersion = ''){
		/*$replace = ["'", " ", "!", "%", "@", "$", '#'];
		$new_manufacturer_name = str_replace($replace, "_", $manufacturer_name);
		$path                  = $this->_dir->getPath('media').DIRECTORY_SEPARATOR.'Manufacturer'.DIRECTORY_SEPARATOR.$new_manufacturer_name.DIRECTORY_SEPARATOR;*/
		$path                  = $this->_dir->getPath('media').DIRECTORY_SEPARATOR.'Manufacturer';
		if ($fileName) {
			$path .= DIRECTORY_SEPARATOR.'resized'.DIRECTORY_SEPARATOR.$resize_type.DIRECTORY_SEPARATOR.$fileName;
		}
		return $path;
	}





	public function setManufacturerImageResize($dirImg, $width = 30, $height = 30, $imageResized) {
		$imageFactory = $this->_objectManager->get('\Magento\Framework\Image\Factory');
		$imageObj     = $imageFactory->create($dirImg);
		$imageObj->constrainOnly(false);
		$imageObj->keepAspectRatio(true);
		$imageObj->keepFrame(false);
		$imageObj->backgroundColor([255, 255, 255]);
		if (is_numeric($width)) {
			$width = $width;
		}
		if (is_numeric($height)) {
			$height = $height;
		}

		$imageObj->resize($width, $height);
		$imageObj->save($imageResized);
		return true;
	}

	public function clearUrlKey($urlKey) {
		for ($i = 0; $i < 5; $i++) {
			$urlKey = str_replace(' ', ' ', $urlKey);
		}
		$change    = [' ', '\''];
		$newUrlKey = strtolower(str_replace($change, '-', $urlKey));
		return $newUrlKey;
	}

	public function getManufacturerCollection() {
		$store         = $this->_storeManager->getStore()->getId();
		$prefix        = '';
		$manufacturers = $this->_manufacturer->getCollection();
		$attribute     = $this->scopeConfig->getValue('manufacturer/general/brandlist_attribute', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store);

		$manufacturers->getSelect()->joinLeft($prefix.'manufacturer_text', 'main_table.manufacturer_id ='.$prefix.'manufacturer_text.manufacturer_id AND '.$prefix.'manufacturer_text.store_id = '.$store, ['status', 'description', 'short_description', 'url_key', 'position', 'store_id']);
		/*echo "<pre>";
		print_r($manufacturers->getData());
		die();*/

		if ($this->scopeConfig->getValue('manufacturer/general/manufacturer_display_brand')) {
			foreach ($manufacturers as $manufacturer) {
				$collection  = $this->_productCollection->getCollection();
				$table       = $this->_eavConfig->getAttribute('catalog_product', $attribute)->getBackend()->getTable();
				$attributeId = $this->_eavConfig->getAttribute('catalog_product', $attribute)->getAttributeId();

				$collection->getSelect('entity_id')->join(['attributeTable' => $table], 'e.entity_id = attributeTable.entity_id', [$attribute => 'attributeTable.value'])
				->where('attributeTable.attribute_id = ?', $attributeId)
				->where('attributeTable.value = ?', $manufacturer->getManufacturerId());

				if ($this->scopeConfig->getValue('manufacturer/general/manufacturer_display_instock')) {
					$collection->addAttributeToSelect('entity_id')->addAttributeToFilter('visibility', ['neq' => 1])->addFieldToFilter($attribute, $manufacturer->getManufacturerId())->addMinimalPrice()->addFinalPrice()->addTaxPercents();
				}

				if ($collection->getSize() == 0) {
					$manufacturers->removeItemByKey($manufacturer->getManufacturerId());
				}
			}
		}

		$ids = [];
		foreach ($manufacturers as $manufacturer) {
			$ids[] = $manufacturer->getManufacturerId();
		}
		if( !sizeof($ids) )
			$ids = '';
		$newManufactures = $this->getNewManufacturerCollection($ids);

		return $newManufactures;
	}

	public function getNewManufacturerCollection($ids = null){
		$store         = $this->_storeManager->getStore()->getId();
		$prefix        = '';
		$attribute     = $this->scopeConfig->getValue('manufacturer/general/brandlist_attribute', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store);

		$newManufactures = $this->_manufacturer->getCollection()
		->addFieldToFilter('main_table.manufacturer_id', ['in'=> [$ids]]);
		$newManufactures->getSelect()->join($prefix.'manufacturer_text', 'main_table.manufacturer_id ='.$prefix.'manufacturer_text.manufacturer_id AND '.$prefix.'manufacturer_text.store_id = '.$store, ['status', 'description', 'short_description', 'url_key', 'position', 'store_id']);
		$newManufactures->addFieldToFilter('status',1);
		return $newManufactures;		
	}

	public function getManufacturerCollectionSiteMap() {
		$store         = $this->_storeManager->getStore()->getId();
		$prefix        = '';
		$manufacturers = $this->_manufacturer->getCollection();
		$attribute     = $this->scopeConfig->getValue('manufacturer/general/brandlist_attribute', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store);

		$manufacturers->getSelect()->joinLeft($prefix.'manufacturer_text', 'main_table.manufacturer_id ='.$prefix.'manufacturer_text.manufacturer_id AND '.$prefix.'manufacturer_text.store_id = '.$store, ['status', 'description', 'short_description', 'url_key', 'position', 'store_id']);

		foreach ($manufacturers as $manufacturer) {
			$manufacturer->setUrl($manufacturer->getData('url_key'));
		}

		return $manufacturers;
	}

}
