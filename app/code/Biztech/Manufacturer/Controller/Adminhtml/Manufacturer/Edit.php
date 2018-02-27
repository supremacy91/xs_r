<?php

namespace Biztech\Manufacturer\Controller\Adminhtml\Manufacturer;

class Edit extends \Magento\Backend\App\Action
{

    public function __construct(
        \Magento\Backend\App\Action\Context $context
    )
    {

        parent::__construct($context);
    }

    public function execute()
    {

        $store = $this->getRequest()->getParam('store', 0);
        $id = $this->getRequest()->getParam('manufacturer_id');

        $manufacturerModel = $this->_objectManager->create('Biztech\Manufacturer\Model\Manufacturer');
        $manufacturertextModel = $this->_objectManager->create('Biztech\Manufacturer\Model\Manufacturertext');
        $registryObject = $this->_objectManager->get('Magento\Framework\Registry');

        $model = $manufacturerModel->load($id);
        $model_text = $manufacturertextModel->getCollection()
            ->addFieldToFilter('store_id', $store)
            ->addFieldToFilter('manufacturer_id', $model->getManufacturerId())
            ->getData();

        if (isset($model_text[0])) {
            $text_data = $model_text[0];
        } else {
            $text_data = [];
        }

        $model = $model->addData($text_data);

        if ($model->getManufacturerId() || $id == 0) {
            $data = $this->_objectManager->get('Magento\Backend\Model\Session')->getFormData(true);

            if (!empty($data)) {
                try {
                    $model->addData($data);
                    if (is_object($manufacturertextModel)) {
                        $manufacturertextModel->addData($data);
                    }
                } catch (Exception $e) {
                    $this->messageManager->addError($e->getMessage());
                    $this->messageManager->setFormData($data);
                    $this->_redirect('*/*/edit', ['manufacturer_id' => $this->getRequest()->getParam('manufacturer_id')]);
                    return;
                }
            }
            $registryObject->register('manufacturer_grid', $model);


            $this->_view->loadLayout();
            $this->_view->getPage()->getConfig()->getTitle()->set('Manufacturer Manager');

            $this->_view->getLayout()->initMessages();
            $this->_view->renderLayout();
        } else {
            $this->messageManager->addError(__('Manufacturer does not exist'));
            $this->_redirect('*/*/', ['store' => $store]);
        }

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
