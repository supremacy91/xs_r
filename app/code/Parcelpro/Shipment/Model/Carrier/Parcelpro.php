<?php
namespace Parcelpro\Shipment\Model\Carrier;

use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Rate\Result;

class Parcelpro extends \Magento\Shipping\Model\Carrier\AbstractCarrier implements
    \Magento\Shipping\Model\Carrier\CarrierInterface
{
    /**
     * @var string
     */
    protected $_code = 'parcelpro';

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        array $data = []
    ) {
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * @return array
     */
    public function getAllowedMethods()
    {
        return [
            'postnl_afleveradres' => 'Afleveradres',
            'postnl_pakjegemak' => 'PakjeGemak',
            'postnl_nbb' => 'Alleen Huisadres',
            'postnl_hvo' => 'Handtekening',
            'postnl_or' => 'Onder Rembours',
            'postnl_vb' => 'Verzekerd bedrag',
            'postnl_pricerule' => 'Pricerule',
            'dhl_afleveradres' => 'Afleveradres',
            'dhl_parcelshop' => 'Parcelshop',
            'dhl_nbb' => 'Niet bij buren',
            'dhl_hvo' => 'Handtekening',
            'dhl_ez' => 'Extra zeker',
            'dhl_eve' => 'Avondlevering',
            'dhl_pricerule' => 'Pricerule',
            'custom_pricerule' => 'Pricerule'
        ];
    }

    protected function _rateresult($key, $value)
    {
        $rate = $this->_rateMethodFactory->create();
        $rate->setCarrier($this->_code);

        $matches = explode('_', $key);
        if ($matches[0] === 'dhl') $rate->setCarrierTitle($this->getConfigData('dhl_title'));
        if ($matches[0] === 'postnl') $rate->setCarrierTitle($this->getConfigData('postnl_title'));

        $rate->setMethod($key);
        $rate->setMethodTitle($value);

        $price = (float)$this->getConfigData($key);

        $rate->setPrice($price);
        $rate->setCost();
        return $rate;
    }

    /**
     * @param RateRequest $request
     * @return bool|Result
     */
    public function collectRates(RateRequest $request)
    {

        if (!$this->getConfigFlag('active')) {
            return false;
        }

        $result = $this->_rateResultFactory->create();
        $am = $this->getAllowedMethods();
        foreach ($am as $key => $value) {
            if ($this->getConfigData($key) ) {

                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $state = $objectManager->get('\Magento\Framework\App\State');

                if ($state->getAreaCode() == \Magento\Framework\App\Area::AREA_ADMINHTML) {

                    $object = $objectManager->create('\Magento\Sales\Model\AdminOrder\Create');
                    $total = $object->getQuote()->getSubtotal();
                    $grandTotal = $object->getQuote()->getGrandTotal();

                    $total = $grandTotal; // Verzendkosten berekenen op basis van bedrag incl. BTW

                    $freeBoxes = $this->getFreeBoxesCount($request);
                    error_log(print_r($freeBoxes, true));
                } else {
                    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                    $total = $objectManager->create('\Magento\Checkout\Model\Session')
                        ->getQuote()->getSubtotal();

                    $grandTotal = $objectManager->create('\Magento\Checkout\Model\Session')
                        ->getQuote()->getGrandTotal();
                }

                $countryId = $request->getDestCountryId();
                $weight = $request->getPackageWeight();
                $shippingPrice = false;

                $pricerules = unserialize($this->getConfigData($key));
                if ($key == "custom_pricerule") {
                    error_log(print_r($pricerules, true));
                }

                if (!empty($pricerules)) {

                    foreach ($pricerules as $pricerule) {
                        if ($pricerule['country'] != $countryId) continue;

                        if (($weight >= (float)$pricerule['min_weight']) && ($weight <= (float)$pricerule['max_weight']) && ($total >= (float)$pricerule['min_total']) && ($total <= (float)$pricerule['max_total'])) {
                            $shippingPrice = (float)$pricerule['price'];
                            break;
                        }
                    }

                    if ($shippingPrice !== false && $key != "custom_pricerule") {
                        $method = $this->_rateMethodFactory->create();

                        $method->setCarrier($this->_code);

                        if (strpos(strtolower($key), 'postnl') !== false) {
                            $method->setCarrierTitle('PostNL');
                        } else if (strpos(strtolower($key), 'DHL') !== false) {
                            $method->setCarrierTitle('DHL');
                        }
                        $method->setMethod($key);
                        $method->setMethodTitle($pricerule['titel']);
                        $method->setPrice($request->getFreeShipping() === true ? 0 : $shippingPrice);
                        $method->setCost($request->getFreeShipping() === true ? 0 : $shippingPrice);
                        $result->append($method);
                    }

                    if ($key == "custom_pricerule") {
                        $counter = 0;
                        $carrier = null;
                        foreach ($pricerules as $pricerule) {
                            if ($pricerule['country'] != $countryId) continue;

                            if (($weight >= (float)$pricerule['min_weight']) && ($weight <= (float)$pricerule['max_weight']) && ($total >= (float)$pricerule['min_total']) && ($total <= (float)$pricerule['max_total'])) {
                                $shippingPrice = (float)$pricerule['price'];

                                if ($shippingPrice !== false) {
                                    error_log(print_r($pricerule, true));
                                    $method = $this->_rateMethodFactory->create();

                                    $method->setCarrier($this->_code);
                                    $method->setCarrierTitle($pricerule['carrier']);

                                    $method->setMethod($key . "_" . $counter);
                                    $method->setMethodTitle($pricerule['titel']);
                                    $method->setPrice($request->getFreeShipping() === true ? 0 : $shippingPrice);
                                    $method->setCost($request->getFreeShipping() === true ? 0 : $shippingPrice);
                                    $result->append($method);
                                }
                            }
                            if ($key == "custom_pricerule") {
                                $counter++;
                            }
                        }
                    }
                }

            }
        }
        return $result;
    }

    /**
     * @param RateRequest $request
     * @return int
     */
    private function getFreeBoxesCount(RateRequest $request)
    {
        $freeBoxes = 0;
        if ($request->getAllItems()) {
            foreach ($request->getAllItems() as $item) {
                if ($item->getProduct()->isVirtual() || $item->getParentItem()) {
                    continue;
                }

                if ($item->getHasChildren() && $item->isShipSeparately()) {
                    $freeBoxes += $this->getFreeBoxesCountFromChildren($item);
                } elseif ($item->getFreeShipping()) {
                    $freeBoxes += $item->getQty();
                }
            }
        }
        return $freeBoxes;
    }

    /**
     * @param mixed $item
     * @return mixed
     */
    private function getFreeBoxesCountFromChildren($item)
    {
        $freeBoxes = 0;
        foreach ($item->getChildren() as $child) {
            if ($child->getFreeShipping() && !$child->getProduct()->isVirtual()) {
                $freeBoxes += $item->getQty() * $child->getQty();
            }
        }
        return $freeBoxes;
    }
}
