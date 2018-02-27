<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Xlanding
 */

namespace Amasty\Xlanding\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Cms\Block\Adminhtml\Page\Grid\Renderer\Action\UrlBuilder;
use Magento\Framework\UrlInterface;

class PageActions extends Column
{
    const LANDING_URL_PATH_EDIT = 'amasty_xlanding/page/edit';
    const LANDING_URL_PATH_DELETE = 'amasty_xlanding/page/delete';

    protected $_editUrl;

    protected $_actionUrlBuilder;

    protected $_urlBuilder;

    public function __construct(
        ContextInterface $context,
        UrlBuilder $actionUrlBuilder,
        UrlInterface $urlBuilder,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = [],
        $editUrl = self::LANDING_URL_PATH_EDIT
    ) {
        $this->_urlBuilder = $urlBuilder;
        $this->_actionUrlBuilder = $actionUrlBuilder;
        $this->_editUrl = $editUrl;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dSource)
    {
        if (isset($dSource['data']['items'])) {
            foreach ($dSource['data']['items'] as & $item) {
                $name = $this->getData('name');
                if (isset($item['identifier'])) {
                    $item[$name]['preview'] = [
                        'href' => $this->_actionUrlBuilder->getUrl(
                            $item['identifier'],
                            isset($item['_first_store_id']) ? $item['_first_store_id'] : null,
                            isset($item['store_code']) ? $item['store_code'] : null
                        ),
                        'label' => __('Preview')
                    ];
                }
                if (isset($item['page_id'])) {
                    $item[$name]['delete'] = [
                        'href' => $this->_urlBuilder->getUrl(self::LANDING_URL_PATH_DELETE, ['page_id' => $item['page_id']]),
                        'label' => __('Delete'),
                        'confirm' => [
                            'title' => __('Delete ${ $.$data.title }'),
                            'message' => __('Are you sure you wan\'t to delete a ${ $.$data.title } record?')
                        ]
                    ];
                    $item[$name]['edit'] = [
                        'href' => $this->_urlBuilder->getUrl($this->_editUrl, ['page_id' => $item['page_id']]),
                        'label' => __('Edit')
                    ];
                }
            }
        }
        return $dSource;
    }
}
