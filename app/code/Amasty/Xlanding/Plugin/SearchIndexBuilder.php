<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Xlanding
 */

namespace Amasty\Xlanding\Plugin;

class SearchIndexBuilder
{
    protected $_coreRegistry;

    public function __construct(
        \Magento\Framework\Registry $coreRegistry
    ){
        $this->_coreRegistry = $coreRegistry;
    }

    public function afterBuild(\Magento\CatalogSearch\Model\Search\IndexBuilder $indexBuilder, $select)
    {
        $page = $this->_coreRegistry->registry('amasty_xlanding_page');

        if ($page) {
            $page->applyAttributesFilter($select);
        }

        return $select;
    }
}
