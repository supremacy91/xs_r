<?php
namespace Biztech\Manufacturer\Block\Adminhtml\Manufacturer\Grid\Edit\Tab;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
class Image extends Generic implements TabInterface
{

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $systemStore;
    protected $helperData;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Biztech\Manufacturer\Helper\Data $helperData,
        array $data = []
    )
    {
        $this->helperData = $helperData;
        $this->systemStore = $systemStore;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {

        /* @var $model \Magento\Cms\Model\Page */
        $model = $this->_coreRegistry->registry('manufacturer_grid');
        $isElementDisabled = false;
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('page_');

        $fieldset = $form->addFieldset('manufacturer_form', array('legend' => __('Image')));

        if ($model->getManufacturerId()) {
            $fieldset->addField('manufacturer_id', 'hidden', array('name' => 'manufacturer_id'));
            $fieldset->addField('manufacturer_name', 'hidden', array('name' => 'manufacturer_id'));
        }

        $store = $this->getRequest()->getParam('store', 0);
        $upload_dimension = explode('x', $this->helperData->getConfigValue('manufacturer/general/image_upload_width_height'));

        $manufacturer_name = $this->_coreRegistry->registry('manufacturer_grid')->getData('brand_name');
        $replace = array("'");
        $new_manufacturer_name = str_replace($replace, "_", $manufacturer_name);
        if ($this->_coreRegistry->registry('manufacturer_grid')->getData('filename') != "") {
            $fieldset->addField('filename', 'file', array(
                'label' => __('Manufacture Logo'),
                'required' => false,
                'name' => 'filename',
                'note' => 'Brand image dimension must be greater than equal to ' . $upload_dimension[0] . 'px width and ' . $upload_dimension[1] . 'px height',
                'after_element_html' => $this->_coreRegistry->registry('manufacturer_grid')->getData('filename') != "" ? '<span class="hint"><img src="' . $this->helperData->getImageUrl($this->_coreRegistry->registry('manufacturer_grid')->getData('filename')) . '" width="25" height="25" name="manufacturer_image" style="vertical-align: middle;" /></span>' : '',
            ));
        } else {
            $fieldset->addField('filename', 'file', array(
                'label' => __('Manufacture Logo'),
                'required' => false,
                'name' => 'filename',
                'note' => 'Brand image dimension must be greater than equal to ' . $upload_dimension[0] . 'px width and ' . $upload_dimension[1] . 'px height',
                'after_element_html' => $this->_coreRegistry->registry('manufacturer_grid')->getData('filename') != "" ? '<span class="hint"><img src="' . $this->helperData->getImageUrl($this->_coreRegistry->registry('manufacturer_grid')->getData('filename')) . '" width="25" height="25" name="manufacturer_image" style="vertical-align: middle;" /></span>' : '',
            ));
        }

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
        return __('Image');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Image');
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
