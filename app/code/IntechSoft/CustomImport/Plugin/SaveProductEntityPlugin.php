<?php
namespace IntechSoft\CustomImport\Plugin;

class SaveProductEntityPlugin{

    protected $_om;
    public function __construct(\Magento\Framework\ObjectManagerInterface $om) {
        $this->_om = $om;
    }

    public function afterSaveProductEntity(\Magento\CatalogImportExport\Model\Import\Product $subject, $result){
        $registryImportFlag = $this->_om->get('\Magento\Framework\Registry');
        if ($result instanceof \Magento\CatalogImportExport\Model\Import\Product) {
            $registryImportFlag->register('importSuccessFlag', 1, true);
        }
        return $result;

    }


}
