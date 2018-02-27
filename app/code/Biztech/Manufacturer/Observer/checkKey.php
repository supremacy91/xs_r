<?php

namespace Biztech\Manufacturer\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Cache\Frontend\Pool;
use Magento\Framework\App\Cache\TypeListInterface;

class checkKey implements ObserverInterface
{

    const XML_PATH_ACTIVATIONKEY = 'manufacturer/activation/key';
    const XML_PATH_DATA = 'manufacturer/activation/data';

    protected $_scopeConfig;
    protected $encryptor;
    protected $_configFactory;
    protected $_helper;
    protected $_objectManager;
    protected $_request;
    protected $_resourceConfig;
    protected $configModel;
    protected $_configValueFactory;
    protected $_zend;
    protected $_cacheFrontendPool;
    protected $_cacheTypeList;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Config\Model\Config\Factory $configFactory,
        \Biztech\Manufacturer\Helper\Data $helper,
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        \Magento\Framework\App\RequestInterface $request,
        \Zend\Json\Json $zend,
        \Magento\Config\Model\ResourceModel\Config $resourceConfig,
        \Magento\Framework\App\Config\ValueFactory $configValueFactory,
        \Magento\Config\Model\Config $configModel,
        Pool $cacheFrontendPool,
        TypeListInterface $cacheTypeList
        )
    {
        $this->_scopeConfig = $scopeConfig;
        $this->encryptor = $encryptor;
        $this->_configFactory = $configFactory;
        $this->_helper = $helper;
        $this->_objectManager = $objectmanager;
        $this->_request = $request;
        $this->_zend = $zend;
        $this->_resourceConfig = $resourceConfig;
        $this->configModel = $configModel;
        $this->_configValueFactory = $configValueFactory;
        $this->_cacheFrontendPool = $cacheFrontendPool;
        $this->_cacheTypeList = $cacheTypeList;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($observer->getData()['website'] != '' || $observer->getData()['store'] != '') {
            return;
        }
        $k = $this->_scopeConfig->getValue(self::XML_PATH_ACTIVATIONKEY, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $s = '';
        $ch = curl_init();
        // curl_setopt($ch, CURLOPT_URL, sprintf('http://store.biztechconsultancy.com/extension/licence.php'));
        curl_setopt($ch, CURLOPT_URL, sprintf('http://www.appjetty.com/extension/licence.php'));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, 'key=' . urlencode($k) . '&domains=' . urlencode(implode(',', $this->_helper->getAllStoreDomains())) . '&sec=magento2-manufacturerpro');
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        $content = curl_exec($ch);
        $res1 = json_decode($content);
        $res = (array)$res1;

        if (isset($res['suc']) && $res['suc'] == 0) {
            curl_setopt($ch, CURLOPT_URL, sprintf('http://store.biztechconsultancy.com/extension/licence.php'));
        // curl_setopt($ch, CURLOPT_URL, sprintf('http://www.appjetty.com/extension/licence.php'));
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, 'key=' . urlencode($k) . '&domains=' . urlencode(implode(',', $this->_helper->getAllStoreDomains())) . '&sec=magento2-manufacturerpro');
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            $content = curl_exec($ch);
            $res1 = json_decode($content);
            $res = (array)$res1;            
        }
        $modulestatus = $this->_resourceConfig;
        if (empty($res)) {
            $modulestatus->saveConfig('manufacturer/activation/key', "", 'default', 0);
            $modulestatus->saveConfig('manufacturer/general/enabled', "", 'default', 0);
            $data = $this->_scopeConfig->getValue('manufacturer/activation/data');
            $this->_resourceConfig->saveConfig('manufacturer/activation/data', $data, 'default', 0);
            $this->_resourceConfig->saveConfig('manufacturer/activation/websites', '', 'default', 0);
            return;
        }
        $data = '';
        $web = '';
        $en = '';
        if (isset($res['dom']) && intval($res['c']) > 0 && intval($res['suc']) == 1) {
            $data = $this->encryptor->encrypt(base64_encode(json_encode($res1)));
            if (!$s) {
                $params = $this->_request->getParam('groups');
                if (isset($params['activation']['fields']['websites']['value'])) {
                    $s = $params['activation']['fields']['websites']['value'];
                }
            }
            $en = $res['suc'];
            if (isset($s) && $s != null) {
                $web = $this->encryptor->encrypt($data . implode(',', $s) . $data);
            } else {
                $web = $this->encryptor->encrypt($data . $data);
            }
        } else {
            $modulestatus->saveConfig('manufacturer/activation/key', "", 'default', 0);
            $modulestatus->saveConfig('manufacturer/general/enabled', 0, 'default', 0);
        }
        $this->_resourceConfig->saveConfig('manufacturer/activation/data', $data, 'default', 0);
        $this->_resourceConfig->saveConfig('manufacturer/activation/websites', $web, 'default', 0);
        $this->_resourceConfig->saveConfig('manufacturer/activation/en', $en, 'default', 0);
        $this->_resourceConfig->saveConfig('manufacturer/activation/installed', 1, 'default', 0);

        //refresh config cache after save
        $types = ['config', 'full_page'];
        foreach ($types as $type) {
            $this->_cacheTypeList->cleanType($type);
        }
        foreach ($this->_cacheFrontendPool as $cacheFrontend) {
            $cacheFrontend->getBackend()->clean();
        }
    }

}
