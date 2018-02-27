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

namespace Lof\Formbuilder\Block\Adminhtml\Modelcategory\Edit\Tab;

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
    protected $_categoryCollection;

    protected $_drawLevel;

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
     * @param \Lof\Formbuilder\Model\ResourceModel\Modelcategory\Collection $categoryCollection
     * @param \Magento\Email\Model\Template\Config                          $emailConfig
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
        \Lof\Formbuilder\Model\ResourceModel\Modelcategory\Collection $categoryCollection,
        \Magento\Email\Model\Template\Config $emailConfig,
        array $data = []
    )
    {
        $this->_systemStore = $systemStore;
        $this->groupRepository = $groupRepository;
        $this->_objectConverter = $objectConverter;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_templatesFactory = $templatesFactory;
        $this->_emailConfig = $emailConfig;
        $this->_categoryCollection = $categoryCollection;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('formbuilder_modelcategory');

        if ($this->_isAllowedAction('Lof_Formbuilder::category_edit')) {
            $isElementDisabled = false;
        } else {
            $isElementDisabled = true;
        }

        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('form_');
        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Category Information')]);
        if ($model->getCategoryId()) {
            $fieldset->addField('category_id', 'hidden', ['name' => 'category_id']);
        }
        $fieldset->addField(
            'title',
            'text',
            [
                'name' => 'title',
                'label' => __('Name'),
                'title' => __('Name'),
                'required' => true,
                'disabled' => $isElementDisabled
            ]
        );

        $categories[] = ['label' => __('Please select'), 'value' => 0];
        $this->_drawLevel = $categories;
        $collection = $this->getCatCollection();
        $cats = [];
        foreach ($collection as $_cat) {
            if (!$_cat->getParentId()) {
                $cat = [
                    'label' => $_cat->getTitle(),
                    'value' => $_cat->getId(),
                    'id' => $_cat->getId(),
                    'parent_id' => $_cat->getParentId(),
                    'level' => 0
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
                    'value' => $_cat->getId(),
                    'id' => $_cat->getId(),
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

    public function getCatCollection()
    {
        $model = $this->_coreRegistry->registry('formbuilder_modelcategory');
        $collection = $this->_categoryCollection
            ->addFieldToFilter('category_id', array('neq' => $model->getCategoryId()));
        return $collection;
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Category Information');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Category Information');
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
}