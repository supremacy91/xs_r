<?php
namespace Parcelpro\Shipment\Plugin;

use Parcelpro\Shipment\Model\ParcelproFactory;

class PluginBefore
{
    protected $_url = 'http://login.parcelpro.nl';

    public function beforePushButtons(
        \Magento\Backend\Block\Widget\Button\Toolbar\Interceptor $subject,
        \Magento\Framework\View\Element\AbstractBlock $context,
        \Magento\Backend\Block\Widget\Button\ButtonList $buttonList
    ) {
      if (!$context instanceof \Magento\Sales\Block\Adminhtml\Order\View) {
          return [$context, $buttonList];
      }
        $this->_request = $context->getRequest();
        if($this->_request->getFullActionName() == 'sales_order_view'){
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

            $order_id = $this->_request->getParams()['order_id'];
            $order = $objectManager->create('Magento\Sales\Model\Order')->load($order_id);
            $order_id = $order->getIncrementId();
            $collection = $objectManager->create('Parcelpro\Shipment\Model\Resource\Parcelpro\CollectionFactory');
            $collection = $collection->create()->addFieldToFilter('order_id', $order_id)->addFieldToSelect('*')->setOrder('id', 'DESC')->load();
            $result = ($collection->getColumnValues('zending_id')) ? $collection->getColumnValues('zending_id') : null;
            $zending_id = (is_array($result)) ? current($result) : null;

            if (count($result) !== 0) {
                $buttonList->add(
                    'print_label',
                    ['label' => __('Print label'), 'onclick' => "window.open('" . $this->getLabelUrl($zending_id) . "','Show Label','height=600,width=800,toolbar=yes,location=yes,directories=yes,status=yes,menubar=yes,scrollbars=yes,copyhistory=yes,resizable=yes')", 'class' => 'save'],
                    -1
                );
            }else{
                $buttonList->add(
                    'zendingAanmelden',
                    ['label' => __('Zending aanmelden'), 'onclick' => 'setLocation(\''.$context->getUrl("pp_shipment/shipment/index").'\')', 'class' => 'reset'],
                    -1
                );
            }
        }
    }

    public function getLabelUrl($zendingId)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $config = $objectManager->create('Magento\Framework\App\Config');
        $config = $config->getValue('carriers/parcelpro', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $gebruikerid = $config["gebruiker_id"];
        $apikey = $config["api_key"];

        $hmacSha256 = hash_hmac("sha256", $gebruikerid . $zendingId, $apikey);

        $parameters = array(
            'GebruikerId' => $gebruikerid,
            'ZendingId' => $zendingId,
            'HmacSha256' => $hmacSha256
        );

        $queryString = http_build_query($parameters);

        $labelUrl = $this->_url . '/API/label.php?' . $queryString;

        return $labelUrl;
    }
}
