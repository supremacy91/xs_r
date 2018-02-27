<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Xlanding
 */

namespace Amasty\Xlanding\Block\Adminhtml\Page\Helper;

class Image extends \Magento\Framework\Data\Form\Element\Image
{
    protected $_storeManager;

    public function __construct(
        \Magento\Framework\Data\Form\Element\Factory $factoryElement,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\UrlInterface $urlBuilder,
        $data = []
    ) {
        $this->_storeManager = $storeManager;
        parent::__construct($factoryElement, $factoryCollection, $escaper, $urlBuilder, $data);
    }

    protected function _getUrl()
    {
        $url = false;
        if ($this->getValue()) {
            $url = $this->_storeManager->getStore()->getBaseUrl(
                \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
            ) . \Amasty\Xlanding\Model\Page::FILE_PATH_UPLOAD . $this->getValue();
        }
        return $url;
    }
}
