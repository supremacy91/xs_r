<?php

namespace Biztech\Manufacturer\Observer;

use Magento\Framework\Event\ObserverInterface;

class cleanCache implements ObserverInterface
{

    protected $_scopeConfig;
    protected $_storeConfig;
    protected $_manufacturerCollection;
    protected $_manufacturerHelper;
    protected $_dirList;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Biztech\Manufacturer\Model\Manufacturer $manufacturerCollection,
        \Biztech\Manufacturer\Model\Config $config,
        \Biztech\Manufacturer\Helper\Data $manufacturerHelper,
        \Magento\Framework\Filesystem\DirectoryList $dirList
    )
    {
        $this->_scopeConfig = $scopeConfig;
        $this->_storeConfig = $config;
        $this->_manufacturerCollection = $manufacturerCollection;
        $this->_manufacturerHelper = $manufacturerHelper;
        $this->_dirList = $dirList;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $manufacturers = $this->_manufacturerCollection->getCollection();
            $product_dimension = explode('x', $this->_storeConfig->getCurrentStoreConfigValue('manufacturer/manufacturer_product_view/product_view_dimension'));

            $list_dimension = explode('x', $this->_storeConfig->getCurrentStoreConfigValue('manufacturer/manufacturer_brand_list/manufacturer_list_dimension'));
            $left_dimension = explode('x', $this->_storeConfig->getCurrentStoreConfigValue('manufacturer/left_configuration/layered_navigation_dimension'));

            foreach ($manufacturers as $manufacturer) {
                $manufacturer_name = $manufacturer->getBrandName();
                $fileName = $manufacturer->getFilename();
                $imageUrl = $this->_manufacturerHelper->getImageUrl($manufacturer_name, $fileName);

                //productthumb
                $productImageResized = $this->_manufacturerHelper->getManufacturerImageUploadPath($manufacturer_name, $fileName, 'product_thumb');
                $dirImg = $this->_dirList->getRoot() . str_replace('/', DIRECTORY_SEPARATOR, strstr($imageUrl, '/pub/media'));

                $this->_manufacturerHelper->setManufacturerImageResize($dirImg, $product_dimension[0], $product_dimension[1], $productImageResized);

                //leftnavigation
                $leftImageResized = $this->_manufacturerHelper->getManufacturerImageUploadPath($manufacturer_name, $fileName, 'small_thumb');
                $dirImg = $this->_dirList->getRoot() . str_replace('/', DIRECTORY_SEPARATOR, strstr($imageUrl, '/pub/media'));

                $this->_manufacturerHelper->setManufacturerImageResize($dirImg, $left_dimension[0], $left_dimension[1], $leftImageResized);

                //list page
                $listImageResized = $this->_manufacturerHelper->getManufacturerImageUploadPath($manufacturer_name, $fileName, 'large_thumb');
                $dirImg = $this->_dirList->getRoot() . str_replace('/', DIRECTORY_SEPARATOR, strstr($imageUrl, '/pub/media'));

                $this->_manufacturerHelper->setManufacturerImageResize($dirImg, $list_dimension[0], $list_dimension[1], $listImageResized);
            }
        } catch (\Exception $e) {
            // $this->messageManager->addError(__('There is some problem in cache cleaning.Please try again later.'));
        }

    }
}