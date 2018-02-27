<?php

namespace Biztech\Manufacturer\Model\ResourceModel\Manufacturertext;
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    public function _construct()
    {
        $this->_init('Biztech\Manufacturer\Model\Manufacturertext',
            'Biztech\Manufacturer\Model\ResourceModel\Manufacturertext');
    }
}
