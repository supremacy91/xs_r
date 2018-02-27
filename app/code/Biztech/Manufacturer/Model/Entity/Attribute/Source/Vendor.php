<?php

namespace Biztech\Manufacturer\Model\Entity\Attribute\Source;

/**
 * Description of Vendor
 *
 * @author vinay
 */
class Vendor extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource {
    
    public function __construct(
            
    ) {
        parent::__construct();
    }
    /*
     * Retrieve all options array
     * 
     * @return array
     * @author vinay
     */
    public function getAllOptions(){
        if(is_null($this->_options) ){
            $collection = new \Biztech\Manufacturer\Model\Manufacturer\Manufacturer;
            $collection = $collection->getCollection();
        }
    }
}
