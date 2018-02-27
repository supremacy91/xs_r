<?php

namespace Biztech\Manufacturer\Block\Adminhtml\Manufacturer\Grid\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Biztech\Manufacturer\Model\Config;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use Magento\Store\Model\System\Store;

class General extends Generic implements TabInterface
{

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $systemStore;
    protected $eavConfig;
    protected $storeConfig;

    /**
     * General constructor.
     * @param Config $config
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Store $systemStore
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param array $data
     */
    public function __construct(
        Config $config,
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Store $systemStore,
        \Magento\Eav\Model\Config $eavConfig,
        array $data = []
    )
    {
        $this->systemStore = $systemStore;
        $this->eavConfig = $eavConfig;
        $this->storeConfig = $config;

        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('manufacturer_grid');
        $isElementDisabled = false;
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('page_');
        $fieldset = $form->addFieldset('manufacturer_form', ['legend' => __('General')]);
        if ($model->getManufacturerId()) {
            $fieldset->addField('manufacturer_id', 'hidden', ['name' => 'manufacturer_id']);
        }

        $store = $this->getRequest()->getParam('store', 0);

        /*$attribute = $this->storeConfig->getCurrentStoreConfigValue('manufacturer/general/brandlist_attribute');

        $attributes = $this->eavConfig->getAttribute('catalog_product', $attribute);
        $attributeOptions = $attributes->getSource()->getAllOptions(false);

        $default = ['value' => '', 'label' => __('Choose Manufacturer')];

        $i = 0;

        $manufacturer[$i] = $default;

        foreach ($attributeOptions as $key => $value) {
            $i++;
            $manufacturer[$i] = $value;
        }*/

        if ($store == 0) {
            /*$fieldset->addField('manufacturer_name', 'select', [
                'label' => __('Select Manufacturer'),
                'class' => 'required-entry',
                'required' => true,
                'name' => 'manufacturer_name',
                'values' => $manufacturer
            ]);*/
            $fieldset->addField('manufacturer_name', 'text', [
                'label' => __('Enter Manufacturer'),
                'class' => 'required-entry',
                'required' => true,
                'name' => 'manufacturer_name',
                // 'values' => $manufacturer
            ]);
        } else {
            $fieldset->addField('manufacturer_name', 'hidden', [
                'label' => __('Manufacturer'),
                'name' => 'manufacturer_name',
            ]);
        }

        $fieldset->addField('status', 'select', [
            'label' => __('Status'),
            'name' => 'status',
            'values' => [
                [
                    'value' => 1,
                    'label' => __('Enabled')
                ],
                [
                    'value' => 2,
                    'label' => __('Disabled')
                ]
            ]
        ]);

        $fieldset->addField('show_in_sidebar', 'select', [
            'label' => __('Show In Sidebar'),
            'name' => 'show_in_sidebar',
            'values' => [
                [
                    'value' => 1,
                    'label' => __('Yes'),
                ],
                [
                    'value' => 0,
                    'label' => __('No'),
                ],
            ],
        ]);

        $fieldset->addField('is_featured', 'select', [
            'label' => __('Is Featured'),
            'name' => 'is_featured',
            'values' => [
                [
                    'value' => 1,
                    'label' => __('Yes'),
                ],
                [
                    'value' => 0,
                    'label' => __('No'),
                ],
            ],
        ]);

        $fieldset->addField('position', 'text', [
            'label' => __('Priority'),
            'required' => false,
            'name' => 'position',
            'style' => 'width:275px;',
            'note' => 'Set Brand priority. To show in particular order on frontend.'
        ]);

        $fieldset->addField('store_id', 'hidden', [
            'label' => __('Store Id'),
            'required' => false,
            'name' => 'store_id'
        ]);

        if (!$model->getId()) {
            $model->setData('status', $isElementDisabled ? '2' : '1');
        }

        if ($this->_backendSession->getManufacturerData()) {
            $form->setValues($this->_backendSession->getManufacturerData());
            $this->_backendSession->setManufacturerData(null);
        } elseif ($this->_coreRegistry->registry('manufacturer_grid')) {
            $this->_coreRegistry->registry('manufacturer_grid')->setData('store_id', $store);
            $form->setValues($this->_coreRegistry->registry('manufacturer_grid')->getData());
        } else {
            $form->setValues($model->getData());
        }
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('General');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('General');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }

}
