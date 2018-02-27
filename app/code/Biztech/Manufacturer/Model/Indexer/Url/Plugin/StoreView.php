<?php

namespace Biztech\Manufacturer\Model\Indexer\Url\Plugin;

class StoreView
{
    /**
     * Product attribute indexer processor
     *
     * @var \Magento\Catalog\Model\Indexer\Product\Eav\Processor
     */
    protected $_indexerUrlProcessor;

    /**
     * @param \Biztech\Manufacturer\Model\Indexer\Url\Processor
     */
    public function __construct(\Biztech\Manufacturer\Model\Indexer\Url\Processor $indexerUrlProcessor)
    {
        $this->_indexerUrlProcessor = $indexerUrlProcessor;
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
            $this->_indexerUrlProcessor->markIndexerAsInvalid();
        }
    }
}
