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

namespace Lof\Formbuilder\Model;

class Form extends \Magento\Framework\Model\AbstractModel
{

    const CACHE_BLOCK_TAG = 'lof_formbuilder_block';
    const CACHE_PAGE_TAG = 'lof_formbuilder_page';
    const CACHE_MEDIA_TAG = 'lof_formbuilder_media';


    const FORM_ID = 'form_id';

    /**
     * CMS block cache tag
     */
    const CACHE_TAG = 'formbuilder_form';

    /**#@+
     * Form's Statuses
     */
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;

    /**
     * @var string
     */
    protected $_cacheTag = 'formbuilder_form';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'formbuilder_form';

    /**
     * Product collection factory
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory;

    /** @var \Magento\Store\Model\StoreManagerInterface */
    protected $_storeManager;

    /**
     * URL Model instance
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $_url;

    /**
     * @var \Magento\Catalog\Helper\Category
     */
    protected $_blogHelper;

    protected $_resource;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    protected $_store;

    /**
     * [__construct description]
     *
     * @param \Magento\Framework\Model\Context                          $context
     * @param \Magento\Framework\Registry                               $registry
     * @param \Lof\Formbuilder\Model\ResourceModel\Form|null            $resource
     * @param \Lof\Formbuilder\Model\ResourceModel\Form\Collection|null $resourceCollection
     * @param \Magento\Store\Model\StoreManagerInterface                $storeManager
     * @param \Magento\Framework\UrlInterface                           $url
     * @param \Magento\Framework\App\Config\ScopeConfigInterface        $scopeConfig
     * @param \Lof\Formbuilder\Helper\Data                              $helper
     * @param \Magento\Newsletter\Model\Subscriber                      $subscriber
     * @param \Magento\Directory\Model\Country                          $country
     * @param \Magento\Framework\Pricing\Helper\Data                    $currency
     * @param \Magento\Framework\Filter\FilterManager                   $filterManager
     * @param array                                                     $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Lof\Formbuilder\Model\ResourceModel\Form $resource = null,
        \Lof\Formbuilder\Model\ResourceModel\Form\Collection $resourceCollection = null,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Lof\Formbuilder\Helper\Data $helper,
        \Magento\Newsletter\Model\Subscriber $subscriber,
        \Magento\Directory\Model\Country $country,
        \Magento\Framework\Pricing\Helper\Data $currency,
        \Magento\Framework\Filter\FilterManager $filterManager,
        array $data = []
    )
    {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->_resource = $resource;
        $this->scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
        $this->_url = $url;
        $this->_helper = $helper;
        $this->_subscriber = $subscriber;
        $this->_country = $country;
        $this->_currency = $currency;
        $this->filterManager = $filterManager;
    }


    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Lof\Formbuilder\Model\ResourceModel\Form');
    }

    /**
     * Prevent blocks recursion
     *
     * @return \Magento\Framework\Model\AbstractModel
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeSave()
    {
        $needle = 'form_id="' . $this->getId() . '"';
        if (false == strstr($this->getContent(), $needle)) {
            return parent::beforeSave();
        }
        throw new \Magento\Framework\Exception\LocalizedException(
            __('Make sure that static form content does not reference the form itself.')
        );
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId(), self::CACHE_TAG . '_' . $this->getIdentifier()];
    }

    /**
     * Retrieve block id
     *
     * @return int
     */
    public function getId()
    {
        return $this->getData(self::FORM_ID);
    }

    /**
     * Receive page store ids
     *
     * @return int[]
     */
    public function getStores()
    {
        return $this->hasData('stores') ? $this->getData('stores') : $this->getData('store_id');
    }

    /**
     * Prepare page's statuses.
     * Available event cms_page_get_available_statuses to customize statuses.
     *
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [self::STATUS_ENABLED => __('Enabled'), self::STATUS_DISABLED => __('Disabled')];
    }

    /**
     * Prepare page's statuses.
     * Available event cms_page_get_available_statuses to customize statuses.
     *
     * @return array
     */
    public function getYesno()
    {
        return [self::STATUS_ENABLED => __('Yes'), self::STATUS_DISABLED => __('No')];
    }

    public function getFields()
    {
        $fields = json_decode('[' . $this->getData('design') . ']', TRUE);
        if (isset($fields[0]['fields'])) {
            $fields[0] = $fields[0]['fields'];
            $fls = [];
            foreach ($fields[0] as $k => $v) {
                if (isset($v['field_type'])) {
                    $fls[] = $v;
                }
            }
            $fields[0] = $fls;
        }
        if (isset($fields[0])) {
            return $fields[0];
        }
        return;
    }

    /**
     * Check if page identifier exist for specific store
     * return page id if page exists
     *
     * @param string $identifier
     * @param int    $storeId
     * @return int
     */
    public function checkIdentifier($identifier, $storeId)
    {
        return $this->_getResource()->checkIdentifier($identifier, $storeId);
    }

    public function checkCustomerGroup($identifier, $customerGroupId)
    {
        return $this->_getResource()->checkCustomerGroup($identifier, $customerGroupId);
    }

    public function checkFormAvailable($block_profile = null)
    {
        $checked = true;
        if ($block_profile) {
            if (1 != $block_profile->getStatus()) {
                $checked = false;
            } else {
                $customer_group_id = (int)Mage::getSingleton('customer/session')->getCustomerGroupId();
                $customer_group = $block_profile->getCustomerGroup();
                $array_groups = explode(",", $customer_group);
                if ($array_groups && !in_array(0, $array_groups) && !in_array($customer_group_id, $array_groups)) {
                    $checked = false;
                }
            }
        }
        return $checked;
    }

    public function getCustomFormFields($post_data = array())
    {
        if (0 < $this->getId() && $post_data) {
            $form_data = array();
            $custom_fields = array();

            $emails = array();
            $is_subscription = false;

            if ($custom_fields = $this->getFields()) {

                foreach ($custom_fields as $i => $field) {

                    $field_id = "loffield_" . $field['cid'];
                    $field_type = $field['field_type'];
                    $field_value = "";

                    if (isset($post_data[$field_id])) {
                        $tmp = $field;
                        $field_value = isset($post_data[$field_id]) ? $post_data[$field_id] : "";
                        switch ($field_type) {
                            case 'website':
                                $field_value = '<a href="' . $field_value . '" target="_BLANK">' . $field_value . '</a>';

                                break;
                            case 'email':
                                $emails[] = trim($field_value);
                                $tmp['thanks_email'] = trim($field_value);
                                $field_value = '<a href="mailto:' . trim($field_value) . '" target="_BLANK">' . $field_value . '</a>';

                                break;
                            case 'radio':

                                if ($field_value == "other" && isset($post_data[$field_id . "_other"]) && $post_data[$field_id . "_other"]) {
                                    $field_value = $post_data[$field_id . "_other"];
                                }
                                if (strpos($field_value, "{{") !== false) {
                                    $field_value = str_replace(array("{{", "}}"), array('<img src="{{', '}}" alt="img"/>'), $field_value);
                                    $field_value = $this->_helper->filter($field_value);
                                }

                                break;
                            case 'checkboxes':
                                if (is_array($field_value) && $field_value) {
                                    foreach ($field_value as $j => $value) {
                                        if ($value == "other" && isset($post_data[$field_id . "_other"]) && $post_data[$field_id . "_other"]) {

                                            $field_value[$j] = $post_data[$field_id . "_other"];
                                        }

                                        if (strpos($field_value[$j], "{{") !== false) {
                                            $field_value[$j] = str_replace(array("{{", "}}"), array('<img src="{{', '}}" alt="img"/>'), $field_value[$j]);
                                            $field_value[$j] = $this->_helper->filter($field_value[$j]);
                                        }
                                    }
                                }
                                if (is_array($field_value)) {
                                    $field_value = implode(", ", $field_value);
                                }
                                break;
                            case 'address':
                                $street = isset($post_data[$field_id . "_street"]) ? $post_data[$field_id . "_street"] : "";
                                $city = isset($post_data[$field_id . "_city"]) ? $post_data[$field_id . "_city"] : "";
                                $state = isset($post_data[$field_id . "_state"]) ? $post_data[$field_id . "_state"] : "";
                                $zipcode = isset($post_data[$field_id . "_zipcode"]) ? $post_data[$field_id . "_zipcode"] : "";
                                $country = isset($post_data[$field_id . "_country"]) ? $post_data[$field_id . "_country"] : "";
                                //$country = $this->_country->loadByCode($country_code);
                                //$country_name = $country->getName();
                                $field_value = $this->formatAddress($street, $city, $state, $zipcode, $country);
                                break;
                            case 'file_upload':
                                $field_value = '<a href="' . $post_data[$field_id . "_fileurl"] . '" target="_BLANK">';
                                if (isset($post_data[$field_id . "_isimage"])) {
                                    $field_value .= '<div><img style="width: 150px" src="' . $post_data[$field_id . "_fileurl"] . '"/></div>';
                                }
                                $field_value .= $post_data[$field_id . "_filename"] . ' - (' . round($post_data[$field_id . "_filesize"], 2) . 'Kb)';
                                $field_value .= '</a>';
                                break;
                            case 'model_dropdown':
                                if ($field_value && is_array($field_value)) {
                                    $tmp_models = array();
                                    $k = 1;
                                    foreach ($field_value as $key => $fitem) {
                                        $tmp2 = array();
                                        if (is_array($fitem)) {
                                            foreach ($fitem as $k2 => $fitem2) {
                                                $tmp2[] = $fitem2;
                                            }
                                        } else {
                                            $tmp2 = array($fitem);
                                        }
                                        if ($tmp2 && $fitem) {
                                            $tmp_models[] = $k . ". " . implode(" > ", $tmp2);
                                        }

                                        $k++;
                                    }
                                    $field_value = implode("<br/>", $tmp_models);
                                }

                                break;
                            case 'price':
                                $field_value = $this->_currency->currency($field_value, true, false);
                                break;
                            case 'time':
                                $hours = isset($post_data[$field_id . "_hours"]) ? $post_data[$field_id . "_hours"] : "";
                                $minutes = isset($post_data[$field_id . "_minutes"]) ? $post_data[$field_id . "_minutes"] : "";
                                $seconds = isset($post_data[$field_id . "_seconds"]) ? $post_data[$field_id . "_seconds"] : "";
                                $am_pm = isset($post_data[$field_id . "_am_pm"]) ? $post_data[$field_id . "_am_pm"] : "";
                                $field_value = $hours . ':' . $minutes . ':' . $seconds . ' ' . $am_pm;
                                break;
                            case 'google_map':
                                $location = $field_value;
                                $lat = isset($post_data[$field_id . "_lat"]) ? $post_data[$field_id . "_lat"] : "";
                                $long = isset($post_data[$field_id . "_long"]) ? $post_data[$field_id . "_long"] : "";
                                $rand = isset($post_data[$field_id . "_radius"]) ? $post_data[$field_id . "_radius"] : "";

                                $field_value = $location . "<br/>" . __("Latitude: %1", $lat) . " , " . __("Longtitude: %1", $long);
                                break;
                            case 'subscription':
                                $field_value = isset($post_data[$field_id . '0']) ? $post_data[$field_id . '0'] : "";

                                if (is_array($field_value) && $field_value) {
                                    $field_value = $field_value[0];
                                }
                                if ($field_value == 1) {
                                    $is_subscription = true;
                                }

                                $field_value = "";
                                $tmp['subscription'] = true;
                                break;
                            case 'rating':
                                $limit = isset($post_data[$field_id . "_limit"]) ? (int)$post_data[$field_id . "_limit"] : 5;
                                $rating_value = (float)$field_value;
                                if ($limit) {
                                    $field_value = '<div class="rating small">';
                                    for ($i = 1; $i <= $limit; $i++) {
                                        $fclass = "";
                                        if ($i <= $rating_value) {
                                            $fclass = 'on';
                                        }
                                        $field_value .= '<span class="star ' . $fclass . '">&nbsp;</span>';
                                    }
                                    $field_value .= '<span class="score">' . __("%1 stars", $rating_value) . '</span>';
                                    $field_value .= '</div>';
                                }
                                break;
                        }
                        $tmp['value'] = $field_value;
                        $form_data[] = $tmp;
                    }
                }

                /*Active Subscription For There Emails*/
                if ($is_subscription && $emails) {
                    foreach ($emails as $email) {
                        $status = $this->_subscriber->subscribe($email);
                    }
                }
                return $form_data;
            }
        }
        return false;
    }

    public function getFormLink()
    {
        $route = $this->getConfig('general_settings/route');
        return $route;
    }

    public function formatAddress($street = "", $city = "", $state = "", $zipcode = "", $country = "")
    {
        $address_format = $this->_helper->getConfig("field_templates/address");
        $data = [
            "street" => $street,
            "city" => $city,
            "region" => $state,
            "postcode" => $zipcode,
            "country" => $country
        ];
        if ($address_format == '') return $street . ', ' . $city . ', ' . $state . ', ' . $zipcode . ', ' . $country;
        $addressText = $this->filterManager->template($address_format, ['variables' => $data]);
        return $addressText;
    }
}