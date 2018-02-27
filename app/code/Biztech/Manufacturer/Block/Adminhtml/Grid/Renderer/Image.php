<?php
namespace Biztech\Manufacturer\Block\Adminhtml\Grid\Renderer;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Backend\Block\Context;
use Magento\Catalog\Model\Product;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\DataObject;
use Magento\Framework\UrlInterface;
class Image extends AbstractRenderer
{
    protected $productModel;
    protected $storeManager;

    public function __construct(
        Context $context,
        Product $productModel,
        StoreManagerInterface $storeManager
    )
    {
        $this->productModel = $productModel;
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * @param \Magento\Framework\DataObject $row
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function render(DataObject $row)
    {
        if ($row->getData('filename') == "") {
            return "";
        } else {
            $attr = $this->productModel->getResource()->getAttribute('manufacturer');
            $manufacturerName = $attr->getSource()->getOptionText($row->getManufacturerName());
            $replace = ["'", " ", "!", "%", "@", "$", '#'];
            $newManufacturerName = str_replace($replace, "_", $manufacturerName);
            return "<img src='" . $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . "Manufacturer" . $row->getdata('filename') . "' width='75' height='75'/>";
        }

        parent::render($row);
    }

}
