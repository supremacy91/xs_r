<?php

namespace Biztech\Manufacturer\Model\Indexer\Data\Plugin;

class StoreView
{
    /**
     * Product attribute indexer processor
     *
     * @var \Biztech\Manufacturer\Model\Indexer\Data\Processor
     */
    protected $_indexerDataProcessor;

    /**
     * @param \Biztech\Manufacturer\Model\Indexer\Data\Processor $indexerDataProcessor
     */
    public function __construct(\Biztech\Manufacturer\Model\Indexer\Data\Processor $indexerDataProcessor)
    {
        $this->_indexerDataProcessor = $indexerDataProcessor;
    }

    /**
     * Before save handler
     *
     * @param \Magento\Store\Model\ResourceModel\Store $subject
     * @param \Magento\Framework\Model\AbstractModel $object
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSave(
        \Magento\Store\Model\ResourceModel\Store $subject,
        \Magento\Framework\Model\AbstractModel $object
    ) {
        if ((!$object->getId() || $object->dataHasChangedFor('group_id')) && $object->getIsActive()) {
            $this->_indexerDataProcessor->markIndexerAsInvalid();
        }
    }
}
