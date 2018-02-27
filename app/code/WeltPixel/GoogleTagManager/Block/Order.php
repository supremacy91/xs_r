<?php
namespace WeltPixel\GoogleTagManager\Block;

/**
 * Class \WeltPixel\GoogleTagManager\Block\Order
 */
class Order extends \WeltPixel\GoogleTagManager\Block\Core
{
    /**
     * Returns the product details for the purchase gtm event
     * @return array
     */
    public function getProducts() {
        $order = $this->getOrder();
        $products = [];

        foreach ($order->getAllVisibleItems() as $item) {
            $product = $item->getProduct();
            $productDetail = [];
            $productDetail['name'] = html_entity_decode($item->getName());
            $productDetail['id'] = $this->helper->getGtmOrderItemId($item);
            $productDetail['price'] = number_format($item->getBasePrice(), 2, '.', '');
            if ($this->helper->isBrandEnabled()) :
                $productDetail['brand'] = $this->helper->getGtmBrand($product);
            endif;
            $productDetail['category'] = $this->helper->getGtmCategoryFromCategoryIds($product->getCategoryIds());
            $productDetail['quantity'] = $item->getQtyOrdered();
            $products[] = $productDetail;
        }

        return $products;
    }

    /**
     * Returns the product id's
     * @return array
     */
    public function getProductIds() {
        $order = $this->getOrder();
        $products = [];

        foreach ($order->getAllVisibleItems() as $item) {
            $products[] = $this->helper->getGtmOrderItemId($item);
        }

        return $products;
    }


    /**
     * Retuns the order total (subtotal or grandtotal)
     * @return float
     */
    public function getOrderTotal() {
        $orderTotalCalculationOption = $this->helper->getOrderTotalCalculation();
        switch ($orderTotalCalculationOption) {
            case \WeltPixel\GoogleTagManager\Model\Config\Source\OrderTotalCalculation::CALCULATE_SUBTOTAL :
                $orderTotal = $this->getOrder()->getBaseSubtotal();
                break;
            case \WeltPixel\GoogleTagManager\Model\Config\Source\OrderTotalCalculation::CALCULATE_GRANDTOTAL :
            default:
                $orderTotal = $this->getOrder()->getBaseGrandtotal();
                break;

        }

        return $orderTotal;
    }
}