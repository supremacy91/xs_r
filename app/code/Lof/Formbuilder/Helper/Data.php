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

namespace Lof\Formbuilder\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /** @var \Magento\Store\Model\StoreManagerInterface */
    protected $_storeManager;

    /**
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
    protected $_filterProvider;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;

    /**
     * @var \Magento\Framework\Locale\CurrencyInterface
     */
    protected $_localeCurrency;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @param \Magento\Framework\App\Helper\Context                $context
     * @param \Magento\Cms\Model\Template\FilterProvider           $filterProvider
     * @param \Magento\Store\Model\StoreManagerInterface           $storeManager
     * @param \Magento\Framework\Locale\CurrencyInterface          $localeCurrency
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\ObjectManagerInterface            $objectManager
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\ObjectManagerInterface $objectManager
    )
    {
        parent::__construct($context);
        $this->_filterProvider = $filterProvider;
        $this->_storeManager = $storeManager;
        $this->_localeDate = $localeDate;
        $this->_localeCurrency = $localeCurrency;
        $this->_objectManager = $objectManager;
    }

    public function filter($str)
    {
        $html = $this->_filterProvider->getPageFilter()->filter($str);
        return $html;
    }

    public function isAdmin()
    {
        if (Mage::app()->getStore()->isAdmin()) {
            return true;
        }

        if (Mage::getDesign()->getArea() == 'adminhtml') {
            return true;
        }

        return false;
    }

    public function getWidgetFormUrl($target_id = "")
    {
        $params = array();
        if ($target_id) {
            $params['widget_target_id'] = $target_id;
        }

        $admin_route = Mage::getConfig()->getNode('admin/routers/adminhtml/args/frontName');
        $admin_route = $admin_route ? $admin_route : "admin";

        $url = Mage::getSingleton('adminhtml/url')->getUrl('*/widget/loadOptions', $params);
        $url = str_replace("/formbuilder/", "/{$admin_route}/", $url);
        return $url;
    }

    public function getListWidgetsUrl($target_id = "")
    {
        //return Mage::helper("adminhtml")->getUrl("*/*/listwidgets");
        $params = array();
        if ($target_id) {
            $params['widget_target_id'] = $target_id;
        }

        $admin_route = Mage::getConfig()->getNode('admin/routers/adminhtml/args/frontName');
        $admin_route = $admin_route ? $admin_route : "admin";

        $url = Mage::getSingleton('adminhtml/url')->getUrl('*/widget/index', $params);
        $url = str_replace("/formbuilder/", "/{$admin_route}/", $url);
        return $url;
    }

    public function getWidgetDataUrl()
    {
        return Mage::helper("adminhtml")->getUrl("*/*/widgetdata");
    }

    public function getImageUrl()
    {
        return str_replace(array('index.php/', 'index.php'), '', Mage::getBaseUrl('media'));
    }

    public function getImportPath($theme = "")
    {
        $path = Mage::getBaseDir('var') . DS . 'cache' . DS;

        if (is_dir_writeable($path) != true) {
            mkdir($path, '0744', $recursive = true);
        } // end

        return $path;
    }

    public function getAllStores()
    {
        $allStores = Mage::app()->getStores();
        $stores = array();
        foreach ($allStores as $_eachStoreId => $val) {
            $stores[] = Mage::app()->getStore($_eachStoreId)->getId();
        }
        return $stores;
    }

    public function getIp()
    {

        //Just get the headers if we can or else use the SERVER global
        if (function_exists('apache_request_headers')) {

            $headers = apache_request_headers();

        } else {

            $headers = $_SERVER;

        }

        //Get the forwarded IP if it exists
        if (array_key_exists('X-Forwarded-For', $headers) && filter_var($headers['X-Forwarded-For'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {

            $the_ip = $headers['X-Forwarded-For'];

        } elseif (array_key_exists('HTTP_X_FORWARDED_FOR', $headers) && filter_var($headers['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)
        ) {

            $the_ip = $headers['HTTP_X_FORWARDED_FOR'];

        } else {

            $the_ip = filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);

        }

        return $the_ip;

    }

    public function getFormLink($form)
    {
        $identifier = $form->getData('identifier');
        if ($identifier != '') {
            $form_link = $identifier . '.html';
            return Mage::getUrl() . $form_link;
        }
        return '#';
    }

    /**
     * Return brand config value by key and store
     *
     * @param string                                $key
     * @param \Magento\Store\Model\Store|int|string $store
     * @return string|null
     */
    public function getConfig($key, $store = null)
    {
        $store = $this->_storeManager->getStore($store);
        $websiteId = $store->getWebsiteId();

        $result = $this->scopeConfig->getValue(
            'lofformbuilder/' . $key,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store);
        return $result;
    }

    public function formatDate(
        $date = null,
        $format = \IntlDateFormatter::SHORT,
        $showTime = false,
        $timezone = null
    )
    {
        $date = $date instanceof \DateTimeInterface ? $date : new \DateTime($date);
        return $this->_localeDate->formatDateTime(
            $date,
            $format,
            $showTime ? $format : \IntlDateFormatter::NONE,
            null,
            $timezone
        );
    }

    public function getFormatDate($date, $type = 'full')
    {
        $result = '';
        switch ($type) {
            case 'full':
                $result = $this->formatDate($date, \IntlDateFormatter::FULL);
                break;
            case 'long':
                $result = $this->formatDate($date, \IntlDateFormatter::LONG);
                break;
            case 'medium':
                $result = $this->formatDate($date, \IntlDateFormatter::MEDIUM);
                break;
            case 'short':
                $result = $this->formatDate($date, \IntlDateFormatter::SHORT);
                break;
        }
        return $result;
    }

    public function getSymbol()
    {
        $currency = $this->_localeCurrency->getCurrency($this->_storeManager->getStore()->getCurrentCurrencyCode());
        $symbol = $currency->getSymbol() ? $currency->getSymbol() : $currency->getShortName();

        if (!$symbol) $symbol = '';
        return $symbol;
    }

    public function getMediaUrl()
    {
        $storeMediaUrl = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')
            ->getStore()
            ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        return $storeMediaUrl;
    }

    public function getFieldPrefix()
    {
        return 'loffield_';
    }
}