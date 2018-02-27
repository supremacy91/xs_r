<?php
namespace Biztech\Manufacturer\Model;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory as AttributeCollectionFactory;

class Layer extends \Magento\Catalog\Model\Layer
{

//Apart from the default construct argument you need to add your model from which your product collection is fetched.

    public function __construct(
        \Magento\Catalog\Model\Layer\ContextInterface $context,
        \Magento\Catalog\Model\Layer\StateFactory $layerStateFactory,
        AttributeCollectionFactory $attributeCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Product $catalogProduct,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Registry $registry,
        CategoryRepositoryInterface $categoryRepository,
        array $data = []
    ) {
    parent::__construct(
            $context,
            $layerStateFactory,
            $attributeCollectionFactory,
            $catalogProduct,
            $storeManager,
            $registry,
            $categoryRepository,
            $data
        );
    }

    public function getProductCollection()
    {

        /*Unique id is needed so that when product is loaded /filtered in the custom listing page it will be set in the
         $this->_productCollections array with unique key else you will not get the updated or proper collection.
        */

        if (isset($this->_productCollections[144])) {
            $collection = $this->_productCollections[144];
        } else {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $collection = $objectManager->get('\Biztech\Manufacturer\Block\Manufacturer\View')->getProductCollection();
            $this->prepareProductCollection($collection);
            $this->_productCollections[144] = $collection;
        }
        return $collection;
    }
}