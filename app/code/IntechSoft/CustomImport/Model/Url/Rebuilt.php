<?php
namespace IntechSoft\CustomImport\Model\Url;

use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

class Rebuilt
{

    const ENTITY_TYPE = 'product';

    /**
     * XML path for category url suffix
     */
    const XML_PATH_CATEGORY_URL_SUFFIX = 'catalog/seo/category_url_suffix';

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectmanager;

    /** @var UrlFinderInterface */
    protected $urlFinder;

    /**
     * @var \Magento\UrlRewrite\Model\UrlRewrite
     */
    protected $urlRewrite;

    /**
     * @var \Magento\UrlRewrite\Model\UrlPersistInterface
     */
    protected $urlPersistInterface;

    /**
     * @var \Magento\Catalog\Model\ProductFactory productFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\Catalog\Model\Product\Attribute\Source\Status
     */
    protected $productStatus;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $productVisibility;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $categoryColFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /** @var \Magento\Framework\App\Config\ScopeConfigInterface */
    protected $scopeConfig;

    /** @var \Magento\Catalog\Api\CategoryRepositoryInterface */
    protected $categoryRepository;

    protected $rebuildCounter = 0;

    protected $baseUrl;

    /**
     * Uninstall constructor.
     * @param \Magento\UrlRewrite\Model\UrlRewrite $urlRewrite
     * @param \Magento\UrlRewrite\Model\UrlPersistInterface $urlPersistInterface
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productFactory
     * @param UrlFinderInterface $urlFinder
     * @param \Magento\Catalog\Model\Product\Attribute\Source\Status $productStatus
     * @param \Magento\Catalog\Model\Product\Visibility $productVisibility
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryColFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        \Magento\UrlRewrite\Model\UrlPersistInterface $urlPersistInterface,
        \Magento\UrlRewrite\Model\UrlRewrite $urlRewrite,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productFactory,
        UrlFinderInterface $urlFinder,
        \Magento\Catalog\Model\Product\Attribute\Source\Status $productStatus,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryColFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository
    )
    {
        $this->_objectmanager = $objectmanager;
        $this->urlFinder = $urlFinder;
        $this->urlRewrite = $urlRewrite;
        $this->urlPersistInterface = $urlPersistInterface;
        $this->_productFactory   = $productFactory;
        $this->productStatus = $productStatus;
        $this->productVisibility = $productVisibility;
        $this->categoryColFactory = $categoryColFactory;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->categoryRepository = $categoryRepository;
        $this->baseUrl = $this->storeManager->getStore()->getBaseUrl();
        $this->categoryUrlSuffix = $this->getCategoryUrlSuffix($this->storeManager->getStore()->getId());
    }


    /**
     * @return string
     */
    public function rebuildProductUrlRewrites()
    {
        $productCollection = $this->getProductCollection();
        $this->rebuildRewrites($productCollection);
        if ($this->rebuildCounter) {
            return $this->rebuildCounter." products urls  was rebuild successfuly";
        } else {
            return "nothing to rebuild";
        }
    }

    /**
     * @param $product
     */
    public function rebuildSingleProductUrlRewrites($product)
    {
        $this->rebuildProductRewrite($product);
    }

    /**
     * @param $product
     */
    public function setDefaultCategoryToProduct($product)
    {
        $defaultCategoryId = $product->getCategoryCollection()->getFirstItem()->getEntityId();
        $defaultCategoryName = $this->categoryRepository->get($defaultCategoryId)->getName();
        $product->setDefaultCategory($defaultCategoryName);
    }

    /**
     * @param $product
     * @return UrlRewrite[]
     */
    protected function getCurrentRewrite($product)
    {
        $currentUrlRewrites = $this->urlFinder->findAllByData(
            [
                UrlRewrite::ENTITY_ID => $product->getEntityId(),
                UrlRewrite::ENTITY_TYPE => self::ENTITY_TYPE,
            ]
        );

        return $currentUrlRewrites;
    }

    /**
     * @param $productCollection
     */
    protected function rebuildRewrites($productCollection)
    {
        foreach ($productCollection as $product){
            if ($product->getDefaultCategory()){
                $this->rebuildProductRewrite($product);
            }
        }
    }

    /**
     * @param $product
     */
    protected function rebuildProductRewrite($product)
    {
        $currentUrlRewrites = $this->getCurrentRewrite($product);
        $urls = array();

        foreach ($currentUrlRewrites as $currentUrlRewrite) {

            $defaultCategoryId = $this->getDefaultCategoryId($product);
            $defaultCategoryUrl = $product->getCategoryCollection()->getItemById($defaultCategoryId)->getUrl();

            $defaultCategoryUrl = $this->getCleanCategoryPath($defaultCategoryUrl);
            $newTargetPath = $defaultCategoryUrl . '/' . $product->getUrlKey() . $this->categoryUrlSuffix;
            $currentRequestPath = str_replace($this->categoryUrlSuffix, '', $currentUrlRewrite->getRequestPath());
            if ($product->getUrlKey() == $currentRequestPath && $newTargetPath != $currentUrlRewrite->getTargetPath()) {

                $currentUrlRewrite->setTargetPath($newTargetPath);
                $currentUrlRewrite->setRedirectType('301');
                $urls[] = $currentUrlRewrite;
                $this->rebuildCounter++;
            } else {
                $urls[] = $currentUrlRewrite;
            }

        }
        $this->urlPersistInterface->replace($urls);
    }

    /**
     * @param $product
     * @return mixed
     */
    protected function getDefaultCategoryId($product)
    {
        $productCategoryCollection  = $product->getCategoryCollection();
        foreach ($productCategoryCollection as $category)
        {
            if ($this->isDefaultCategory($category->getId(), $product->getDefaultCategory())){
                return $category->getId();
            }
        }
    }

    /**
     * @param $categoryId
     * @param $defaultCategory
     * @return bool
     */
    protected function isDefaultCategory($categoryId, $defaultCategory)
    {
        $categoryName = $this->categoryRepository->get($categoryId)->getName();
        if ($categoryName == $defaultCategory) {
            return true;
        }

        return false;
    }

    /**
     * @param $path
     * @return mixed
     */
    protected function getCleanCategoryPath($path)
    {
        $path = str_replace($this->baseUrl, '',$path);
        $path = str_replace($this->categoryUrlSuffix, '', $path);
        return $path;
    }

    /**
     * @return mixed
     */
    public function getProductCollection()
    {
        $collection = $this->_productFactory->create();
        $collection->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH);
        $collection->addAttributeToSelect('url_key');
        $collection->addAttributeToSelect('default_category');

        return $collection;
    }

    /**
     * @param $storeId
     * @return mixed
     */
    protected function  getCategoryUrlSuffix($storeId)
    {
        $suffix = $this->scopeConfig->getValue(
        self::XML_PATH_CATEGORY_URL_SUFFIX,
        \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
        $storeId
        );
        return $suffix;
    }

        /**
     * Insert multiple
     *
     * @param array $data
     * @return void
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     *
     * @throws \Exception
     */
    public function uninstallAttribute($attributeId = false)
    {
        if ($attributeId){
            $this->attributeRepository->deleteById($attributeId);
        }
    }
}