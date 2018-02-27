<?php
namespace WeltPixel\GoogleTagManager\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;

/**
 * Gtm section
 */
class Gtm extends \Magento\Framework\DataObject implements SectionSourceInterface
{

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * Customer group repository
     *
     * @var \Magento\Customer\Api\GroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * @var \WeltPixel\GoogleTagManager\Helper\Data
     */
    protected $gtmHelper;

    /**
     * Constructor
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Checkout\Model\Session $_checkoutSession
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Api\GroupRepositoryInterface $groupRepository
     * @param \WeltPixel\GoogleTagManager\Helper\Data $gtmHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Checkout\Model\Session $_checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository,
        \WeltPixel\GoogleTagManager\Helper\Data $gtmHelper,
        array $data = []
    )
    {
        parent::__construct($data);
        $this->jsonHelper = $jsonHelper;
        $this->_checkoutSession = $_checkoutSession;
        $this->customerSession = $customerSession;
        $this->groupRepository = $groupRepository;
        $this->gtmHelper = $gtmHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function getSectionData()
    {

        $data = [];

        /**
         * AddToCart data verifications
         */
        if ($this->_checkoutSession->getAddToCartData()) {
            $data[] = $this->_checkoutSession->getAddToCartData();
        }

        $this->_checkoutSession->setAddToCartData(null);

        /**
         * RemoveFromCart data verifications
         */
        if ($this->_checkoutSession->getRemoveFromCartData()) {
            $data[] = $this->_checkoutSession->getRemoveFromCartData();
        }

        $this->_checkoutSession->setRemoveFromCartData(null);

        /**
         * Checkout Steps data verifications
         */
        if ($this->_checkoutSession->getCheckoutOptionsData()) {
            $data[] = $this->_checkoutSession->getCheckoutOptionsData();
        }
        $this->_checkoutSession->setCheckoutOptionsData(null);

        $customDimensionData = $this->getCustomDimensions();
        if ($customDimensionData) {
            $data[] = $customDimensionData;
        }


        return [
            'datalayer' => $this->jsonHelper->jsonEncode($data)
        ];
    }

    /**
     * @return array
     */
    protected function getCustomDimensions()
    {
        $result = [];

        $customerDimensions = $this->_addCustomerDimensionOptions();
        $result = array_merge($result, $customerDimensions);

        return $result;
    }

    /**
     * Add Customer related Custom Dimensions
     */
    protected function _addCustomerDimensionOptions()
    {
        $result = [];
        if ($this->gtmHelper->isCustomDimensionCustomerIdEnabled()) {
            $customerId = ($this->customerSession->isLoggedIn()) ? $this->customerSession->getCustomerId() : 'NOT LOGGED IN';
            $result['CustomerId'] = $customerId;
        }

        if ($this->gtmHelper->isCustomDimensionCustomerGroupEnabled()) {
            $customerGroup = 'NOT LOGGED IN';
            if ($this->customerSession->isLoggedIn()) {
                $customerGroupId = $this->customerSession->getCustomerGroupId();
                $groupObj = $this->groupRepository->getById($customerGroupId);
                $customerGroup = $groupObj->getCode();
            }

            $result['CustomerGroup'] = $customerGroup;
        }

        return $result;
    }

}
