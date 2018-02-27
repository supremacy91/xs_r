<?php
namespace ParcelPro\Shipment\Model;

use Magento\Checkout\Model\ConfigProviderInterface;

class CustomConfigProvider implements ConfigProviderInterface
{
    protected $logger;
    protected $scopeConfig;

    public function __construct(
        \Psr\Log\LoggerInterface $loggerInterface,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->logger = $loggerInterface;
        $this->scopeConfig = $scopeConfig;
    }
    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $config = $this->scopeConfig->getValue('carriers/parcelpro', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $gebruikerid = $config["gebruiker_id"];
        $apikey = $config["api_key"];

        $config = [
            'config' => [
                'gebruikerID' => $gebruikerid
            ]
        ];
        return $config;
    }
}