<?php
namespace WeltPixel\GoogleTagManager\Observer;

use Magento\Framework\Event\ObserverInterface;

class SalesOrderCreditMemoRefundObserver implements ObserverInterface
{
    /**
     * @var \WeltPixel\GoogleTagManager\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $_backendSession;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $productRepository;

    /**
     * @param \WeltPixel\GoogleTagManager\Helper\Data $helper
     * @param \Magento\Backend\Model\Session $backendSession
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     */
    public function __construct(\WeltPixel\GoogleTagManager\Helper\Data $helper,
                                \Magento\Backend\Model\Session $backendSession,
                                \Magento\Catalog\Model\ProductRepository $productRepository)
    {
        $this->helper = $helper;
        $this->_backendSession = $backendSession;
        $this->productRepository = $productRepository;
    }
    
    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return self
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->helper->isEnabled()) {
            return $this;
        }

        $result = [];

        $creditmemo = $observer->getData('creditmemo');
        $orderIncrementId = $creditmemo->getOrder()->getIncrementId();

        $result['actionField'] = ['id' => $orderIncrementId];

        $products = [];
        foreach ($creditmemo->getAllItems() as $item) {
            // ParentId returns empty all the time!! seems to be magento bug
            $baseRowTotal = $item->getData('base_row_total', NULL);
            if (isset($baseRowTotal)) {
                $qty = $item->getQty();
                $product = $this->productRepository->getById($item->getData('product_id'));
                $products[] = [
                    'id'        => $this->helper->getGtmProductId($product),
                    'quantity'  => $qty
                ];
            }
        }

        $result['products'] = $products;

        $this->_backendSession->setGtmrefunds($result);

        return $this;
    }
}