<?php
/**
 * Landofcoder
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the landofcoder.com license that is
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

namespace Lof\Formbuilder\Block;

class Toplinks extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Lof\Formbuilder\Model\Modelcategory
     */
    private $_formCategory;

    /**
     * @var \Lof\Formbuilder\Model\Model
     */
    private $_form;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Lof\Formbuilder\Model\Modelcategory             $modelCategory
     * @param \Lof\Formbuilder\Model\Model                     $model
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Lof\Formbuilder\Model\Modelcategory $modelCategory,
        \Lof\Formbuilder\Model\Form $form,
        \Lof\Formbuilder\Helper\Data $helper,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->_formCategory = $modelCategory;
        $this->_form = $form;
        $this->_helper = $helper;
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->_helper->getConfig('general_settings/enable')) return;
        $collection = $this->_form->getCollection();
        $collection->addFieldToFilter("status", 1)
            ->addFieldToFilter("show_toplink", 1);
        $link = '';
        $route = $this->_helper->getConfig('general_settings/route');
        if ($route != '') $route = $route . '/';
        if ($collection->getSize()) {
            foreach ($collection as $item) {
                $link .= '<li><a href="' . $this->getUrl($route . $item->getData('identifier')) . '"> ' . $this->escapeHtml($item->getTitle()) . ' </a></li>';
            }
        }
        return $link;
    }

    public function addCustomFormLinks()
    {
        $parentBlock = $this->getParentBlock();
        if ($parentBlock) {
            //get Form Collection
            $collection = $this->_form->getCollection();
            $collection->addFieldToFilter("status", 1)
                ->addFieldToFilter("show_toplink", 1);

            $link = '';
            if ($collection->getSize()) {
                foreach ($collection as $item) {
                    $link .= '<a href="' . $item->getFormLink() . '"> ' . $this->escapeHtml($item->getTitle()) . ' </a>';
                }
            }
        }
        return $this;
    }
}