<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Xlanding
 */

namespace Amasty\Xlanding\Block;

class Page extends \Magento\Framework\View\Element\Template
{
    protected $_coreRegistry;
    protected $_templateFilterFactory;
    protected $_templateFilterModel = 'Magento\Catalog\Model\Template\Filter';
    protected $_pageTemplateProcessor;
    protected $_filterProvider;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Catalog\Model\Template\Filter\Factory $templateFilterFactory,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        array $data = []
    ){
        $this->_coreRegistry = $coreRegistry;
        $this->_templateFilterFactory = $templateFilterFactory;
        $this->_filterProvider = $filterProvider;

        return parent::__construct(
            $context,
            $data
        );
    }

    public function getPage()
    {
        return $this->_coreRegistry->registry('amasty_xlanding_page');
    }

    public function getLayoutFileUrl()
    {
        $url = false;
        if ($this->getPage()->getLayoutFile()) {
            $url = $this->_storeManager->getStore()->getBaseUrl(
                \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
            ) . \Amasty\Xlanding\Model\Page::FILE_PATH_UPLOAD . $this->getPage()->getLayoutFile();
        }
        return $url;
    }

    public function getPageTemplateProcessor()
    {
        if (!$this->_pageTemplateProcessor){
            $this->_pageTemplateProcessor = $this->_templateFilterFactory->create($this->_templateFilterModel);
        }
        return $this->_pageTemplateProcessor;
    }

    public function filter($value)
    {
        return $this->getPageTemplateProcessor()->filter($value);
    }

    public function getCmsBlockHtml($blockId)
    {
        return $this->getLayout()->createBlock(
            'Magento\Cms\Block\Block'
        )->setBlockId(
            $blockId
        )->toHtml();
    }

    public function getLayoutTopDescription()
    {
        $html = $this->_filterProvider->getPageFilter()->filter($this->getPage()->getLayoutTopDescription());
        return $html;
    }

    public function getLayoutBottomDescription()
    {
        $html = $this->_filterProvider->getPageFilter()->filter($this->getPage()->getLayoutBottomDescription());
        return $html;
    }
}
