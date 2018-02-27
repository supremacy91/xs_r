<?php

namespace Biztech\Manufacturer\Block\Adminhtml\Config\Form\Button;

class Layeredconfig extends \Magento\Config\Block\System\Config\Form\Field
{
    const BUTTON_TEMPLATE = 'config/form/button/layeredconfig.phtml';
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    )
    {
        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('Biztech_Manufacturer::config/form/button/layeredconfig.phtml');

    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getButtonHtml()
    {
        $params = [
            'website' => $this->getRequest()->getParam('website')
        ];

        $url = $this->getUrl('manufacturer/manufacturer/UpdateLeftNavigation', $params);

        $button = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData([
            'id' => 'clean_cache',
            'label' => __('Clean Cache'),
            'onclick' => 'window.location.href="' . $url . '"'
        ]);

        return $button->toHtml();
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        // Remove scope label
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return $this->_toHtml();
    }

}
