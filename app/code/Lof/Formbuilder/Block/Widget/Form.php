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

namespace Lof\Formbuilder\Block\Widget;

class Form extends \Lof\Formbuilder\Block\Form implements \Magento\Widget\Block\BlockInterface
{
    /**
     * @var \Lof\Formbuilder\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Lof\Formbuilder\Model\Menu
     */
    protected $_form;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface       $storeManager
     * @param \Lof\Formbuilder\Helper\Data                     $helper
     * @param \Lof\Formbuilder\Model\Menu                      $menu
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Lof\Formbuilder\Helper\Data $helper,
        \Lof\Formbuilder\Model\Form $form,
        \Magento\Framework\Url $url,
        \Magento\Customer\Model\Session $customerSession,
        array $data = []
    )
    {
        parent::__construct($context, $registry, $helper, $form, $url, $customerSession);
        $this->_helper = $helper;
        $this->_form = $form;
        $this->_customerSession = $customerSession;

        $my_template = "widget/form.phtml";

        if ($this->hasData("template") && $this->getData("template")) {
            $my_template = $this->getData("template");
        }

        $this->setTemplate($my_template);
    }

    public function _toHtml()
    {
        $store = $this->_storeManager->getStore();
        $html = $form = '';
        if ($formId = $this->getData('formid')) {
            $form = $this->_form->setStore($store)->load((int)$formId);

            $customergroups = $form->getData('customergroups');
            $customerGroupId = $this->_customerSession->getCustomerId();
            if (!in_array(0, $customergroups)) {
                if (!in_array($customerGroupId, $customergroups)) return;
            }
        }
        if ($form && $form->getStatus()) {
            $this->setCurrentForm($form);
        }
        return parent::_toHtml();
    }
}