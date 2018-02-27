<?php

namespace Biztech\Manufacturer\Model\ResourceModel;

class Manufacturer extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    public function _construct()
    {
        $this->_init('manufacturer', 'manufacturer_id');
    }

    public function getStoreManufacturer($manufacturerId, $storeId)
    {
        $manufacturer = $this->load($manufacturerId);
        $manufacturerName = $manufacturer->getBrandName();
    }

}
