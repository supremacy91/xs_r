<?php
namespace Biztech\Manufacturer\Block\Dames;
use Biztech\Manufacturer\Block\BaseBlock;
use Biztech\Manufacturer\Block\Context;
class Index extends BaseBlock
{
    protected $_defaultToolbarBlock = 'Magento\Catalog\Block\Product\ProductList\Toolbar';
    protected $_defaultColumnCount = 3;
    protected $_columnCountLayoutDepend = [];
    protected $_collection;
    protected $_helper;
    protected $_config;

    public function __construct(
        Context $context
        ){
        parent::__construct($context);
        $this->_helper = $context->getManufacturerHelper();
        $this->_config = $context->getConfig();
        $this->_collection = $context->getManufacturerHelper()->getManufacturerCollection();
        $this->setCollection($this->_collection);
        
    }

    public function _construct() {

        parent::_construct();
    }

    public function getHelper(){
        return $this->_helper;
    }

    public function getConfig(){
        return $this->_config;
    }

    /**
     * @return $this
     */
    protected function _prepareLayout() {



        parent::_prepareLayout();

        $toolbar = $this->getToolbarBlock();
        $collection = $this->getCollection();

      


        if ($orders = $this->getAvailableOrders()) {
            $toolbar->setAvailableOrders($orders);
        }
        if ($dir = $this->getDefaultDirection()) {
            $toolbar->setDefaultDirection($dir);
        }
        $toolbar->setCollection($collection);
        // print_r($toolbar->getData());
        // die();
        if ($toolbar->getData('_current_grid_direction')=='asc') {
            $collection->setOrder('manufacturer_name','ASC');
        } else if ($toolbar->getData('_current_grid_direction')=='desc') {
            $collection->setOrder('manufacturer_name','DESC');
        }
        //product_list_toolbar_pager
        $this->setChild('toolbar', $toolbar);
        /*$pager = $this->getLayout()->createBlock('Magento\Theme\Block\Html\Pager');
        // $pager = $this->getLayout()->getBlock('product_list_toolbar_pager');

        $pager->setCollection($collection)
        ->setAvailableLimit($toolbar->getAvailableLimit());

        $pager->setCollection( $this->getCollection() )->setShowPerPage(1);
        $this->setChild('manufacturer_list_toolbar_pager', $pager);
        $this->getCollection()->load();*/
        return $this;
    

    /*
        parent::_prepareLayout();

        $toolbar = $this->getToolbarBlock();

        $collection = $this->getCollection();

        if ($orders = $this->getAvailableOrders()) {
            $toolbar->setAvailableOrders($orders);
        }
        if ($sort = $this->getSortBy()) {
            $toolbar->setDefaultOrder($sort);
        }
        if ($dir = $this->getDefaultDirection()) {
            $toolbar->setDefaultDirection($dir);
        }
        $toolbar->setCollection($collection);
        //product_list_toolbar_pager
        $this->setChild('toolbar', $toolbar);
        
        


        $pager = $this->getLayout()->createBlock('Magento\Theme\Block\Html\Pager');
        // $pager = $this->getLayout()->getBlock('product_list_toolbar_pager');

        $pager->setCollection($collection)
        ->setAvailableLimit($toolbar->getAvailableLimit());

        $pager->setCollection( $this->getCollection() )->setShowPerPage(1);
        $this->setChild('manufacturer_list_toolbar_pager', $pager);
        $this->getCollection()->load();
        return $this;
    */}

    /**
     * Retrieve current view mode
     *
     * @return string
     */
    public function getMode()
    {
        return $this->getChildBlock('toolbar')->getCurrentMode();
    }

    public function getAvailableOrders() {
        return array('position' => 'Position', 'brand_name' => 'Name');
    }

    public function getDefaultDirection() {
        return 'asc';
    }

    public function getSortBy() {
        return 'manufacturer_name';
    }

    public function getManufacturerUrl($manufacturer) {
        $url = $this->getUrl($manufacturer->getUrlKey(), array());
        return $url;
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
        $block->setData('_current_limit', 'ALL');
        return $block;
    }

    /**
     * @return string
     */
    public function getToolbarHtml() {
        return $this->getChildHtml('toolbar');
    }

    /**
     * @return string
     */
    public function getAdditionalHtml()
    {
        return $this->getChildHtml('additional');
    }

    public function getPagerHtml(){
        return $this->getChildHtml('manufacturer_list_toolbar_pager');
    }

}
