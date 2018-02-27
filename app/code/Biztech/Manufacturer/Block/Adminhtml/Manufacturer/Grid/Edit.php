<?php
namespace Biztech\Manufacturer\Block\Adminhtml\Manufacturer\Grid;
class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'Biztech_Manufacturer';
        $this->_controller = 'adminhtml_manufacturer_grid';

        parent::_construct();

        $this->buttonList->update('save', 'label', __('Save Manufacturer'));
        $this->buttonList->update('delete', 'label', __('Delete Manufacturer'));

        $this->buttonList->add(
            'saveandcontinue', [
            'label' => __('Save and Continue Edit'),
            'class' => 'save',
            'data_attribute' => [
                'mage-init' => ['button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form']]
            ]
        ], -100
        );

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('block_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'hello_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'hello_content');
                }
            }
        ";
    }

    /**
     * Get edit form container header text
     *
     * @return string
     */
    public function getHeaderText()
    {
        if ($this->_coreRegistry->registry('manufacturer_grid')->getId()) {
            return __("Edit Manufacturer '%1'", $this->escapeHtml($this->_coreRegistry->registry('manufacturer_grid')->getTitle()));
        } else {
            return __('New Manufacturer');
        }
    }

}
