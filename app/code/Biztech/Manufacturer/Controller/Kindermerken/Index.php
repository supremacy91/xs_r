<?php

namespace Biztech\Manufacturer\Controller\Kindermerken;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Cache\StateInterface;
use Magento\Framework\App\Cache\Frontend\Pool;
use Magento\Framework\View\Result\PageFactory;
use Biztech\Manufacturer\Model\Config;
use Biztech\Manufacturer\Model\Manufacturertext;
use Biztech\Manufacturer\Helper\Data;

class Index extends \Magento\Framework\App\Action\Action
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
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
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
        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }
}
