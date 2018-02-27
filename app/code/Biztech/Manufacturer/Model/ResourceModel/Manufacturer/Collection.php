<?php
namespace Biztech\Manufacturer\Model\ResourceModel\Manufacturer;
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Initialize resource collection
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('Biztech\Manufacturer\Model\Manufacturer',
            'Biztech\Manufacturer\Model\ResourceModel\Manufacturer');
    }
}
