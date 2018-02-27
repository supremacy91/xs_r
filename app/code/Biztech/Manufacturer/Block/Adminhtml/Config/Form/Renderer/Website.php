<?php

namespace Biztech\Manufacturer\Block\Adminhtml\Config\Form\Renderer;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Encryption\EncryptorInterface;
use Biztech\Manufacturer\Helper\Data;

class Website extends Field
{
    protected $scopeConfig;
    protected $helper;
    protected $encryptor;

    public function __construct(
        Context $context,
        EncryptorInterface $encryptor,
        Data $helper,
        array $data = []
    )
    {
        $this->scopeConfig = $context->getScopeConfig();
        $this->encryptor = $encryptor;
        $this->helper = $helper;
        $this->storeManager = $context->getStoreManager();
        parent::__construct($context, $data);
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $html = '';
        $data = $this->scopeConfig->getValue('manufacturer/activation/data');
        $eleValue = explode(',', str_replace($data, '', $this->encryptor->decrypt($element->getValue())));
        $ele_name = $element->getName();
        $eleId = $element->getId();
        $element->setName($ele_name . '[]');
        $dataInfo1 = $this->helper->getDataInfo();
        $dataInfo = (array)$dataInfo1;
        if (isset($dataInfo['dom']) && intval($dataInfo['c']) > 0 && intval($dataInfo['suc']) == 1) {
            foreach ($this->storeManager->getWebsites() as $website) {
                $url = $this->scopeConfig->getValue('web/unsecure/base_url');
                $url = $this->helper->getFormatUrl(trim(preg_replace('/^.*?\/\/(.*)?\//', '$1', $url)));
                foreach ($dataInfo['dom'] as $web) {
                    if ($web->dom == $url && $web->suc == 1) {
                        $element->setChecked(false);
                        $id = $website->getId();
                        $name = $website->getName();
                        $element->setId($eleId . '_' . $id);
                        $element->setValue($id);
                        if (in_array($id, $eleValue) !== false) {
                            $element->setChecked(true);
                        }
                        if ($id != 0) {
                            $html .= '<div><label>' . $element->getElementHtml() . ' ' . $name . ' </label></div>';
                        }
                    }
                }
            }
        } else {
            $html = sprintf('<strong class="required">%s</strong>', __('Please enter a valid key'));
        }
        return $html;
    }

}