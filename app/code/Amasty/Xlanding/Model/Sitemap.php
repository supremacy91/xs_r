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
namespace Amasty\Xlanding\Model;

class Sitemap extends \Magento\Sitemap\Model\Sitemap
{
    protected $_amastyXlandingPageFactory;
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Escaper $escaper,
        \Magento\Sitemap\Helper\Data $sitemapData,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Sitemap\Model\ResourceModel\Catalog\CategoryFactory $categoryFactory,
        \Magento\Sitemap\Model\ResourceModel\Catalog\ProductFactory $productFactory,
        \Magento\Sitemap\Model\ResourceModel\Cms\PageFactory $cmsFactory,
        \Amasty\Xlanding\Model\ResourceModel\PageFactory $amastyXlandingPageFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $modelDate,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_amastyXlandingPageFactory = $amastyXlandingPageFactory;

        return parent::__construct(
            $context,
            $registry,
            $escaper,
            $sitemapData,
            $filesystem,
            $categoryFactory,
            $productFactory,
            $cmsFactory,
            $modelDate,
            $storeManager,
            $request,
            $dateTime,
            $resource,
            $resourceCollection,
            $data
        );
    }
    protected function _initSitemapItems()
    {
        /** @var $helper \Magento\Sitemap\Helper\Data */
                $helper = $this->_sitemapData;

        $storeId = $this->getStoreId();

        parent::_initSitemapItems();
        $this->_sitemapItems[] = new \Magento\Framework\DataObject(
            [
                'changefreq' => $helper->getPageChangefreq($storeId),
                'priority' => $helper->getPagePriority($storeId),
                'collection' => $this->_amastyXlandingPageFactory->create()->getSitemapCollection($storeId),
            ]
        );
    }
}