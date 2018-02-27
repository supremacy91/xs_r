<?php

namespace IntechSoft\CustomImport\Model\System\Config;

use Magento\Framework\Option\ArrayInterface;

class RootCategories implements ArrayInterface
{
    const ENABLED  = 1;
    const DISABLED = 0;


    /**
     * @var \Magento\Catalog\Api\CategoryManagementInterface
     */
    protected $_categoryManagement;

    /**
     * RootCategories constructor.
     * @param \Magento\Catalog\Api\CategoryManagementInterface $categoryManagement
     */
    public function __construct(
        \Magento\Catalog\Api\CategoryManagementInterface $categoryManagement
    )
    {
        $this->_categoryManagement = $categoryManagement;
    }


    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = $this->getRootCategories();

        return $options;
    }

    /**
     * @return array
     */
    protected function getRootCategories()
    {
        $rootCategories = array();
        $rootCategories[] = 'Choose root category';

        $rootId = 1;
        $depth = 1;

        $items = $this->_categoryManagement->getTree($rootId, $depth);

        foreach($items->getChildrenData() as $item) {
            $rootCategories[$item->getName()]  = $item->getName();
        }

        return $rootCategories;
    }
}