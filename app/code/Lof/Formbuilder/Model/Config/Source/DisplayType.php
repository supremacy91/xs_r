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

namespace Lof\Formbuilder\Model\Config\Source;

class DisplayType implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var array
     */
    protected $options;

    /**
     * @var CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @param \Lof\Formbuilder\Model\Form $collectionFactory
     */
    public function __construct(
        \Lof\Formbuilder\Model\Form $collectionFactory
    )
    {
        $this->_collectionFactory = $collectionFactory;
    }

    public function toOptionArray()
    {
        $data = [];
        $data[] = [
            'value' => 'popup',
            'label' => __('Popup'),
        ];
        $data[] = [
            'value' => 'link',
            'label' => __('Button Link'),
        ];
        $data[] = [
            'value' => 'current_page',
            'label' => __('Show on current page')
        ];
        return $data;
    }
}