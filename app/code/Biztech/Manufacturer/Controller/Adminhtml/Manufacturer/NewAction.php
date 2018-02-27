<?php
namespace Biztech\Manufacturer\Controller\Adminhtml\Manufacturer;
use Magento\Backend\App\Action;
class NewAction extends Action
{
    public function execute()
    {
        $this->_forward('edit');
    }
    /**
     * Check for is allowed
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Biztech_Manufacturer::biztech_manufacturer_index');
    }
}
