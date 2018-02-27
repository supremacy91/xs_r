<?php
/**
 * Landofcoder
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the landofcoder.com license that is
 * available through the world-wide-web at this URL:
 * http://landofcoder.com/license
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category   Landofcoder
 * @package    Lof_Formbuilder
 * @copyright  Copyright (c) 2016 Landofcoder (http://www.landofcoder.com/)
 * @license    http://www.landofcoder.com/LICENSE-1.0.html
 */

namespace Lof\Formbuilder\Block;

class Field extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Lof\Formbuilder\Model\Modelcategory
     */
    private $_modelCategory;

    /**
     * @var \Lof\Formbuilder\Model\Model
     */
    private $_model;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Lof\Formbuilder\Model\Modelcategory             $modelCategory
     * @param \Lof\Formbuilder\Model\Model                     $model
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Lof\Formbuilder\Model\Modelcategory $modelCategory,
        \Lof\Formbuilder\Model\Model $model,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->_modelCategory = $modelCategory;
        $this->_model = $model;
    }

    public function getCategories($category_id = 0, $max_level = 2)
    {
        $return = [];
        if ($category_id) {
            $category = $this->_modelCategory->load($category_id);
            $count_level = 0;
            if (1 == $category->getStatus()) {
                $tmp = [
                    "id" => $category->getId(),
                    "label" => $category->getTitle()
                ];
                $model_collection = $this->_model->getCollection();
                $model_collection->addFieldToFilter("category_id", $category_id)
                    ->addFieldToFilter("status", 1)
                    ->setOrder("position", "ASC");
                $tmp["models"] = $model_collection;
                $return[] = $tmp;
                $count_level++;

                //get category children
                if ($count_level < $max_level) {
                    $return = array_merge($return, $this->getTreeCategories($category_id, $max_level, $count_level));
                }
            }
        }
        return $return;
    }

    public function getTreeCategories($category_id = 0, $max_level = 2, $count_level = 2)
    {
        $return = array();
        $collection = $this->_modelCategory->getCollection();
        $collection->addFieldToFilter("parent_id", $category_id)
            ->addFieldToFilter("status", 1)
            ->setOrder("position", "ASC")
            ->getSelect()
            ->limit(1);
        if (0 < $collection->getSize()) {
            foreach ($collection as $item) {
                $tmp = array("id" => $item->getId(), "label" => $item->getTitle(), "models" => array());
                $return[] = $tmp;
                if ($count_level < $max_level) {
                    $return = array_merge($return, $this->getTreeCategories($item->getId(), $max_level, $count_level + 1));
                }
            }
        }
        return $return;
    }

    public function getDefaultSelectUrl()
    {
        return $this->getUrl('lofformbuilder/form/modeldropdown');
    }

    public function getStore()
    {
        return $this->_storeManager->getStore();
    }
}