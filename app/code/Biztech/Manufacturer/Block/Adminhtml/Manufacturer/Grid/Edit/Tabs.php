<?php
namespace Biztech\Manufacturer\Block\Adminhtml\Manufacturer\Grid\Edit;
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    protected function _construct()
    {
        parent::_construct();
        $this->setId('manufacturer_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Manufacturer Information'));
    }
}
