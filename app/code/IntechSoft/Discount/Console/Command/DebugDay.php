<?php

namespace IntechSoft\Discount\Console\Command;


use Symfony\Component\Console\Command\Command; // for parent class
use Symfony\Component\Console\Input\InputInterface; // for InputInterface used in execute method
use Symfony\Component\Console\Output\OutputInterface; // for OutputInterface used in execute method
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Store\Model\StoreManagerInterface;

class DebugDay extends Command
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

    public function __construct(ProductRepositoryInterface $productRepository,
                                SearchCriteriaBuilder $searchCriteriaBuilder,
                                SortOrderBuilder $sortOrderBuilder,
                                StoreManagerInterface $storeManager

    ) {

        $this->productRepository = $productRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->storeManager = $storeManager;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('debug:day')
            ->setDescription('Clear var/generation folder!');

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
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