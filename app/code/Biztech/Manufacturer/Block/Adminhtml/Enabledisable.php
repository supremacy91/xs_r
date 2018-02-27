<?php
namespace Biztech\Manufacturer\Block\Adminhtml;

use Biztech\Manufacturer\Helper\Data;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Store\Model\Store;
use Magento\Store\Model\Website;

class Enabledisable extends Field
{

    const XML_PATH_ACTIVATION = 'manufacturer/activation/key';

    protected $scopeConfig;
    protected $helper;
    protected $resourceConfig;
    protected $web;
    protected $store;

    /**
     * Enabledisable constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Biztech\Manufacturer\Helper\Data $helper
     * @param \Magento\Config\Model\ResourceModel\Config $resourceConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\Website $web
     * @param \Magento\Store\Model\Store $store
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $helper,
        Config $resourceConfig,
        Website $web,
        Store $store,
        Http $Request,
        array $data = []
    )
    {
        $this->helper = $helper;
        $this->storeManager = $context->getStoreManager();
        $this->web = $web;
        $this->resourceConfig = $resourceConfig;
        $this->store = $store;
        $this->scopeConfig = $context->getScopeConfig();
        $this->request = $Request;
        parent::__construct($context, $data);
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $websites = $this->helper->getAllWebsites();
        if (!empty($websites)) {
            $website_id = $this->getRequest()->getParam('website');
            $website = $this->web->load($website_id);
            if ($website && in_array($website->getWebsiteId(), $websites)) {
                $html = $element->getElementHtml();
            } elseif (!$website_id) {
                $html = $element->getElementHtml();
                $isEnabl = $this->scopeConfig->getValue('manufacturer/general/enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                if (!$isEnabl) {
                    $this->resourceConfig->saveConfig('manufacturer/general/enabled', 0, 'default', 0);
                }
            } else {
                $html = sprintf('<strong style="color:red;" class="required">%s</strong>', __('Please buy additional domains'));
            }
        } else {
            $websitecode = $this->request->getParam('website');
            $websiteId = $this->store->load($websitecode)->getWebsiteId();
            $isenabled = $this->storeManager->getWebsite($websiteId)->getConfig('manufacturer/activation/key');
            $modulestatus = $this->resourceConfig;
            if ($isenabled != null || $isenabled != '') {
                $html = sprintf('<strong class="required">%s</strong>', __('Please select a website'));
                $modulestatus->saveConfig('manufacturer/general/enabled', 0, 'default', 0);
            } else {
                $html = sprintf('<strong class="required">%s</strong>', __('Please enter a valid key'));
                $modulestatus->saveConfig('manufacturer/general/enabled', 0, 'default', 0);
            }
        }
        return $html;
    }

}
