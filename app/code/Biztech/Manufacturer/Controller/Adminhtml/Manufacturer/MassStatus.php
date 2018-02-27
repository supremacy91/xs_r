<?php

namespace Biztech\Manufacturer\Controller\Adminhtml\Manufacturer;

class MassStatus extends \Magento\Backend\App\Action {

    protected $_storeManager;
    protected $_manufacturerModel;
    protected $_manufacturertextModel;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Biztech\Manufacturer\Model\Config $storeConfig,
        \Biztech\Manufacturer\Model\Manufacturer $manufacturer,
        \Biztech\Manufacturer\Model\Manufacturertext $manufacturertext
    ){
        $this->_storeManager = $storeConfig->getStoreManager();
        $this->_manufacturerModel = $manufacturer;
        $this->_manufacturertextModel = $manufacturertext;
        parent::__construct($context);
    }

    public function execute() {
        $ids = $this->getRequest()->getParam('manufacturer');
        $status = $this->getRequest()->getParam('status');
        $storeId = $this->getRequest()->getParam('store', 0);

        if (!is_array($ids) || empty($ids)) {
            $this->messageManager->addError(__('Please select manufacturer(s).'));
        } else {
            try {

                foreach ($ids as $id) {
                    if( $storeId != 0 ){
                        $collection_text = '';
                        $collection_text = $this->_manufacturertextModel->getCollection()->addFieldToFilter('manufacturer_id',$id);
                        $textData = $collection_text->addFieldToFilter('store_id',$storeId)->getData();
                        $modelText = $this->_manufacturertextModel->load($textData[0]['text_id']);

                            $modelText->setTextId($textData[0]['text_id'])->setStoreId($storeId)
                            ->setStatus($status)
                            ->setIsMassupdate(true)
                            ->save();
                    } else {
                        $collection_text = '';
                        $collection_text = $this->_manufacturertextModel->getCollection();
                        $textData = $collection_text->addFieldToFilter('manufacturer_id',$id)->addFieldToFilter('store_id', 0)->getData();
                        
                        $modelText = $this->_manufacturertextModel->load($textData[0]['text_id']);

                            $modelText->setTextId($textData[0]['text_id'])->setStoreId($storeId)
                            ->setStatus($status)
                            ->setIsMassupdate(true)
                            ->save();

                        foreach ($this->_storeManager->getStores() as $store) {
                            $collection_text = '';
                            $collection_text = $this->_manufacturertextModel->getCollection()->addFieldToFilter('manufacturer_id',$id);
                            $textData = $collection_text
                                                ->addFieldToFilter('store_id', $store->getId())
                                                ->getData();

                            $modelText = $this->_manufacturertextModel->load($textData[0]['text_id']);

                            $modelText->setTextId($textData[0]['text_id'])->setStoreId($store->getId())
                            ->setStatus($status)
                            ->setIsMassupdate(true)
                            ->save();
                        }
                    }
                    
                }

                $this->messageManager->addSuccess(
                        __('A total of %1 record(s) were successfully updated', count($ids))
                );
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }

        $this->_redirect('*/*/', array('store' => $storeId ));
    }

    /**
     * Check for is allowed
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Biztech_Manufacturer::biztech_manufacturer_index');
    }

}
