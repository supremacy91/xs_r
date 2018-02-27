<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Codazon\AjaxLayeredNav\Block;

class Json extends \Magento\Framework\View\Element\Template
{
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Codazon\AjaxLayeredNav\Block\Block $lib,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_registry = $coreRegistry;
        $this->_categoryFactory = $categoryFactory;
        $this->_lib = $lib;
    }
    
    public function toHtml()
    {
        $layer = $this->getLayout()->getBlock('catalog.leftnav');
        $products = $this->getLayout()->getBlock('category.products');
        $data = array(
            'layer'=> $layer->toHtml(), 
            'products' => $products->toHtml()
        );
        $json = json_encode($data);
        return $json;
    }
}
