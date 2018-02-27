<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Xlanding
 */

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Amasty\Xlanding\Helper;

use Magento\Framework\App\Action\Action;
use Magento\Catalog\Api\CategoryRepositoryInterface;

/**
 * CMS Page Helper
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.NPathComplexity)
 */
class Page extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $messageManager;
    protected $categoryRepository;
    protected $_page;
    protected $_design;
    protected $_pageFactory;
    protected $_storeManager;
    protected $_localeDate;
    protected $_escaper;
    protected $resultPageFactory;
    protected $_coreRegistry;
    protected $_layerResolver;
    protected $_pageConfig;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Amasty\Xlanding\Model\Page $page,
        \Magento\Framework\View\DesignInterface $design,
        \Amasty\Xlanding\Model\PageFactory $pageFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $coreRegistry,
        CategoryRepositoryInterface $categoryRepository,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Magento\Framework\View\Page\Config $pageConfig
    ) {
        $this->messageManager = $messageManager;
        $this->_page = $page;
        $this->_design = $design;
        $this->_pageFactory = $pageFactory;
        $this->_storeManager = $storeManager;
        $this->_localeDate = $localeDate;
        $this->_escaper = $escaper;
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->categoryRepository = $categoryRepository;
        $this->_layerResolver = $layerResolver;
        $this->_pageConfig = $pageConfig;

        parent::__construct($context);
    }

    public function prepareResultPage($pageId = null)
    {
        if ($pageId !== null && $pageId !== $this->_page->getId()) {
            $delimiterPosition = strrpos($pageId, '|');
            if ($delimiterPosition) {
                $pageId = substr($pageId, 0, $delimiterPosition);
            }

            $this->_page->setStoreId($this->_storeManager->getStore()->getId());
            if (!$this->_page->load($pageId)) {
                return false;
            }
        }

        if (!$this->_page->getId()) {
            return false;
        }


        $rootCategoryId = $this->_storeManager->getStore()->getRootCategoryId();

        $category = $this->categoryRepository->get($rootCategoryId, $this->_storeManager->getStore()->getId());

        $this->_coreRegistry->register('current_category', $category);
        $this->_coreRegistry->register('amasty_xlanding_page', $this->_page);

        $resultPage = $this->resultPageFactory->create();
        $this->setLayoutType($resultPage);

        $resultPage->addHandle('catalog_category_view');

        $type = $category->hasChildren() ? 'layered' : 'layered_without_children';

        if (!$category->hasChildren()) {
            // Two levels removed from parent.  Need to add default page type.
            $parentType = strtok($type, '_');
            $resultPage->addPageLayoutHandles(
                ['type' => $parentType],
                'catalog_category_view'
            );
        }

        $resultPage->addPageLayoutHandles(
            ['type' => $type, 'id' => $category->getId()],
            'catalog_category_view'

        );

        $layoutUpdate = $this->_page->getLayoutUpdateXml();

        if (!empty($layoutUpdate)) {
            $resultPage->getLayout()->getUpdate()->addUpdate($layoutUpdate);
        }

        $this->setPageTitle($resultPage);
        $this->addBreadcrumb($resultPage);


        $this->_pageConfig->getTitle()->set($this->_page->getMetaTitle());
        $this->_pageConfig->setKeywords($this->_page->getMetaKeywords());
        $this->_pageConfig->setDescription($this->_page->getMetaDescription());

        return $resultPage;
    }

    protected function addBreadcrumb($resultPage)
    {
        $breadcrumbs = $resultPage->getLayout()->getBlock('breadcrumbs');
        if ($breadcrumbs){
            $breadcrumbs->addCrumb('amasty_xlanding_page', ['label' => $this->_page->getTitle(), 'title' => $this->_page->getTitle()]);
        }
    }

    protected function setPageTitle($resultPage)
    {
        $contentHeadingBlock = $resultPage->getLayout()->getBlock('page.main.title');

        if ($contentHeadingBlock) {
            $contentHeading = $this->_escaper->escapeHtml($this->_page->getLayoutHeading());
            $contentHeadingBlock->setPageTitle($contentHeading);
        }
    }

    protected function setLayoutType($resultPage)
    {
        if ($this->_page->getPageLayout()) {
            $resultPage->getConfig()->setPageLayout($this->_page->getPageLayout());
        }
        return $resultPage;
    }
}