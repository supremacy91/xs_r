<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Xlanding
 */

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Amasty\Xlanding\Block\Adminhtml\Page\Edit\Tab;

/**
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Design extends \Magento\Backend\Block\Widget\Form\Generic implements
    \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Framework\View\Design\Theme\LabelFactory
     */
    protected $_labelFactory;

    /**
     * @var \Magento\Theme\Model\Layout\Source\Layout
     */
    protected $_pageLayout;

    /**
     * @var \Magento\Framework\View\Model\PageLayout\Config\BuilderInterface
     */
    protected $pageLayoutBuilder;

    protected $_eavConfig;

    protected $_categoryAttributeSourcePage;
    protected $_wysiwygConfig;
    protected $_ruleSourceColumns;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Theme\Model\Layout\Source\Layout $pageLayout,
        \Magento\Framework\View\Design\Theme\LabelFactory $labelFactory,
        \Magento\Framework\View\Model\PageLayout\Config\BuilderInterface $pageLayoutBuilder,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Catalog\Model\Category\Attribute\Source\Page $categoryAttributeSourcePage,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        \Amasty\Xlanding\Model\Rule\Source\Columns $ruleSourceColumns,
        array $data = []
    ) {
        $this->pageLayoutBuilder = $pageLayoutBuilder;
        $this->_labelFactory = $labelFactory;
        $this->_pageLayout = $pageLayout;
        $this->_eavConfig = $eavConfig;
        $this->_categoryAttributeSourcePage = $categoryAttributeSourcePage;
        $this->_ruleSourceColumns = $ruleSourceColumns;
        $this->_wysiwygConfig = $wysiwygConfig;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form tab configuration
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setShowGlobalIcon(true);
    }

    /**
     * Initialise form fields
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        /*
         * Checking if user have permissions to save information
         */
        $isElementDisabled = !$this->_isAllowedAction('Amasty_Xlanding::page');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(['data' => ['html_id_prefix' => 'page_']]);

        $model = $this->_coreRegistry->registry('amasty_xlanding_page');

        $form->setDataObject($model);

        $layoutFieldset = $form->addFieldset(
            'layout_fieldset',
            ['legend' => __('Page Layout'), 'class' => 'fieldset-wide', 'disabled' => $isElementDisabled]
        );

        $this->_addElementTypes($layoutFieldset);

        if (!$model->getId()) {
            $model
                ->setRootTemplate($this->_pageLayout->getDefaultValue())
                ->setLayoutColumnsCount(\Amasty\Xlanding\Model\Rule\Source\Columns::FOUR_COL_MODE)
                ->setLayoutIncludeNavigation(1);
        }

        $layoutFieldset->addField(
            'page_layout',
            'select',
            [
                'name' => 'page_layout',
                'label' => __('Layout'),
                'required' => true,
                'values' => $this->pageLayoutBuilder->getPageLayoutsConfig()->toOptionArray(),
                'disabled' => $isElementDisabled
            ]
        );

        $layoutFieldset->addField('layout_columns_count', 'select', array(
            'name'     => 'layout_columns_count',
            'label'    => __('Columns Count'),
            'values' => $this->_ruleSourceColumns->toOptionArray(),
            'note'     => __('Count of columns in products grid')
        ));

        $layoutFieldset->addField('layout_include_navigation', 'select', array(
            'name'     => 'layout_include_navigation',
            'label'    => __('Include Navigation'),
            'options' => ['1' => __('Yes'), '0' => __('No')]
        ));

        $layoutFieldset->addField('layout_heading', 'text', array(
            'name'     => 'layout_heading',
            'label'    => __('Heading'),
        ));

        $layoutFieldset->addField('layout_file', 'image', array(
            'name'     => 'layout_file',
            'note' => __('Supported formats: jpg,jpeg,gif,png'),
            'label'    => __('Image'),
        ));

        $layoutFieldset->addField('layout_file_alt', 'text', array(
            'name'     => 'layout_file_alt',
            'label'    => __('Image Alt'),
        ));

        $layoutFieldset->addField(
            'layout_top_description',
            'editor',
                [
                   'name' => 'layout_top_description',
                   'label' => __('Top Description'),
                   'title' => __('Top Description'),
                   'style' => 'width:725px;height:360px',
                   'force_load' => true,
                   'config' => $this->_wysiwygConfig->getConfig()
                ]
        );

        $layoutFieldset->addField(
            'layout_bottom_description',
            'editor',
                [
                   'name' => 'layout_bottom_description',
                   'label' => __('Bottom Description'),
                   'title' => __('Bottom Description'),
                   'style' => 'width:725px;height:360px',
                   'force_load' => true,
                   'config' => $this->_wysiwygConfig->getConfig()
                ]
        );

        $layoutFieldset->addField('layout_static_top', 'select', array(
            'name'     => 'layout_static_top',
            'label'    => __('Top Static Block'),
            'values' => $this->_categoryAttributeSourcePage->getAllOptions(),
            'note'     => __('Choose Static Block to show Above Products List')
        ));

        $layoutFieldset->addField('layout_static_bottom', 'select', array(
            'name'     => 'layout_static_bottom',
            'label'    => __('Bottom Static Block'),
            'values' => $this->_categoryAttributeSourcePage->getAllOptions(),
            'note'     => __('Choose Static Block to show Below Products List')
        ));

        $attribute = $this->_eavConfig->getAttribute(\Magento\Catalog\Model\Category::ENTITY, "default_sort_by");

        $this->_setFieldset(array($attribute), $layoutFieldset);

        $layoutFieldset->addField(
            'layout_update_xml',
            'textarea',
            [
                'name' => 'layout_update_xml',
                'label' => __('Layout Update XML'),
                'style' => 'height:24em;',
                'disabled' => $isElementDisabled
            ]
        );

        $form->addValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Design');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Design');
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

    protected function _getAdditionalElementTypes()
    {
        return [
            'image' => 'Amasty\Xlanding\Block\Adminhtml\Page\Helper\Image',
        ];
    }
}
