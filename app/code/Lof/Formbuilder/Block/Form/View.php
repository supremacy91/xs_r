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

namespace Lof\Formbuilder\Block\Form;

class View extends \Lof\Formbuilder\Block\Form
{
    /**
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Catalog\Helper\Category
     */
    protected $_helper;

    /**
     * @var [type]
     */
    protected $_collection;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry                      $registry
     * @param \Lof\Formbuilder\Helper\Data                     $helper
     * @param \Lof\Formbuilder\Model\Form                      $form
     * @param \Magento\Framework\Url                           $url
     * @param \Magento\Customer\Model\Session                  $customerSession
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
        $this->_helper = $helper;
        $this->_coreRegistry = $registry;
        parent::__construct($context, $registry, $helper, $form, $url, $customerSession);
        $this->_customerSession = $customerSession;
        $form = $this->_coreRegistry->registry('current_form');
        $this->setCurrentForm($form);
    }

    protected function _addBreadcrumbs()
    {
        $breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs');
        $baseUrl = $this->_storeManager->getStore()->getBaseUrl();
        $form = $this->getCurrentForm();
        $page_title = $form->getPageTitle();
        if ($page_title == '') $page_title = $form->getTitle();
        $route = $this->_helper->getConfig('general_settings/route');
        if ($breadcrumbsBlock) {
            $breadcrumbsBlock->addCrumb(
                'home',
                [
                    'label' => __('Home'),
                    'title' => __('Go to Home Page'),
                    'link' => $baseUrl
                ]
            );
            $breadcrumbsBlock->addCrumb(
                'lofformbuilder',
                [
                    'label' => trim($page_title),
                    'title' => trim($page_title),
                    'link' => ''
                ]
            );
        }
    }

    public function setCollection($collection)
    {
        $this->_collection = $collection;
        return $this->_collection;
    }

    public function getCollection()
    {
        return $this->_collection;
    }

    protected function _prepareLayout()
    {
        $form = $this->getCurrentForm();
        $page_title = $form->getPageTitle();
        if ($page_title == '') $page_title = $form->getTitle();
        $meta_description = $form->getMetaDescription();
        $meta_keywords = $form->getMetaKeywords();

        $this->_addBreadcrumbs();
        $this->pageConfig->addBodyClass('formbuilder-form-' . $form->getIdentifier());
        if ($page_title) {
            $this->pageConfig->getTitle()->set($page_title);
        }
        if ($meta_keywords) {
            $this->pageConfig->setKeywords($meta_keywords);
        }
        if ($meta_description) {
            $this->pageConfig->setDescription($meta_description);
        }
        return parent::_prepareLayout();
    }
}