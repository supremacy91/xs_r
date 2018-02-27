<?php
namespace Biztech\Manufacturer\Block\Adminhtml\Manufacturer\Grid\Edit\Tab;

use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use Magento\Store\Model\System\Store;
use Magento\Cms\Model\Wysiwyg\Config;

class Manufacturerpage extends Generic implements TabInterface
{
    protected $systemStore;
    protected $wysiwygConfig;

    /**
     * Manufacturerpage constructor.
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Store $systemStore
     * @param Config $wysiwygConfig
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Store $systemStore,
        Config $wysiwygConfig,
        array $data = []
    )
    {
        $this->systemStore = $systemStore;
        $this->wysiwygConfig = $wysiwygConfig;
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
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'wysiwyg_edit_form', 'action' => $this->getData('action'), 'method' => 'post'],]
        );
        $store = $this->getRequest()->getParam('store', 0);
        $form->setHtmlIdPrefix('page_');

        $fieldset = $form->addFieldset('manufacturer_form', ['legend' => __('Manufacture Page')]);

        if ($model->getManufacturerId()) {
            $fieldset->addField('manufacturer_id', 'hidden', ['name' => 'manufacturer_id']);
        }

        $config['document_base_url'] = $this->getData('store_media_url');
        $config['store_id'] = $this->getData('store_id');
        $config['add_variables'] = false;
        $config['add_widgets'] = false;
        $config['add_directives'] = true;
        $config['use_container'] = true;
        $config['container_class'] = 'hor-scroll';

        $fieldset->addField(
            'description', 'editor', [
                'name' => 'description',
                'label' => __('Description'),
                'title' => __('Description'),
                'required' => true,
                'wysiwyg' => true,
                'config' => $this->wysiwygConfig->getConfig($config)
            ]
        );

        $fieldset->addField(
            'short_description', 'editor', [
            'name' => 'short_description',
            'label' => __('Short Description'),
            'title' => __('Short Description'),
            'wysiwyg' => true,
            'config' => $this->wysiwygConfig->getConfig($config)
        ]);

        $fieldset->addField(
            'meta_title', 'text', [
            'name' => 'meta_title',
            'label' => __('Meta Title'),
        ]);


        $fieldset->addField(
            'meta_keyword', 'editor', [
            'name' => 'meta_keyword',
            'label' => __('Meta Keyword'),
            'title' => __('Meta Keyword')
        ]);

        $fieldset->addField(
            'meta_description', 'editor', [
            'name' => 'meta_description',
            'label' => __('Meta Description'),
            'title' => __('Meta Description')
        ]);

        $fieldset->addField(
            'url_key', 'text', [
            'name' => 'url_key',
            'required' => true,
            'label' => __('URL Key'),
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
        return __('Manufacture Page');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Manufacture Page');
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
