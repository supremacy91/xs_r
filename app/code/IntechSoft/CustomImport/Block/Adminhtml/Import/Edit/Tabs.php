<?php

namespace IntechSoft\CustomImport\Block\Adminhtml\Import\Edit;

use Magento\Backend\Block\Widget\Tabs as WidgetTabs;

class Tabs extends WidgetTabs
{
    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {

        parent::_construct();
        $this->setId('import_settings_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Import Settings'));
    }

    /**
     * @return $this
     */
    protected function _beforeToHtml()
    {
        $this->addTab(
            'news_info',
            [
                'label' => __('General'),
                'title' => __('General'),
                'content' => $this->getLayout()->createBlock(
                    'IntechSoft\CustomImport\Block\Adminhtml\Import\Edit\Tab\Info'
                )->toHtml(),
                'active' => true
            ]
        );

        return parent::_beforeToHtml();
    }
}