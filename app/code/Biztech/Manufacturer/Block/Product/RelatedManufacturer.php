<?php
namespace Biztech\Manufacturer\Block\Product;
use Magento\Catalog\Block\Product\AbstractProduct;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Eav\Model\Config;
class RelatedManufacturer extends AbstractProduct
{
    protected $productCollection;
    protected $eavConfig;

    /**
     * RelatedManufacturer constructor.
     * @param Context $context
     * @param Collection $collection
     * @param Config $eavConfig
     * @param array $data
     */
    public function __construct(
        Context $context,
        Collection $collection,
        Config $eavConfig,
        $data = []
    ){
        parent::__construct($context, $data);
        $this->productCollection = $collection;
        $this->eavConfig = $eavConfig;
    }


    /**
     * @return Collection
     */
    public function getProductCollection()
    {
        return $this->productCollection;
    }

    /**
     * @return Config
     */
    public function getEavConfig()
    {
        return $this->eavConfig;
    }


}
