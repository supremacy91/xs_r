<?php
/**
 * Landofcoder
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Landofcoder.com license that is
 * available through the world-wide-web at this URL:
 * http://landofcoder.com/license
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category   Landofcoder
 * @package    Lof_Formbuilder
 * @copyright  Copyright (c) 2016 Landofcoder (http://www.landofcoder.com/)
 * @license    http://www.landofcoder.com/LICENSE-1.0.html
 */

namespace Lof\Formbuilder\Block\Adminhtml\Model\Edit\Tab;

use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Convert\DataObject as ObjectConverter;

class Main extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @var GroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $_searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Convert\DataObject
     */
    protected $_objectConverter;

    /**
     * @var \Magefan\Blog\Model\ResourceModel\Category\Collection
     */
    protected $_category;

    /**
     * [__construct description]
     *
     * @param \Magento\Backend\Block\Template\Context                       $context
     * @param \Magento\Framework\Registry                                   $registry
     * @param \Magento\Framework\Data\FormFactory                           $formFactory
     * @param GroupRepositoryInterface                                      $groupRepository
     * @param ObjectConverter                                               $objectConverter
     * @param SearchCriteriaBuilder                                         $searchCriteriaBuilder
     * @param \Magento\Store\Model\System\Store                             $systemStore
     * @param \Magento\Email\Model\ResourceModel\Template\CollectionFactory $templatesFactory
     * @param \Magento\Email\Model\Template\Config                          $emailConfig
     * @param \Lof\Formbuilder\Model\Model                                  $model
     * @param \Lof\Formbuilder\Model\Modelcategory                          $category
     * @param array                                                         $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        GroupRepositoryInterface $groupRepository,
        ObjectConverter $objectConverter,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Email\Model\ResourceModel\Template\CollectionFactory $templatesFactory,
        \Magento\Email\Model\Template\Config $emailConfig,
        \Lof\Formbuilder\Model\Model $model,
        \Lof\Formbuilder\Model\Modelcategory $category,
        array $data = []
    )
    {
        $this->_systemStore = $systemStore;
        $this->groupRepository = $groupRepository;
        $this->_objectConverter = $objectConverter;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_templatesFactory = $templatesFactory;
        $this->_emailConfig = $emailConfig;
        $this->_model = $model;
        $this->_category = $category;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('formbuilder_model');

        if ($this->_isAllowedAction('Lof_Formbuilder::model_edit')) {
            $isElementDisabled = false;
        } else {
            $isElementDisabled = true;
        }

        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('form_');
        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Model Information')]);
        if ($model->getId()) {
            $fieldset->addField('model_id', 'hidden', ['name' => 'model_id']);
        }
        $fieldset->addField(
            'title',
            'text',
            [
                'name' => 'title',
                'label' => __('Model Title'),
                'title' => __('Model Title'),
                'required' => true,
                'disabled' => $isElementDisabled
            ]
        );

        $categories[] = ['label' => __('-- Select a Parent --'), 'value' => 0];
        $this->_drawLevel = $categories;
        $collection = $this->getModelCollection();
        $cats = [];
        foreach ($collection as $_model) {
            if (!$_model->getParentId()) {
                $label = $_model->getTitle();
                if ($_model->getCategoryId()) {
                    $label = $label . '(' . ' Cat: ' . $this->_category->load($_model->getCategoryId())->getTitle() . ')';
                }
                $cat = [
                    'label' => $label,
                    'value' => $_model->getId(),
                    'id' => $_model->getId()
                ];
                $cats[] = $this->drawItems($collection, $cat);
            }
        }
        $this->drawSpaces($cats);

        if (count($this->_drawLevel)) {
            $fieldset->addField(
                'parent_id',
                'select',
                [
                    'name' => 'parent_id',
                    'label' => __('Parent'),
                    'title' => __('Parent'),
                    'values' => $this->_drawLevel,
                    'disabled' => $isElementDisabled
                ]
            );
        }
        $category = ['label' => __('-- Select a Category --'), 'value' => 0];
        $cats = $this->_category->getCollection()->toOptionArray();
        array_unshift($cats, $category);

        if (count($this->_drawLevel)) {
            $fieldset->addField(
                'category_id',
                'select',
                [
                    'name' => 'category_id',
                    'label' => __('Category'),
                    'title' => __('Category'),
                    'values' => $cats,
                    'disabled' => $isElementDisabled
                ]
            );
        }

        $fieldset->addField(
            'status',
            'select',
            [
                'label' => __('Status'),
                'title' => __('Status'),
                'name' => 'status',
                'options' => $model->getAvailableStatuses(),
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'position',
            'text',
            [
                'name' => 'position',
                'label' => __('Position'),
                'title' => __('Position'),
                'disabled' => $isElementDisabled
            ]
        );
        if (!$model->getId()) {
            $model->setData('status', $isElementDisabled ? '0' : '1');
        }

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _getSpaces($n)
    {
        $s = '';
        for ($i = 0; $i < $n; $i++) {
            $s .= '--- ';
        }
        return $s;
    }

    public function drawItems($collection, $cat, $level = 0)
    {
        foreach ($collection as $_cat) {
            if ($_cat->getParentId() == $cat['id']) {
                $cat1 = [
                    'label' => $_cat->getTitle(),
                    'value' => $_cat->getModelId(),
                    'id' => $_cat->getModelId(),
                    'parent_id' => $_cat->getParentId(),
                    'level' => 0,
                    'postion' => $_cat->getCatPosition()
                ];
                $children[] = $this->drawItems($collection, $cat1, $level + 1);
                $cat['children'] = $children;
            }
        }
        $cat['level'] = $level;
        return $cat;
    }

    public function getModelCollection()
    {
        $model = $this->_coreRegistry->registry('formbuilder_model');
        $collection = $this->_model->getCollection()
            ->addFieldToFilter('model_id', array('neq' => $model->getModelId()));
        return $collection;
    }

    public function drawSpaces($cats)
    {
        if (is_array($cats)) {
            foreach ($cats as $k => $v) {
                $v['label'] = $this->_getSpaces($v['level']) . $v['label'];
                $this->_drawLevel[] = $v;
                if (isset($v['children']) && $children = $v['children']) {
                    $this->drawSpaces($children);
                }
            }
        }
    }

    public function getTabLabel()
    {
        return __('Model Information');
    }

    public function getTabTitle()
    {
        return __('Model Information');
    }

    public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
        return false;
    }

    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
