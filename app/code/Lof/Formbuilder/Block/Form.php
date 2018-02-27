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

namespace Lof\Formbuilder\Block;

class Form extends \Magento\Framework\View\Element\Template
{
    protected $_fields = [
        "text" => "fields/text.phtml",
        "website" => "fields/website.phtml",
        "radio" => "fields/radio.phtml",
        "dropdown" => "fields/dropdown.phtml",
        "paragraph" => "fields/textarea.phtml",
        "email" => "fields/email.phtml",
        "date" => "fields/date.phtml",
        "time" => "fields/time.phtml",
        "checkboxes" => "fields/checkboxes.phtml",
        "number" => "fields/number.phtml",
        "price" => "fields/price.phtml",
        "section_break" => "fields/section_break.phtml",
        "address" => "fields/address.phtml",
        "file_upload" => "fields/file.phtml",
        "model_dropdown" => "fields/model_dropdown.phtml",
        "subscription" => "fields/subscription.phtml",
        "rating" => "fields/rating.phtml",
        "google_map" => "fields/google_map.phtml"
    ];

    /**
     * @var \Lof\Formbuilder\Model\Form
     */
    protected $_form = null;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     * @var \Magento\Framework\Url
     */
    protected $_url;

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
        parent::__construct($context, $data);
        $this->_helper = $helper;
        $this->_form = $form;
        $this->_registry = $registry;
        $this->_url = $url;
    }

    public function setCurrentForm($form)
    {
        $this->_form = $form;
        return $this;
    }

    public function getCurrentForm()
    {
        if (!$this->_form) {
            $this->_form = $this->_registry->registry("current_form");
        }
        return $this->_form;
    }

    public function getField($field_type, $field_data)
    {
        $fieldArr = $this->_fields;
        $html = '';
        if (array_key_exists($field_type, $fieldArr)) {
            $template = $fieldArr[$field_type];
            $html = $this->getLayout()->createBlock('\Lof\Formbuilder\Block\Field')
                ->setData('field_data', $field_data)
                ->setTemplate($template)
                ->toHtml();
        }
        return $html;
    }

    public function getFormAction()
    {
        return $this->getUrl('lofformbuilder/form/post');
    }

    public function getConfig($key, $default = '')
    {
        if ($this->hasData($key)) {
            return $this->getData($key);
        }
        $result = $this->_helper->getConfig($key);
        if ($result != NULL) return $result;
        return $default;
    }

    public function getCurrentUrl()
    {
        $url = $this->_url->getCurrentUrl();
        return $url;
    }
}