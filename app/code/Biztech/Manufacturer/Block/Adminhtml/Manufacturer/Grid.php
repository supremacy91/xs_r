<?php
namespace Biztech\Manufacturer\Block\Adminhtml\Manufacturer;
use Magento\Backend\Block\Widget\Grid\Container;
class Grid extends Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_manufacturer_grid'; /* block grid.php directory */
        $this->_blockGroup = 'Biztech_Manufacturer';
        $this->_headerText = __('Manufacturer Manager');
        $this->_addButtonLabel = __('Add Manufacturer');
        parent::_construct();
    }

}
