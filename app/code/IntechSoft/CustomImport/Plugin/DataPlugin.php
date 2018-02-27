<?php
namespace IntechSoft\CustomImport\Plugin;

class DataPlugin extends \Magento\ImportExport\Model\ResourceModel\Import\Data{

    protected $_coreRegistry;

    public function __construct(
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        $connectionName = null
    ) {
        parent::__construct($context, $jsonHelper, $connectionName);
        $this->_coreRegistry = $coreRegistry;

    }
    public function aroundGetNextBunch(\Magento\ImportExport\Model\ResourceModel\Import\Data $subject, $proceed){

        $ifIntech = $this->_coreRegistry->registry('isIntechsoftCustomImportModule');
        if ($ifIntech === 1) {
            $proceed = function () {
                $this->_iterator = null;
                if (null === $this->_iterator) {
                    $this->_iterator = $this->getIterator();
                    $this->_iterator->rewind();
                }
                $dataRow = null;
                if ($this->_iterator->valid()) {
                    $encodedData = $this->_iterator->current();
                    if (array_key_exists(0, $encodedData) && $encodedData[0]) {
                        $dataRow = $this->jsonHelper->jsonDecode($encodedData[0]);
                        $this->_iterator->next();
                    }
                }
                if (!$dataRow) {
                    $this->_iterator = null;
                }
                return $dataRow;
            };

        }

        return $proceed();

    }
}

?>