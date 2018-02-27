<?php
namespace Biztech\Manufacturer\Block;
use Magento\Framework\View\Element\Template;
class BaseBlock extends Template
{
    protected $devToolHelper;
    protected $urlApp;
    protected $config;
    
    /**
     * BaseBlock constructor.
     * @param Context $context
     */
    public function __construct(\Biztech\Manufacturer\Block\Context $context
    ){
        $this->devToolHelper = $context->getManufacturerHelper();
        $this->config = $context->getConfig();
        $this->urlApp = $context->getUrlFactory()->create();
        parent::__construct($context);
    }

    /**
     * Function for getting event details
     * @return array
     */
    public function getEventDetails()
    {
        return $this->devToolHelper->getEventDetails();
    }

    /**
     * Function for getting current url
     * @return string
     */
    public function getCurrentUrl()
    {
        return $this->urlApp->getCurrentUrl();
    }

    /**
     * Function for getting controller url for given router path
     * @param string $routePath
     * @return string
     */
    public function getControllerUrl($routePath)
    {

        return $this->urlApp->getUrl($routePath);
    }

    /**
     * Function for getting current url
     * @param string $path
     * @return string
     */
    public function getConfigValue($path)
    {
        return $this->config->getCurrentStoreConfigValue($path);
    }

    /**
     * Function canShowManufacturer
     * @return bool
     */
    public function canShowManufacturer()
    {
        $isEnabled = $this->getConfigValue('manufacturer/general/is_enabled');
        if ($isEnabled) {
            $allowedIps = $this->getConfigValue('manufacturer/general/allowed_ip');
            if (is_null($allowedIps)) {
                return true;
            } else {
                $remoteIp = $_SERVER['REMOTE_ADDR'];
                if (strpos($allowedIps, $remoteIp) !== false) {
                    return true;
                }
            }
        }
        return false;
    }

}
