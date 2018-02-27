<?php
namespace Biztech\Manufacturer\Model\ResourceModel;
class Urlrewrite extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    public function _construct()
    {
        $this->_init('Biztech\Manufacturer\Model\Urlrewrite', 'url_rewrite_id');
    }

}
