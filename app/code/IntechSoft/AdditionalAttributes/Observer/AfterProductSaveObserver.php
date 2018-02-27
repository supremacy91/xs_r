<?php
namespace IntechSoft\AdditionalAttributes\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Model\Product as ModelProduct;

/**
 * Class AfterProductSaveObserver
 * @package
 * @author
 * @copyright
 * @version     1.0.1
 */

class AfterProductSaveObserver implements ObserverInterface
{

    const MAXTIMEVALUE = 2140000000;
    const ATTRIBUTECODE = 'attribute_for_sale';
    const ATTRIBUTECODE_SALE_VALUE = 'for_sale';
    const ATTRIBUTECODE_NOTSALE_VALUE = 'not_for_sale';
    const ENTITYTYPE = 'catalog_product';
    const FORSALEOPTION = 'for_sale';
    const XML_PATH_SALE_CATEGORY_ID = 'intechsoft/basic/salecategoryid';

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * @var $_jsHelper \Magento\Backend\Helper\Js
     */
    protected $_jsHelper;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    //protected $messageManager;
    protected $_request;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    protected $_scopeConfig;

    /**
     * CmspagesDeleteObserver constructor.
     * @param \Psr\Log\LoggerInterface $loggerInterface
     * @param \Magento\Backend\Helper\Js $jsHelper
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     */

    public function __construct(
        \Psr\Log\LoggerInterface $loggerInterface,
        \Magento\Backend\Helper\Js $jsHelper,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->_logger = $loggerInterface;
        $this->_jsHelper = $jsHelper;
        //$this->messageManager = $messageManager;
        $this->_request = $request;
        $this->productFactory = $productFactory;
        $this->_scopeConfig = $scopeConfig;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $observerProduct = $observer->getProduct();
        $productSpecialPrice = $observerProduct->getSpecialPrice();
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $saleCategoryId = $this->_scopeConfig->getValue(self::XML_PATH_SALE_CATEGORY_ID, $storeScope);

        if($productSpecialPrice > 0){
            $productSpecialPriceFinishDate = $observerProduct->getData('special_to_date');
            $productSpecialPriceStartDate = $observerProduct->getData('special_from_date');
            // check special price finish date
            $currentTime = time();
            $finishTime = 0;
            $startTime = 0;
            if($productSpecialPriceFinishDate!=null){
                $finishTime = $this->dateToSeconds($productSpecialPriceFinishDate);
            }
            if($productSpecialPriceStartDate!=null){
                $startTime = $this->dateToSeconds($productSpecialPriceStartDate);
            }


            //get is_for_sale Option id

            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

            $arrayOfCategories = $observer->getProduct()->getCategoryIds();
            if(!in_array($saleCategoryId, $arrayOfCategories)){
                $arrayOfCategories[count($arrayOfCategories)]=strval($saleCategoryId);
            }

            if($productSpecialPriceFinishDate == null && $productSpecialPriceStartDate == null){
                $observer->getProduct()->setData('sorting_new_sale', self::MAXTIMEVALUE);
                $observer->getProduct()->setCategoryIds($arrayOfCategories);
            } else if($productSpecialPriceFinishDate == null && $currentTime>$startTime){
                $observer->getProduct()->setData('sorting_new_sale', self::MAXTIMEVALUE);
                $observer->getProduct()->setCategoryIds($arrayOfCategories);
            } else if($productSpecialPriceStartDate == null && $currentTime<$finishTime){
                $observer->getProduct()->setData('sorting_new_sale', self::MAXTIMEVALUE);
                $observer->getProduct()->setCategoryIds($arrayOfCategories);
            } else if($finishTime>$currentTime && $currentTime>$startTime){
                $observer->getProduct()->setData('sorting_new_sale', self::MAXTIMEVALUE);
                $observer->getProduct()->setCategoryIds($arrayOfCategories);
            } else {
                $createDateParam = self::MAXTIMEVALUE - $this->dateToSeconds($observerProduct->getData('created_at'));
                $observer->getProduct()->setData('sorting_new_sale', $createDateParam);
                if(in_array($saleCategoryId, $arrayOfCategories)){
                    if(($key = array_search($saleCategoryId, $arrayOfCategories)) !== false) {
                        unset($arrayOfCategories[$key]);
                    }
                }
                $observer->getProduct()->setCategoryIds($arrayOfCategories);
            }

        } else {
            $createDateParam = self::MAXTIMEVALUE - $this->dateToSeconds($observerProduct->getData('created_at'));
            $observer->getProduct()->setData('sorting_new_sale', $createDateParam);
            $arrayOfCategories = $observer->getProduct()->getCategoryIds();
            if(in_array($saleCategoryId, $arrayOfCategories)){
                if(($key = array_search($saleCategoryId, $arrayOfCategories)) !== false) {
                    unset($arrayOfCategories[$key]);
                }
            }
            $observer->getProduct()->setCategoryIds($arrayOfCategories);
        }

        $discountData = $observer->getProduct()->getData('discount');
        $saleValue = '';

        if($discountData == 'New Collection'){
            $saleValue = self::ATTRIBUTECODE_NOTSALE_VALUE;
        } else if($discountData == 'Sale'){
            $saleValue = self::ATTRIBUTECODE_SALE_VALUE;
        } else {
            $saleValue = self::ATTRIBUTECODE_NOTSALE_VALUE;
        }
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $attributeInfo = $objectManager->get(\Magento\Eav\Model\Entity\Attribute::class)
                    ->loadByCode(self::ENTITYTYPE, self::ATTRIBUTECODE);

                $attributeId = $attributeInfo->getAttributeId();
                $attributeOptionAll = $objectManager->get(\Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection::class)
                    ->setPositionOrder('asc')
                    ->setAttributeFilter($attributeId)
                    ->setStoreFilter()
                    ->load();

                $isForSaleOptionId = '';
                foreach ($attributeOptionAll as $attributeOption){
                    $optionLabelValue = $attributeOption->getData('default_value');
                    if($optionLabelValue == $saleValue){
                        $isForSaleOptionId = $attributeOption->getId();
                        break;
                    }
                }
        $observer->getProduct()->setData(self::ATTRIBUTECODE, $isForSaleOptionId);

    }

    private function dateToSeconds($inputDate){
        $tempArray = explode(' ', $inputDate);
        $tempDateArray = explode('-', $tempArray[0]);
        $tempTimeArray = explode(':', $tempArray[1]);
        $timeInSeconds = mkTime($tempTimeArray[0], $tempTimeArray[1], $tempTimeArray[2], $tempDateArray[1], $tempDateArray[2], $tempDateArray[0]);
        return $timeInSeconds;
    }


}