<?php

namespace Biztech\Manufacturer\Controller\Damesheren;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Cache\StateInterface;
use Magento\Framework\App\Cache\Frontend\Pool;
use Magento\Framework\View\Result\PageFactory;
use Biztech\Manufacturer\Model\Config;
use Biztech\Manufacturer\Model\Manufacturertext;
use Biztech\Manufacturer\Helper\Data;
class View extends \Magento\Framework\App\Action\Action
{

    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    protected $_cacheTypeList;

    /**
     * @var \Magento\Framework\App\Cache\StateInterface
     */
    protected $_cacheState;

    /**
     * @var \Magento\Framework\App\Cache\Frontend\Pool
     */
    protected $_cacheFrontendPool;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;
    protected $_storeManager;
    protected $_manutext;
    protected $_manuHelper;


    public function __construct(
        Context $context,
        TypeListInterface $cacheTypeList,
        StateInterface $cacheState,
        Pool $cacheFrontendPool,
        PageFactory $resultPageFactory,
        Config $config,
        Manufacturertext $manutext,
        Data $helperData
    )
    {
        parent::__construct($context);
        $this->_cacheTypeList = $cacheTypeList;
        $this->_cacheState = $cacheState;
        $this->_cacheFrontendPool = $cacheFrontendPool;
        $this->resultPageFactory = $resultPageFactory;
        $this->_storeManager = $config->getStoreManager();
        $this->_manutext = $manutext;
        $this->_manuHelper = $helperData;
    }

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        if ($this->_manuHelper->isEnabled()) {
            $id = $this->getRequest()->getParam('id');

            $storeId = $this->_storeManager->getStore()->getId();
            $manufacturer = $this->_manutext->getCollection()
                ->addFieldToFilter('store_id', $storeId)
                ->addFieldToFilter('manufacturer_id', $id)
                ->addFieldToFilter('status', 1)
                ->getData();

            if (count($manufacturer) > 0) {
                if ($manufacturer[0]['meta_title'] != "" && !is_null($manufacturer[0]['meta_title'])) {
                    $resultPage->getConfig()->getTitle()->set($manufacturer[0]['meta_title']);
                }

                if ($manufacturer[0]['meta_title'] != "" && !is_null($manufacturer[0]['meta_title'])) {
                }

                if ($manufacturer[0]['meta_keyword'] != "") {
                    $resultPage->getConfig()->setKeywords($manufacturer[0]['meta_keyword']);
                }

                if ($manufacturer[0]['meta_description'] != "") {
                    $resultPage->getConfig()->setDescription($manufacturer[0]['meta_description']);
                }
            } else {
                return $this->_redirect('404');
            }
        } else {
            return $this->_redirect('404');
        }

        return $resultPage;
    }

}
