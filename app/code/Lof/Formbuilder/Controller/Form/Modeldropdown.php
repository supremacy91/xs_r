<?php
/**
 * Landofcoder
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Landofcoder.com license that is
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

namespace Lof\Formbuilder\Controller\Form;

use Magento\Customer\Controller\AccountInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Modeldropdown extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Lof\Formbuilder\Model\Model
     */
    protected $_model;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $_escaper;

    /**
     * @param Context                                    $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry                $registry
     * @param \Lof\Formbuilder\Model\Model               $model
     * @param \Magento\Framework\Escaper                 $escaper
     */
    public function __construct(
        Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        \Lof\Formbuilder\Model\Model $model,
        \Magento\Framework\Escaper $escaper
    )
    {
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $registry;
        $this->_model = $model;
        $this->_escaper = $escaper;
        parent::__construct($context);
    }

    public function execute()
    {
        header('Content-Type: text/javascript');
        $post = $this->getRequest()->getPost();
        $data_return = 'Element.update(';
        if ($post) {
            $store_id = isset($post['store_id']) ? $post['store_id'] : 0;
            $target_id = isset($post['target_id']) ? $post['target_id'] : "";
            $value = isset($post['value']) ? (int)$post['value'] : 0;
            $data_return .= '"' . $target_id . '",' . "'";

            if ($value) {
                $collection = $this->_model->getCollection();
                $collection->addFieldToFilter("parent_id", $value)->getSelect()->order('position', 'asc');
                $title = __("Select a option");
                $title = str_replace("'", "\'", $title);
                $tmp = '<option data-id="0" value="">' . $this->_escaper->escapeHtml($title) . '</option>';
                $data_return .= $tmp;

                if (0 < $collection->getSize()) {
                    foreach ($collection as $item) {
                        $title = $item->getTitle();
                        $title = str_replace("'", "\'", $title);
                        $tmp = '<option data-id="' . $item->getId() . '" value="' . $this->_escaper->escapeHtml($title) . '">' . $this->_escaper->escapeHtml($title) . '</option>';

                        $data_return .= $tmp;
                    }
                }
            }

            $data_return .= "'";
        }
        $data_return .= ')';
        echo str_replace("\n", "", $data_return);
        exit;
    }
}