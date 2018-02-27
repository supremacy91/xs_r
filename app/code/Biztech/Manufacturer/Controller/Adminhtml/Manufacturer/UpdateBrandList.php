<?php

namespace Biztech\Manufacturer\Controller\Adminhtml\Manufacturer;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Biztech\Manufacturer\Model\Manufacturer;
use Biztech\Manufacturer\Model\Config;
use Biztech\Manufacturer\Helper\Data;
class UpdateBrandList extends Action
{
    protected $manufacturerCollection;
    protected $storeConfig;
    protected $manufacturerHelper;

    public function __construct(
        Context $context,
        Manufacturer $manufacturerCollection,
        Config $config,
        Data $manufacturerHelper
    )
    {
        parent::__construct($context);
        $this->manufacturerCollection = $manufacturerCollection;
        $this->storeConfig = $config;
        $this->manufacturerHelper = $manufacturerHelper;
    }

    public function execute()
    {

        $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
        try {
            $manufacturers = $this->manufacturerCollection->getCollection();
            $dimension = explode('x', $this->storeConfig->getCurrentStoreConfigValue('manufacturer/manufacturer_brand_list/manufacturer_list_dimension'));

            foreach ($manufacturers as $manufacturer) {
                $manufacturer_name = $manufacturer->getBrandName();
                $fileName = $manufacturer->getFilename();
                $imageUrl = $this->manufacturerHelper->getImageUrl($manufacturer_name, $fileName);
                $imageResized = $this->manufacturerHelper->getManufacturerImageUploadPath($manufacturer_name, $fileName, 'large_thumb');
                $dirImg = $this->_objectManager->get('\Magento\Framework\Filesystem\DirectoryList')->getRoot() . str_replace('/', DIRECTORY_SEPARATOR, strstr($imageUrl, '/pub/media'));

                $this->manufacturerHelper->setManufacturerImageResize($dirImg, $dimension[0], $dimension[1], $imageResized);
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
