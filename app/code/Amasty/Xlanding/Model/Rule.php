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

use Magento\Catalog\Model\Product;

class Rule extends \Magento\CatalogRule\Model\Rule
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Amasty\Xlanding\Model\ResourceModel\Page');
        $this->setIdFieldName('page_id');
    }

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Amasty\Xlanding\Model\Rule\Condition\CombineFactory $combineFactory,
//        \Magento\CatalogRule\Model\Rule\Condition\CombineFactory $combineFactory,
        \Magento\CatalogRule\Model\Rule\Action\CollectionFactory $actionCollectionFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\Model\ResourceModel\Iterator $resourceIterator,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\CatalogRule\Helper\Data $catalogRuleData,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypesList,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\CatalogRule\Model\Indexer\Rule\RuleProductProcessor $ruleProductProcessor,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $relatedCacheTypes = [],
        array $data = []
    ) {

        return parent::__construct($context,
                $registry,
                $formFactory,
                $localeDate,
                $productCollectionFactory,
                $storeManager,
                $combineFactory,
                $actionCollectionFactory,
                $productFactory,
                $resourceIterator,
                $customerSession,
                $catalogRuleData,
                $cacheTypesList,
                $dateTime,
                $ruleProductProcessor,
                $resource,
                $resourceCollection,
                $relatedCacheTypes,
                $data);
    }
}