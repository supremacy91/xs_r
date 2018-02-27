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
namespace Amasty\Xlanding\Model\ResourceModel\Page;

use Magento\Cms\Api\Data\PageInterface;

class Collection extends \Magento\Cms\Model\ResourceModel\Page\Collection
{

    protected function _construct()
    {
        $this->_init('Amasty\Xlanding\Model\Page', 'Amasty\Xlanding\Model\ResourceModel\Page');
        $this->_map['fields']['page_id'] = 'main_table.page_id';
        $this->_map['fields']['store'] = 'store_table.store_id';
    }

    /**
     * Perform operations after collection load
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        $result = parent::_afterLoad();
        $this->performAfterLoad('amasty_xlanding_page_store', $this->getIdFieldName());
        $this->_previewFlag = false;

        return $result;
    }

    /**
     * Perform operations before rendering filters
     *
     * @return void
     */
    protected function _renderFiltersBefore()
    {
        $entityMetadata = $this->metadataPool->getMetadata(PageInterface::class);
        $this->joinStoreRelationTable('amasty_xlanding_page_store', $entityMetadata->getLinkField());
    }
}
