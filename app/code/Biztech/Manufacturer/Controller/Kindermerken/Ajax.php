<?php

namespace Biztech\Manufacturer\Controller\Kindermerken;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Cache\StateInterface;
use Magento\Framework\App\Cache\Frontend\Pool;
use Magento\Framework\View\Result\PageFactory;
use Biztech\Manufacturer\Model\Config;
use Biztech\Manufacturer\Model\Manufacturertext;

class Ajax extends \Magento\Framework\App\Action\Action
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
    protected $manutext;


    /**
     * Ajax constructor.
     * @param Context $context
     * @param TypeListInterface $cacheTypeList
     * @param StateInterface $cacheState
     * @param Pool $cacheFrontendPool
     * @param PageFactory $resultPageFactory
     * @param Config $config
     * @param Manufacturertext $manutext
     */
    public function __construct(
        Context $context,
        TypeListInterface $cacheTypeList,
        StateInterface $cacheState,
        Pool $cacheFrontendPool,
        PageFactory $resultPageFactory,
        Config $config,
        Manufacturertext $manutext
    ){
        parent::__construct($context);
        $this->_cacheTypeList = $cacheTypeList;
        $this->_cacheState = $cacheState;
        $this->_cacheFrontendPool = $cacheFrontendPool;
        $this->resultPageFactory = $resultPageFactory;
        $this->_storeManager = $config->getStoreManager();
        $this->manutext = $manutext;
    }

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $layout = $resultPage->getLayout();
        $block = $layout->getBlock('manufacturer');
        if ($this->getRequest()->getParam('char')) {
            return $this->getResponse()->setBody($block->toHtml());
        } else {
            $this->_redirect('manufacturer');
        }
        return $resultPage;
    }

}
