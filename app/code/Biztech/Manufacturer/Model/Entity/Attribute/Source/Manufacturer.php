<?php

namespace Biztech\Manufacturer\Model\Entity\Attribute\Source;

class Manufacturer extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
	/**
     * @var array
     */
    protected $_optionsData;
    protected $_options;

    /**
     * @param array $options
     * @codeCoverageIgnore
     */
    public function __construct(
    	\Biztech\Manufacturer\Model\Manufacturer $manufacturerCollection
    )
    {
        $this->_optionsData = $manufacturerCollection->getCollection();
    }

    /**
     * Retrieve all options for the source from configuration
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return array
     */
    public function getAllOptions()
    {
        if ($this->_options === null) {
            $this->_options = [];

            if (empty($this->_optionsData)) {
                throw new \Magento\Framework\Exception\LocalizedException(__('No Brands found.'));
            }
            $nodata = array(
				'label' => __('Select Brand'),
				'value' => ''
			);
			array_push($this->_options,$nodata);

            foreach ($this->_optionsData as $option) {
                $this->_options[] = [
                	'value' => $option->getManufacturerId(), 
                	'label' => __($option->getBrandName())
                ];
            }
        }

        return $this->_options;
    }	
}