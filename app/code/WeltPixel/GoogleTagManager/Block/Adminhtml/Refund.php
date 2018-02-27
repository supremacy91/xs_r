<?php
namespace WeltPixel\GoogleTagManager\Block\Adminhtml;

/**
 * Class \WeltPixel\GoogleTagManager\Block\Adminhtml\Refund
 */
class Refund extends \WeltPixel\GoogleTagManager\Block\Core
{

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $_backendSession;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \WeltPixel\GoogleTagManager\Helper\Data $helper
     * @param \WeltPixel\GoogleTagManager\Model\Storage $storage
     * @param \Magento\Backend\Model\Session $backendSession
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \WeltPixel\GoogleTagManager\Helper\Data $helper,
        \WeltPixel\GoogleTagManager\Model\Storage $storage,
        \Magento\Backend\Model\Session $backendSession,
        array $data = []
    )
    {
        parent::__construct($context, $helper, $storage, $data);
        $this->_backendSession = $backendSession;
    }

    /**
     * @return string
     */
    public function getDataLayerAsJson()
    {
        $this->_checkRefunds();
        return parent::getDataLayerAsJson();
    }


    private function _checkRefunds() {
        $refundsData = $this->_backendSession->getGtmrefunds();
        if ($refundsData) {
            $this->setEcommerceData('refund', $refundsData);
        }
        $this->_backendSession->unsetData('gtmrefunds');
    }
}