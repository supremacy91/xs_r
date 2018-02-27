<?php

namespace Biztech\Manufacturer\Model\Indexer\Url\Action;

/**
 * Class Full reindex action
 */
class Full extends \Biztech\Manufacturer\Model\Indexer\Url\AbstractUrlAction
{
    /**
     * Execute Full reindex
     *
     * @param array|int|null $ids
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($ids = null)
    {
        try {
            $this->reindex();
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()), $e);
        }
    }
}
