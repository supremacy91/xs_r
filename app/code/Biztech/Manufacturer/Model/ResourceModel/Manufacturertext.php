<?php

namespace Biztech\Manufacturer\Model\ResourceModel;

/**
 * Grid resource
 */
class Manufacturertext extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb {

    /**
     * Initialize resource
     *
     * @return void
     */
    public function _construct() {
        $this->_init('manufacturer_text', 'text_id');
    }

}
