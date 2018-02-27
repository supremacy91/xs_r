<?php

namespace IntechSoft\CustomImport\Controller\Adminhtml\Import;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action;
/*use IntechSoft\CustomImport\Controller\Adminhtml\Import;*/

class Index extends Action
{
     /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * Index constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry
    ) {
        parent::__construct($context);
        $this->coreRegistry = $coreRegistry;
    }

    /**
     * @return mixed
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('IntechSoft_CustomImport::customImport');
        $resultPage->getConfig()->getTitle()->prepend(__('Custom Import'));

        return $resultPage;
    }

}