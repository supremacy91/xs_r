<?php

namespace Biztech\Manufacturer\Controller\Adminhtml\Manufacturer;

/**
 * Description of UpdateLeftNavigation
 *
 * @author vinay
 */
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;

class UpdateProductView extends Action {

    protected $_manufacturerCollection;
    protected $_storeConfig;
    protected $_manufacturerHelper;

    public function __construct(
    Context $context, \Biztech\Manufacturer\Model\Manufacturer $manufacturerCollection, \Biztech\Manufacturer\Model\Config $config, \Biztech\Manufacturer\Helper\Data $manufacturerHelper
    ) {
        parent::__construct($context);
        $this->_manufacturerCollection = $manufacturerCollection;
        $this->_storeConfig = $config;
        $this->_manufacturerHelper = $manufacturerHelper;
    }

    public function execute() {

        $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
        try {
            $manufacturers = $this->_manufacturerCollection->getCollection();
            $dimension = explode('x', $this->_storeConfig->getCurrentStoreConfigValue('manufacturer/manufacturer_product_view/product_view_dimension'));

            foreach ($manufacturers as $manufacturer) {
                $manufacturer_name = $manufacturer->getBrandName();
                $fileName = $manufacturer->getFilename();
                $imageUrl = $this->_manufacturerHelper->getImageUrl($manufacturer_name, $fileName);
                $imageResized = $this->_manufacturerHelper->getManufacturerImageUploadPath($manufacturer_name, $fileName, 'product_thumb');
                $dirImg = $this->_objectManager->get('\Magento\Framework\Filesystem\DirectoryList')->getRoot() . str_replace('/', DIRECTORY_SEPARATOR, strstr($imageUrl, '/pub/media'));

                $this->_manufacturerHelper->setManufacturerImageResize($dirImg, $dimension[0], $dimension[1], $imageResized);
            }
            $this->messageManager->addSuccess(__('Cache cleaned Successfully'));
        } catch (Exception $e) {
            $this->messageManager->addError(__('There is some problem in cache cleaning.Please try again later.'));
        }
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }

    /**
     * Check for is allowed
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Biztech_Manufacturer::biztech_manufacturer_index');
    }

}
