<?php

namespace Biztech\Manufacturer\Block\Manufacturer;
use Magento\Catalog\Block\Product\AbstractProduct;
use Magento\Catalog\Block\Product\Context;
use Biztech\Manufacturer\Helper\Data;
use Biztech\Manufacturer\Model\Manufacturer;
use Biztech\Manufacturer\Model\Manufacturertext;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Config;

class View extends AbstractProduct
{
    protected $_defaultToolbarBlock = 'Magento\Catalog\Block\Product\ProductList\Toolbar';
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
    )
    {
        $this->_helperData = $helperData;
        $this->_storeManager = $context->getStoreManager();
        $this->_manufacturerCollection = $manufacturerCollection;
        $this->_manufacturerTextCollection = $manufacturerTextCollection;
        $this->_productCollection = $productCollection;
        $this->_eavConfig = $eavConfig;
        parent::__construct($context, $data);
    }

    public function getConfigValue($configPath)
    {
        return $this->_scopeConfig->getValue($configPath);
    }

    protected function _beforeToHtml()
    {
        $toolbar = $this->getToolbarBlock();

        // called prepare sortable parameters
        $collection = $this->getProductCollection();

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

    public function getManufacturer()
    {
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

    public function getPreparedCollection()
    {
        return $this->_prepareCollection();
    }

    protected function _prepareCollection()
    {
        $collection = $this->_manufacturerCollection->getCollection()->addFieldToFilter("status", ["eq" => 1]);
        $collection->setPageSize(1);
        return $collection;
    }

    public function getProductCollection()
    {
        $manufacturer = $this->getManufacturer();
        if ($manufacturer != null) {
            $collection = $this->_productCollection->getCollection();
            $attribute = $this->getConfigValue('manufacturer/general/brandlist_attribute');
            $table = $this->_eavConfig->getAttribute('catalog_product', $attribute)->getBackend()->getTable();
            $attributeId = $this->_eavConfig->getAttribute('catalog_product', $attribute)->getAttributeId();

            $collection->getSelect()
                ->join(['attributeTable' => $table], 'e.entity_id = attributeTable.entity_id', [$attribute => 'attributeTable.value'])
                ->where('attributeTable.attribute_id=?', $attributeId)
                ->where('attributeTable.value = ?', $manufacturer->getManufacturerId());

            $collection->addAttributeToSelect('*')
                ->addAttributeToFilter('visibility', ['neq' => 1])
                ->addFieldToFilter('status', ['eq' => 1])
                ->addAttributeToFilter($attribute, $manufacturer->getManufacturerId())
                ->addMinimalPrice()
                ->addFinalPrice()
                ->addTaxPercents();
        }
        return $collection;
    }

    /**
     * Retrieve Toolbar block
     *
     * @return \Magento\Catalog\Block\Product\ProductList\Toolbar
     */
    public function getToolbarBlock()
    {
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

    public function getToolbarHtml()
    {
        return $this->getChildHtml('toolbar');
    }

    /**
     * Retrieve current view mode
     *
     * @return string
     */
    public function getMode()
    {
        return $this->getChildBlock('toolbar')->getCurrentMode();
    }

    public function getAvailableOrders()
    {
        return ['position' => 'Position', 'brand_name' => 'Name'];
    }

    public function getDefaultDirection()
    {
        return 'asc';
    }

    public function getSortBy()
    {
        return 'manufacturer_id';
    }

    public function getManufacturerUrl($manufacturer)
    {
        $url = $this->getUrl($manufacturer->getUrlKey(), []);
        return $url;
    }

    public function getAdditionalHtml()
    {
        return $this->getChildHtml('additional');
    }

}
