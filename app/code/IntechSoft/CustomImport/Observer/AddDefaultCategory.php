<?php
namespace IntechSoft\CustomImport\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Class AddDefaultCategory
 * @package IntechSoft\CustomImport\Observer
 */
class AddDefaultCategory implements ObserverInterface
{
    /**
     * @var \IntechSoft\CustomImport\Model\Url\Rebuilt
     */
    protected $rebuiltModel;

    /**
     * AddDefaultCategory constructor.
     * @param \IntechSoft\CustomImport\Model\Url\Rebuilt $rebuiltModel
     */
    public function __construct(
        \IntechSoft\CustomImport\Model\Url\Rebuilt $rebuiltModel
    )
    {
        $this->rebuiltModel = $rebuiltModel;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product = $observer->getProduct();
        if (!$product->getDefaultCategory() && count($product->getCategoryCollection())){
            $this->rebuiltModel->setDefaultCategoryToProduct($product);
            $this->rebuiltModel->rebuildSingleProductUrlRewrites($product);
        }
    }
}