<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Xlanding
 */

namespace Amasty\Xlanding\Model\Export;

use Magento\Framework\View\Element\UiComponentInterface;

class MetadataProvider extends \Magento\Ui\Model\Export\MetadataProvider
{
    protected $_columns;

    protected $_skipColumns = [
        'page_id', 'creation_time', 'update_time',
        'layout_file', 'layout_file'
    ];

    public function getMainTableColumns(\Amasty\Xlanding\Model\ResourceModel\Page\Grid\Collection $collection)
    {
        if ($this->_columns === null){
            $this->_columns = [];
            $schema = $collection->getConnection()->describeTable($collection->getMainTable());
            foreach ($schema as $column) {
                if (!in_array($column['COLUMN_NAME'], $this->_skipColumns)){
                    $this->_columns[] = $column['COLUMN_NAME'];
                }
            }
        }

        return $this->_columns;
    }

    public function getMainTableHeaders(\Amasty\Xlanding\Model\ResourceModel\Page\Grid\Collection $collection)
    {
        return $this->getMainTableColumns($collection);
    }
}