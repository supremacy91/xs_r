<?php

namespace Biztech\Manufacturer\Model;

use Magento\Framework\DataObject as DataObject;
class Sitemap extends \Magento\Sitemap\Model\Sitemap
{
    protected $_helperData;
    protected $_dataObject;
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Escaper $escaper,
        \Magento\Sitemap\Helper\Data $sitemapData,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Sitemap\Model\ResourceModel\Catalog\CategoryFactory $categoryFactory,
        \Magento\Sitemap\Model\ResourceModel\Catalog\ProductFactory $productFactory,
        \Magento\Sitemap\Model\ResourceModel\Cms\PageFactory $cmsFactory,
        \Biztech\Manufacturer\Helper\Data $helperData,
        DataObject $dataobject,
        \Magento\Framework\Stdlib\DateTime\DateTime $modelDate,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        parent::__construct($context, $registry, $escaper, $sitemapData, $filesystem,
            $categoryFactory, $productFactory, $cmsFactory, $modelDate, $storeManager,
            $request, $dateTime, $resource, $resourceCollection);
        $this->_helperData = $helperData;
        $this->_dataObject = $dataobject;
    }

    protected function _initSitemapItems()
    {
        parent::_initSitemapItems();
        $arr = [];
        $date = $this->_dateModel->gmtDate('Y-m-d H:i:s');
        $changefreq = $this->_helperData->getConfigValue('manufacturer/brand_sitemap/changefreq');
        $priority = $this->_helperData->getConfigValue('manufacturer/brand_sitemap/priority');
        $_manufacturerCollection = $this->_helperData->getManufacturerCollectionSiteMap();

        $arr = [
            'changefreq' => $changefreq,
            'priority' => $priority,
            'collection' => $_manufacturerCollection,
        ];
        $this->_dataObject->addData($arr);


        $this->_sitemapItems[] = $this->_dataObject;
    }

}