<?php

namespace IntechSoft\Discount\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Class Discount
 * @package IntechSoft\Discount\Model
 */
class Discount extends AbstractModel
{
    const XML_PATH_REINDEX_TYPE = 'intechsoft/basic/enabled';
    const MAXTIMEVALUE = 2140000000;
    const ATTRIBUTECODE = 'attribute_for_sale';

    const ATTRIBUTECODE_SALE_VALUE = 'for_sale';
    const ATTRIBUTECODE_NOTSALE_VALUE = 'not_for_sale';
    const ENTITYTYPE = 'catalog_product';
    const FORSALEOPTION = 'for_sale';
    const XML_PATH_SALE_CATEGORY_ID = 'intechsoft/basic/salecategoryid';

    protected $_scopeConfig;
    protected $_logger;
    protected $productCollectionFactory;

    private $productRepository;
    private $searchCriteriaBuilder;
    private $sortOrderBuilder;
    private $storeManager;

    /**
     * Discount constructor.
     *
     * @param ProductRepositoryInterface $productRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SortOrderBuilder $sortOrderBuilder
     * @param StoreManagerInterface $storeManager
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(ProductRepositoryInterface $productRepository,
                                SearchCriteriaBuilder $searchCriteriaBuilder,
                                SortOrderBuilder $sortOrderBuilder,
                                StoreManagerInterface $storeManager,
                                \Magento\Framework\Model\Context $context,
                                \Magento\Framework\Registry $registry,
                                \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
                                \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
                                array $data = []

    )
    {

        $this->productRepository = $productRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->storeManager = $storeManager;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * fetching the Discount data value and setting the corresponding custom attribute dropdown option.
     */
    public function execute()
    {
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $productCollection = $this->productRepository
            ->getList($searchCriteria)
            ->getItems();

        $this->storeManager->setCurrentStore('admin');
        foreach ($productCollection as $product) {
            $saleValue = '';
            $discountData = $product->getData('discount');
            if ($discountData == 'New Collection') {
                $saleValue = self::ATTRIBUTECODE_NOTSALE_VALUE;
            } else if ($discountData == 'Sale') {
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
                ->load();

            foreach ($attributeOptionAll as $attributeOption) {
                $optionLabelValue = $product->getResource()->getAttribute(self::ATTRIBUTECODE)
                    ->getSource()->getOptionText($attributeOption->getData('option_id'));
                if ($optionLabelValue == $saleValue) {
                    $isForSaleOptionId = $attributeOption->getId();
                    $attributeOption->save();
                    $product->setData(self::ATTRIBUTECODE, $attributeOption->getData('option_id'));
                    $product->getResource()->saveAttribute($product, self::ATTRIBUTECODE);
                    break;
                }
            }
        }
    }
}